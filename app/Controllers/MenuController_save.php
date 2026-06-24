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
            'themes'=>Theme::all(),
            'diets'=>Diet::all(),
            'menus'=>Menu::allWithFilters([]),
        ]);
    }

    public function apiIndex(): void
    {
        $filters = [
            'price_max' => $_GET['price_max'] ?? null,
            'price_min' => $_GET['price_min'] ?? null,
            'price_max_range' => $_GET['price_max_range'] ?? null,
            'theme_id' => $_GET['theme_id'] ?? null,
            'diet_id' => $_GET['diet_id'] ?? null,
            'min_people' => $_GET['min_people'] ?? null,
        ];
        $menus = Menu::allWithFilters($filters);
        $items = array_map(fn($m)=>[
            'id'=>(int)$m['id'],
            'title'=>$m['title'],
            'short_description'=>mb_strimwidth((string)$m['description'],0,120,'…'),
            'min_people'=>(int)$m['min_people'],
            'base_price'=>(float)$m['base_price'],
            'theme'=>$m['theme'],
            'diet'=>$m['diet'],
        ], $menus);

        $this->json(['items'=>$items]);
    }

   public function show(string $id): void
{
    $menuId = (int) $id;

    if ($menuId <= 0) {
        http_response_code(404);
        echo "Menu introuvable";
        return;
    }

    // ⚠️ ensuite tu continues ton code EXISTANT
    // mais en utilisant $menuId à la place de $id ou $params['id']

    // Exemple (à adapter à ton code actuel) :
    // $menu = Menu::find($menuId);
    // ...
}

}
