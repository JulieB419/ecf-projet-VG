<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Order;
use App\Models\Review;
use App\Services\Mailer;
use App\Services\StatsStore;
final class EmployeeController extends Controller
{
    public function index(): void
    {
        Auth::requireRole(['employee','admin']);
        $filters = ['status'=>$_GET['status'] ?? '', 'q'=>$_GET['q'] ?? ''];
        $this->view('employee/index', [
            'orders'=>Order::listForEmployee($filters),
            'filters'=>$filters,
            'pendingReviews'=>Review::pending()
        ]);
    }

    public function orderShow(array $params): void
    {
        Auth::requireRole(['employee','admin']);
        $order = Order::findAny((int)$params['id']);
        if (!$order) { http_response_code(404); echo 'Commande introuvable'; return; }
        $this->view('employee/order_show', ['order'=>$order]);
    }

    public function updateStatus(array $params): void
    {
        Auth::requireRole(['employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/espace-employe');
        $u = Auth::user();

        $status = (string)($_POST['status'] ?? '');
        $allowed = ['acceptee','en_preparation','en_livraison','livree','attente_retour_materiel','terminee'];
        if (!in_array($status, $allowed, true)) { Session::flash('error','Statut invalide.'); $this->redirect('/espace-employe'); }

        $orderId = (int)$params['id'];
        Order::updateStatus($orderId, $status, (int)$u['id']);

        $order = Order::findAny($orderId);
        if ($order && $status === 'attente_retour_materiel') {
            Mailer::send($order['email'],'Retour matériel',"Restitution sous 10 jours ouvrés, sinon 600€ (voir CGV).");
        }
        if ($order && $status === 'terminee') {
            Mailer::send($order['email'],'Commande terminée',"Vous pouvez laisser un avis depuis votre espace client.");
        }

        Session::flash('success','Statut mis à jour.');
        $this->redirect('/espace-employe/commandes/'.$orderId);
    }

    public function cancelOrder(array $params): void
    {
        Auth::requireRole(['employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/espace-employe');
        $u = Auth::user();

        $mode = (string)($_POST['contact_mode'] ?? '');
        $cdate = (string)($_POST['contact_date'] ?? '');
        $reason = trim((string)($_POST['reason'] ?? ''));
        if (!in_array($mode, ['appel','mail'], true) || $cdate==='' || $reason==='') { Session::flash('error','Champs obligatoires.'); $this->redirect('/espace-employe/commandes/'.(int)$params['id']); }

        $orderId = (int)$params['id'];
        Order::cancelByEmployee($orderId, (int)$u['id'], $mode, $cdate, $reason);
        StatsStore::markCancelled($orderId);

        $order = Order::findAny($orderId);
        if ($order) Mailer::send($order['email'],'Commande annulée',"Motif : {$reason}");

        Session::flash('success','Commande annulée.');
        $this->redirect('/espace-employe');
    }

    public function validateReview(array $params): void
    {
        Auth::requireRole(['employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/espace-employe');
        Review::setStatus((int)$params['id'], 'valide');
        Session::flash('success','Avis validé.');
        $this->redirect('/espace-employe');
    }

    public function rejectReview(array $params): void
    {
        Auth::requireRole(['employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/espace-employe');
        Review::setStatus((int)$params['id'], 'refuse');
        Session::flash('success','Avis refusé.');
        $this->redirect('/espace-employe');
    }
}
