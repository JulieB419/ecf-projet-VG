<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\Menu;
use App\Models\Order;
use App\Services\Mailer;
use App\Services\StatsStore;

final class OrderController extends Controller
{
    public function createForm(string $menuId): void
    {
        Auth::requireRole(['user','employee','admin'], 'Pour commander, veuillez vous connecter ou vous inscrire.');

        $id = (int)$menuId;
        $menu = Menu::find($id);

        if (!$menu) {
            http_response_code(404);
            echo 'Menu introuvable';
            return;
        }

        // Petite liste pour permettre de changer de menu dans le formulaire
        $menus = Menu::allWithFilters(['only_active' => 1]);

        $this->view('orders/create', [
            'menu'  => $menu,
            'menus' => $menus,
        ]);
    }

    public function create(string $menuId): void
    {
        Auth::requireRole(['user','employee','admin'], 'Pour commander, veuillez vous connecter ou vous inscrire.');

        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            Session::flash('error','Session expirée.');
            $this->redirect('/connexion');
        }

        $id = (int)$menuId;
        $menu = Menu::find($id);

        if (!$menu) {
            http_response_code(404);
            echo 'Menu introuvable';
            return;
        }

        $u = Auth::user();

        // ---- Données saisies ----
        $people = (int)($_POST['people_count'] ?? 0);
        $addr   = trim((string)($_POST['prestation_address'] ?? ''));
        $city   = trim((string)($_POST['prestation_city'] ?? ''));
        $date   = (string)($_POST['prestation_date'] ?? '');
        $time   = (string)($_POST['prestation_time'] ?? '');
        $km     = (float)($_POST['distance_km'] ?? 0);

        // Choix des plats (facultatif côté BDD, mais on veut un récap complet)
        $entreeId  = (int)($_POST['entree_id'] ?? 0);
        $platId    = (int)($_POST['plat_id'] ?? 0);
        $dessertId = (int)($_POST['dessert_id'] ?? 0);

        $dishIds = array_values(array_filter([$entreeId, $platId, $dessertId], fn($v)=>$v>0));

        // ---- Calcul prix ----
        $base = (float)$menu['base_price'];
        $min  = (int)$menu['min_people'];

        // Prix unitaire basé sur le prix affiché (pour le minimum)
        $unitPrice = ($min > 0) ? ($base / $min) : $base;

        // Prix menu recalculé selon nb personnes choisi
        $menuPrice = $unitPrice * max(0, $people);

        $discountRate = 0.0;
        if ($people >= ($min + 5)) {
            $discountRate = 0.10; // -10%
        }
        $discountAmount = $menuPrice * $discountRate;
        $menuPriceAfterDiscount = $menuPrice - $discountAmount;

        // Frais de déplacement (logique existante)
        $deliveryFee = 0.0;
        if (mb_strtolower($city) !== 'bordeaux' && $km > 0) {
            $deliveryFee = 5.0 + 0.59 * max(0.0, $km);
        }

        $total = $menuPriceAfterDiscount + $deliveryFee;

        // ---- Étape 1 : afficher le récap ----
        $isConfirm = (string)($_POST['confirm'] ?? '') === '1' || (string)($_POST['step'] ?? '') === 'confirm';

        if (!$isConfirm) {
    // retrouve les plats choisis pour l'affichage (si l'utilisateur en a choisi)
    $byId = [];
    foreach (($menu['dishes'] ?? []) as $d) {
        $did = (int)($d['dish_id'] ?? 0);
        if ($did > 0) $byId[$did] = $d;
    }

    $entree  = $entreeId  && isset($byId[$entreeId])  ? $byId[$entreeId]  : null;
    $plat    = $platId    && isset($byId[$platId])    ? $byId[$platId]    : null;
    $dessert = $dessertId && isset($byId[$dessertId]) ? $byId[$dessertId] : null;

    $orderDraft = [
        // utilisé par la vue recap.php
        'menu_id' => $id,
        'menu_title' => (string)$menu['title'],
        'people_count' => $people,

        'prestation_address' => $addr,
        'prestation_city' => $city,
        'prestation_date' => $date,
        'prestation_time' => $time,
        'distance_km' => $km,

        'menu_subtotal' => $menuPrice,
        'discount_amount' => $discountAmount,
        'delivery_fee' => $deliveryFee,
        'total' => $total,

        'entree' => $entree ? [
            'id' => (int)$entree['dish_id'],
            'name' => (string)$entree['name'],
            'description' => (string)$entree['description'],
        ] : null,

        'plat' => $plat ? [
            'id' => (int)$plat['dish_id'],
            'name' => (string)$plat['name'],
            'description' => (string)$plat['description'],
        ] : null,

        'dessert' => $dessert ? [
            'id' => (int)$dessert['dish_id'],
            'name' => (string)$dessert['name'],
            'description' => (string)$dessert['description'],
        ] : null,
    ];

    $this->view('orders/recap', [
        'order' => $orderDraft,
        'menu' => $menu,
        'csrf' => (string)($_POST['csrf'] ?? ''),
    ]);
    return;
}


        // ---- Étape 2 : validation (CGV) + enregistrement ----
        if (empty($_POST['accept_cgv'])) {
            Session::flash('error','Vous devez accepter les CGV pour valider la commande.');
            $this->redirect('/commander/' . $id);
        }

        if ($people < $min) {
            Session::flash('error','Le nombre de personnes minimum pour ce menu est de ' . $min . '.');
            $this->redirect('/commander/' . $id);
        }

        $orderId = Order::create([
            'user_id' => (int)$u['id'],
            'menu_id' => $id,
            'addr' => $addr,
            'city' => $city,
            'date' => $date,
            'time' => $time,
            'people' => $people,
            'menu_price' => $menuPriceAfterDiscount,
            'delivery_fee' => $deliveryFee,
            'total' => $total,
            'dish_ids' => $dishIds,
        ]);

        Mailer::send(
            $u['email'],
            'Confirmation commande',
            "Commande #{$orderId} enregistrée. Total : " . number_format($total,2,',',' ') . " €"
        );

        StatsStore::recordOrder([
            'order_id' => $orderId,
            'menu_id' => $id,
            'menu_title' => (string)($menu['title'] ?? ''),
            'created_date' => date('Y-m-d'),
            'total_price' => $total,
            'delivery_fee' => $deliveryFee,
            'people_count' => $people,
            'status' => 'confirmed',
        ]);
Session::flash('success','Votre commande a bien été confirmée.');
        $this->redirect('/menus');
    }
}
