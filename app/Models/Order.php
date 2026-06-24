<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Order
{
    private static function ensureOrderDishesTable(): void
    {
        // Crée la table si elle n'existe pas (pratique en environnement XAMPP).
        DB::pdo()->exec(
            "CREATE TABLE IF NOT EXISTS order_dishes (
                order_id INT(11) NOT NULL,
                dish_id  INT(11) NOT NULL,
                category ENUM('entree','plat','dessert') NOT NULL,
                PRIMARY KEY (order_id, category),
                KEY idx_order_dishes_dish (dish_id),
                CONSTRAINT fk_order_dishes_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
                CONSTRAINT fk_order_dishes_dish  FOREIGN KEY (dish_id)  REFERENCES dishes(id) ON DELETE RESTRICT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
    }

    public static function create(array $d): int
{
    // On ne passe JAMAIS $d directement à execute() : il peut contenir des clés en trop
    // (ex: dish_ids) ou des noms différents selon le controller.
    $params = [
        'user_id'            => (int)($d['user_id'] ?? 0),
        'menu_id'            => (int)($d['menu_id'] ?? 0),
        'prestation_address' => (string)($d['prestation_address'] ?? ($d['addr'] ?? '')),
        'prestation_city'    => (string)($d['prestation_city'] ?? ($d['city'] ?? '')),
        'prestation_date'    => (string)($d['prestation_date'] ?? ($d['date'] ?? '')),
        'prestation_time'    => (string)($d['prestation_time'] ?? ($d['time'] ?? '')),
        'people_count'       => (int)($d['people_count'] ?? ($d['people'] ?? 0)),
        'menu_price'         => (float)($d['menu_price'] ?? 0),
        'delivery_fee'       => (float)($d['delivery_fee'] ?? 0),
        'total_price'        => (float)($d['total_price'] ?? ($d['total'] ?? 0)),
    ];

    DB::pdo()->prepare("
      INSERT INTO orders (
        user_id, menu_id,
        prestation_address, prestation_city, prestation_date, prestation_time,
        people_count, menu_price, delivery_fee, total_price,
        status, created_at
      )
      VALUES (
        :user_id, :menu_id,
        :prestation_address, :prestation_city, :prestation_date, :prestation_time,
        :people_count, :menu_price, :delivery_fee, :total_price,
        'en_attente', NOW()
      )
    ")->execute($params);

    $id = (int)DB::pdo()->lastInsertId();

    DB::pdo()->prepare("INSERT INTO order_status_history (order_id,status,changed_by_user_id,changed_at)
                        VALUES (:oid,'en_attente',:uid,NOW())")
          ->execute(['oid' => $id, 'uid' => $params['user_id']]);

    // Optionnel : enregistre la composition (entrée/plat/dessert)
    // Format attendu : $d['dish_ids'] = ['entree'=>12, 'plat'=>34, 'dessert'=>56]
    if (!empty($d['dish_ids']) && is_array($d['dish_ids'])) {
        self::ensureOrderDishesTable();
        $st = DB::pdo()->prepare("INSERT INTO order_dishes (order_id, dish_id, category) VALUES (:oid,:did,:cat)");
        foreach (['entree','plat','dessert'] as $cat) {
            if (!empty($d['dish_ids'][$cat])) {
                $st->execute(['oid' => $id, 'did' => (int)$d['dish_ids'][$cat], 'cat' => $cat]);
            }
        }
    }

    return $id;
}

    public static function getDishesForOrder(int $orderId): array
    {
        self::ensureOrderDishesTable();
        $st = DB::pdo()->prepare("SELECT od.category, d.id, d.name, d.description
                                  FROM order_dishes od
                                  JOIN dishes d ON d.id = od.dish_id
                                  WHERE od.order_id = :oid
                                  ORDER BY FIELD(od.category,'entree','plat','dessert')");
        $st->execute(['oid'=>$orderId]);
        return $st->fetchAll();
    }

    public static function listForUser(int $userId): array
    {
        $st = DB::pdo()->prepare("SELECT o.id,o.status,o.total_price,o.created_at,m.title AS menu_title
                                  FROM orders o JOIN menus m ON m.id=o.menu_id
                                  WHERE o.user_id=:uid ORDER BY o.created_at DESC");
        $st->execute(['uid'=>$userId]);
        return $st->fetchAll();
    }

    public static function findForUser(int $orderId, int $userId): ?array
    {
        $st = DB::pdo()->prepare("SELECT o.*, m.title AS menu_title FROM orders o JOIN menus m ON m.id=o.menu_id
                                  WHERE o.id=:oid AND o.user_id=:uid LIMIT 1");
        $st->execute(['oid'=>$orderId,'uid'=>$userId]);
        $o = $st->fetch();
        if (!$o) return null;

        $h = DB::pdo()->prepare("SELECT status,changed_at FROM order_status_history WHERE order_id=:oid ORDER BY changed_at");
        $h->execute(['oid'=>$orderId]);
        $o['history'] = $h->fetchAll();
        return $o;
    }

    public static function canUserCancel(array $order): bool { return $order['status'] === 'en_attente'; }

    public static function cancelByUser(int $orderId, int $userId): void
    {
        DB::pdo()->prepare("UPDATE orders SET status='annulee' WHERE id=:oid AND user_id=:uid AND status='en_attente'")
              ->execute(['oid'=>$orderId,'uid'=>$userId]);

        DB::pdo()->prepare("INSERT INTO order_status_history (order_id,status,changed_by_user_id,changed_at)
                            VALUES (:oid,'annulee',:uid,NOW())")
              ->execute(['oid'=>$orderId,'uid'=>$userId]);
    }

    public static function listForEmployee(array $f): array
    {
        $sql = "SELECT o.*, u.email, u.first_name, u.last_name, m.title AS menu_title
                FROM orders o JOIN users u ON u.id=o.user_id JOIN menus m ON m.id=o.menu_id WHERE 1=1";
        $p = [];
        if (!empty($f['status'])) { $sql.=" AND o.status=:s"; $p['s']=$f['status']; }
        if (!empty($f['q'])) { $sql.=" AND (u.email LIKE :q OR u.first_name LIKE :q OR u.last_name LIKE :q)"; $p['q']='%'.$f['q'].'%'; }
        $sql.=" ORDER BY o.created_at DESC";
        $st = DB::pdo()->prepare($sql);
        $st->execute($p);
        return $st->fetchAll();
    }

    public static function findAny(int $orderId): ?array
    {
        $st = DB::pdo()->prepare("SELECT o.*, u.email, u.first_name, u.last_name, m.title AS menu_title
                                  FROM orders o JOIN users u ON u.id=o.user_id JOIN menus m ON m.id=o.menu_id
                                  WHERE o.id=:oid LIMIT 1");
        $st->execute(['oid'=>$orderId]);
        $o = $st->fetch();
        if (!$o) return null;

        $h = DB::pdo()->prepare("SELECT status,changed_at FROM order_status_history WHERE order_id=:oid ORDER BY changed_at");
        $h->execute(['oid'=>$orderId]);
        $o['history'] = $h->fetchAll();
        return $o;
    }

    public static function updateStatus(int $orderId, string $status, int $changedBy): void
    {
        DB::pdo()->prepare("UPDATE orders SET status=:s WHERE id=:oid")->execute(['s'=>$status,'oid'=>$orderId]);
        DB::pdo()->prepare("INSERT INTO order_status_history (order_id,status,changed_by_user_id,changed_at)
                            VALUES (:oid,:s,:uid,NOW())")
              ->execute(['oid'=>$orderId,'s'=>$status,'uid'=>$changedBy]);
    }

    public static function cancelByEmployee(int $orderId, int $employeeId, string $contactMode, string $contactDate, string $reason): void
    {
        DB::pdo()->prepare("UPDATE orders SET status='annulee' WHERE id=:oid")->execute(['oid'=>$orderId]);
        DB::pdo()->prepare("INSERT INTO order_cancellations (order_id,cancelled_by_user_id,contact_mode,contact_date,reason,created_at)
                            VALUES (:oid,:uid,:mode,:cdate,:reason,NOW())")
              ->execute(['oid'=>$orderId,'uid'=>$employeeId,'mode'=>$contactMode,'cdate'=>$contactDate,'reason'=>$reason]);

        self::updateStatus($orderId,'annulee',$employeeId);
    }
}
