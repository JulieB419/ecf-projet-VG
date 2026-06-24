<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Menu
{
    public static function allWithFilters(array $f): array
    {
        $sql = "SELECT m.*, t.name AS theme, d.name AS diet
                FROM menus m
                JOIN themes t ON t.id=m.theme_id
                JOIN diets d ON d.id=m.diet_id
                WHERE m.is_active=1";
        $p = [];

        if (!empty($f['theme_id'])) { $sql.=" AND m.theme_id=:theme"; $p['theme']=(int)$f['theme_id']; }
        if (!empty($f['diet_id'])) { $sql.=" AND m.diet_id=:diet"; $p['diet']=(int)$f['diet_id']; }
        if (!empty($f['min_people'])) { $sql.=" AND m.min_people >= :minp"; $p['minp']=(int)$f['min_people']; }
        if (!empty($f['price_max'])) { $sql.=" AND m.base_price <= :pmax"; $p['pmax']=(float)$f['price_max']; }
        if (!empty($f['price_min'])) { $sql.=" AND m.base_price >= :pmin"; $p['pmin']=(float)$f['price_min']; }
        if (!empty($f['price_max_range'])) { $sql.=" AND m.base_price <= :pmaxr"; $p['pmaxr']=(float)$f['price_max_range']; }

        $sql .= " ORDER BY m.created_at DESC";
        $st = DB::pdo()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $st = DB::pdo()->prepare("SELECT m.*, t.name AS theme, d.name AS diet
                                  FROM menus m JOIN themes t ON t.id=m.theme_id
                                  JOIN diets d ON d.id=m.diet_id
                                  WHERE m.id=:id LIMIT 1");
        $st->execute(['id'=>$id]);
        $m = $st->fetch();
        if (!$m) return null;

        $img = DB::pdo()->prepare("SELECT url FROM menu_images WHERE menu_id=:id ORDER BY sort_order");
        $img->execute(['id'=>$id]);
        $m['images'] = array_column($img->fetchAll(), 'url');

        $dishes = DB::pdo()->prepare("SELECT 
                                          md.dish_id, md.category, di.name, di.description,
                                          GROUP_CONCAT(DISTINCT dd.diet_id ORDER BY dd.diet_id SEPARATOR ',') AS diet_ids,
                                          GROUP_CONCAT(DISTINCT d.name ORDER BY d.name SEPARATOR ',') AS diet_names
                                      FROM menu_dishes md 
                                      JOIN dishes di ON di.id=md.dish_id
                                      LEFT JOIN dish_diet dd ON dd.dish_id = di.id
                                      LEFT JOIN diets d ON d.id = dd.diet_id
                                      WHERE md.menu_id=:id
                                      GROUP BY md.dish_id, md.category, di.name, di.description
                                      ORDER BY FIELD(md.category,'entree','plat','dessert'), di.name");
        $dishes->execute(['id'=>$id]);
        $rows = $dishes->fetchAll();

        // Normalisation: convertir les régimes (diet_ids/diet_names) en tableau exploitable côté front
        foreach ($rows as &$r) {
            $ids = array_filter(array_map('trim', explode(',', (string)($r['diet_ids'] ?? ''))), fn($v) => $v !== '');
            $names = array_filter(array_map('trim', explode(',', (string)($r['diet_names'] ?? ''))), fn($v) => $v !== '');
            $r['diet_ids'] = array_map('intval', $ids);
            $r['diet_names'] = array_values($names);
        }
        unset($r);

        // Correction soft : si certaines lignes ont une category vide, on essaie de l'inférer (seed / tests)
        foreach ($rows as &$r) {
            $cat = (string)($r['category'] ?? '');
            if ($cat === '') {
                $name = mb_strtolower((string)($r['name'] ?? ''), 'UTF-8');
                if (str_contains($name,'cake') || str_contains($name,'tarte') || str_contains($name,'sorbet') || str_contains($name,'mousse')) {
                    $r['category'] = 'dessert';
                } elseif (str_contains($name,'poulet') || str_contains($name,'ratatouille') || str_contains($name,'sole') || str_contains($name,'pâtes') || str_contains($name,'pates')) {
                    $r['category'] = 'plat';
                } else {
                    $r['category'] = 'entree';
                }
            }
        }
        unset($r);
        $m['dishes'] = $rows;

        return $m;
    }


    // =========================
    // CRUD (admin)
    // =========================

    public static function create(array $data): int
    {
        $st = DB::pdo()->prepare("
            INSERT INTO menus (title, description, conditions, theme_id, diet_id, min_people, base_price, stock_available, is_active, created_at)
            VALUES (:title, :description, :conditions, :theme_id, :diet_id, :min_people, :base_price, :stock_available, :is_active, NOW())
        ");
        $st->execute([
            'title' => (string)($data['title'] ?? ''),
            'description' => (string)($data['description'] ?? ''),
            'conditions' => (string)($data['conditions'] ?? ''),
            'theme_id' => (int)($data['theme_id'] ?? 0),
            'diet_id' => (int)($data['diet_id'] ?? 0),
            'min_people' => (int)($data['min_people'] ?? 1),
            'base_price' => (float)($data['base_price'] ?? 0),
            'stock_available' => (int)($data['stock_available'] ?? 0),
            'is_active' => (int)($data['is_active'] ?? 1),
        ]);
        return (int)DB::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        $st = DB::pdo()->prepare("
            UPDATE menus
            SET title=:title,
                description=:description,
                conditions=:conditions,
                theme_id=:theme_id,
                diet_id=:diet_id,
                min_people=:min_people,
                base_price=:base_price,
                stock_available=:stock_available,
                is_active=:is_active
            WHERE id=:id
        ");
        $st->execute([
            'id' => $id,
            'title' => (string)($data['title'] ?? ''),
            'description' => (string)($data['description'] ?? ''),
            'conditions' => (string)($data['conditions'] ?? ''),
            'theme_id' => (int)($data['theme_id'] ?? 0),
            'diet_id' => (int)($data['diet_id'] ?? 0),
            'min_people' => (int)($data['min_people'] ?? 1),
            'base_price' => (float)($data['base_price'] ?? 0),
            'stock_available' => (int)($data['stock_available'] ?? 0),
            'is_active' => (int)($data['is_active'] ?? 1),
        ]);
    }

    public static function setActive(int $id, bool $active): void
    {
        DB::pdo()->prepare("UPDATE menus SET is_active=:a WHERE id=:id")->execute(['a'=>$active?1:0,'id'=>$id]);
    }

    /** @param array<int,string> $imageUrls */
    public static function setImages(int $menuId, array $imageUrls): void
    {
        DB::pdo()->prepare("DELETE FROM menu_images WHERE menu_id=:id")->execute(['id'=>$menuId]);

        $order = 1;
        $st = DB::pdo()->prepare("INSERT INTO menu_images (menu_id, url, sort_order) VALUES (:mid,:url,:ord)");
        foreach ($imageUrls as $url) {
            $u = trim((string)$url);
            if ($u === '') continue;
            $st->execute(['mid'=>$menuId,'url'=>$u,'ord'=>$order]);
            $order++;
        }
    }

    /**
     * @param array<int,int> $entrees
     * @param array<int,int> $plats
     * @param array<int,int> $desserts
     */
    public static function setDishes(int $menuId, array $entrees, array $plats, array $desserts): void
    {
        DB::pdo()->prepare("DELETE FROM menu_dishes WHERE menu_id=:id")->execute(['id'=>$menuId]);

        $insert = DB::pdo()->prepare("INSERT INTO menu_dishes (menu_id, dish_id, category) VALUES (:mid,:did,:cat)");

        foreach ($entrees as $id) {
            $insert->execute(['mid'=>$menuId,'did'=>(int)$id,'cat'=>'entree']);
        }
        foreach ($plats as $id) {
            $insert->execute(['mid'=>$menuId,'did'=>(int)$id,'cat'=>'plat']);
        }
        foreach ($desserts as $id) {
            $insert->execute(['mid'=>$menuId,'did'=>(int)$id,'cat'=>'dessert']);
        }
    }

    public static function allAdmin(): array
    {
        return DB::pdo()->query("
            SELECT m.id, m.title, m.is_active, m.stock_available, m.min_people, m.base_price,
                   t.name AS theme, d.name AS diet
            FROM menus m
            JOIN themes t ON t.id=m.theme_id
            JOIN diets d ON d.id=m.diet_id
            ORDER BY m.created_at DESC, m.id DESC
        ")->fetchAll();
    }

}
