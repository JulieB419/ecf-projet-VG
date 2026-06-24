<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Menu;
use App\Models\Theme;
use App\Models\Diet;

final class MenuController extends Controller
{
    public function index(): void
    {
        $this->view('menus/index', [
            'themes' => Theme::all(),
            'diets'  => Diet::all(),
            'menus'  => Menu::allWithFilters([]),
        ]);
    }

    public function apiIndex(): void
    {
        $filters = [
            'price_max'        => $_GET['price_max'] ?? null,
            'price_min'        => $_GET['price_min'] ?? null,
            'price_max_range'  => $_GET['price_max_range'] ?? null,
            'theme_id'         => $_GET['theme_id'] ?? null,
            'diet_id'          => $_GET['diet_id'] ?? null,
            'min_people'       => $_GET['min_people'] ?? null,
        ];

        $menus = Menu::allWithFilters($filters);

        $items = array_map(fn($m) => [
            'id'              => (int) $m['id'],
            'title'           => $m['title'],
            'short_description'=> mb_strimwidth((string)$m['description'], 0, 120, '…'),
            'min_people'      => (int) $m['min_people'],
            'base_price'      => (float) $m['base_price'],
            'theme'           => $m['theme'],
            'diet'            => $m['diet'],
        ], $menus);

        $this->json(['items' => $items]);
    }

    /**
     * Détail d'un menu (JSON) – utile pour la page de commande.
     * Retourne aussi les plats disponibles, regroupés par catégorie.
     */
    public function apiShow(string $id): void
    {
        $menuId = (int) $id;
        if ($menuId <= 0) {
            http_response_code(404);
            $this->json(['error' => 'Menu introuvable']);
            return;
        }

        $menu = Menu::find($menuId);
        if (!$menu) {
            http_response_code(404);
            $this->json(['error' => 'Menu introuvable']);
            return;
        }

        $dishesByCategory = [
            'entree'  => [],
            'plat'    => [],
            'dessert' => [],
        ];

        foreach ($menu['dishes'] as $dish) {
            $cat = (string) ($dish['category'] ?? '');
            if ($cat !== '' && isset($dishesByCategory[$cat])) {
                $dishesByCategory[$cat][] = [
                    'id' => (int) ($dish['id'] ?? $dish['dish_id'] ?? 0),
                    'name' => $dish['name'],
                    'description' => $dish['description'],
                    'category' => $cat,
                    'diet_ids' => $dish['diet_ids'] ?? [],
                    'diet_names' => $dish['diet_names'] ?? [],
                ];
            }
        }


        // Régimes "options possibles" : union des régimes présents sur au moins 1 plat du menu
        $optionDietIds = [];
        foreach ($dishesByCategory as $cat => $list) {
            foreach ($list as $dish) {
                foreach (($dish['diet_ids'] ?? []) as $did) {
                    $optionDietIds[(int)$did] = true;
                }
            }
        }
        $optionDietIds = array_values(array_map('intval', array_keys($optionDietIds)));

        // Charger les libellés des régimes pour le filtre côté client
        $optionDiets = [];
        if (!empty($optionDietIds)) {
            $in = implode(',', array_fill(0, count($optionDietIds), '?'));
            $st = \App\Core\DB::pdo()->prepare("SELECT id, name FROM diets WHERE id IN ($in) ORDER BY name");
            $st->execute($optionDietIds);
            $optionDiets = $st->fetchAll();
        }
        $this->json([
            'id' => (int) $menu['id'],
            'title' => $menu['title'],
            'description' => $menu['description'],
            'conditions' => $menu['conditions'],
            'theme' => $menu['theme'] ?? null,
            'diet' => $menu['diet'] ?? null,
            'min_people' => (int) $menu['min_people'],
            'base_price' => (float) $menu['base_price'],
            'stock_available' => (int) $menu['stock_available'],
            'images' => $menu['images'] ?? [],
            'dishes' => $dishesByCategory,
            'option_diet_ids' => $optionDietIds,
            'option_diets' => $optionDiets,
        ]);
    }

    public function show(string $id): void
    {
    $menuId = (int) $id;

    if ($menuId <= 0) {
        http_response_code(404);
        echo 'Menu introuvable';
        return;
    }

    $menu = Menu::find($menuId);

    if (!$menu) {
        http_response_code(404);
        echo 'Menu introuvable';
        return;
    }

    // Organisation des plats par catégorie
    $dishesByCategory = [
        'entree'  => [],
        'plat'    => [],
        'dessert' => [],
    ];

    foreach ($menu['dishes'] as $dish) {
        if (isset($dishesByCategory[$dish['category']])) {
            $dishesByCategory[$dish['category']][] = $dish;
        }
    }

    // Options de régime disponibles sur ce menu (agrégées depuis les plats)
$optionDietIdsMap = [];
foreach ($menu['dishes'] as $dish) {
    foreach (($dish['diet_ids'] ?? []) as $did) {
        $optionDietIdsMap[(int)$did] = true;
    }
}
$optionDietIds = array_values(array_keys($optionDietIdsMap));
sort($optionDietIds);

$allDiets = Diet::all();
$optionDiets = array_values(array_filter($allDiets, function ($d) use ($optionDietIds) {
    return in_array((int)($d['id'] ?? 0), $optionDietIds, true);
}));

    $this->view('menus/show', [
        'menu' => $menu,
        'dishes' => $dishesByCategory,
            'option_diet_ids' => $optionDietIds,
            'option_diets' => $optionDiets,
        'images' => $menu['images'],
    ]);
    }
}