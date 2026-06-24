<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Order;
use App\Models\Review;
use App\Services\StatsStore;

final class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['user','employee','admin']);
        $u = Auth::user();
        $this->view('profile/index', ['user'=>$u, 'orders'=>Order::listForUser((int)$u['id'])]);
    }

    public function orderShow(array $params): void
    {
        Auth::requireRole(['user','employee','admin']);
        $u = Auth::user();
        $order = Order::findForUser((int)$params['id'], (int)$u['id']);
        if (!$order) { http_response_code(404); echo 'Commande introuvable'; return; }
        $this->view('profile/order_show', ['order'=>$order]);
    }

    public function cancelOrder(array $params): void
    {
        Auth::requireRole(['user','employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/profil');

        $u = Auth::user();
        $order = Order::findForUser((int)$params['id'], (int)$u['id']);
        if (!$order) { http_response_code(404); echo 'Commande introuvable'; return; }
        if (!Order::canUserCancel($order)) { Session::flash('error','Annulation impossible.'); $this->redirect('/profil/commandes/'.(int)$params['id']); }

        Order::cancelByUser((int)$params['id'], (int)$u['id']);
        StatsStore::markCancelled((int)$params['id']);
        Session::flash('success','Commande annulée.');
        $this->redirect('/profil');
    }

    public function submitReview(array $params): void
    {
        Auth::requireRole(['user','employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/profil');

        $u = Auth::user();
        $order = Order::findForUser((int)$params['id'], (int)$u['id']);
        if (!$order) { http_response_code(404); echo 'Commande introuvable'; return; }
        if ($order['status'] !== 'terminee') { Session::flash('error','Avis possible uniquement après fin.'); $this->redirect('/profil/commandes/'.(int)$params['id']); }

        $rating = max(1, min(5, (int)($_POST['rating'] ?? 0)));
        $comment = trim((string)($_POST['comment'] ?? ''));

        Review::create((int)$params['id'], (int)$u['id'], $rating, $comment);
        Session::flash('success','Avis envoyé (en attente).');
        $this->redirect('/profil/commandes/'.(int)$params['id']);
    }
}
