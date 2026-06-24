<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Review
{
    public static function create(int $orderId, int $userId, int $rating, string $comment): void
    {
        DB::pdo()->prepare("INSERT INTO reviews (order_id,user_id,rating,comment,status,created_at)
                            VALUES (:oid,:uid,:r,:c,'en_attente',NOW())")
              ->execute(['oid'=>$orderId,'uid'=>$userId,'r'=>$rating,'c'=>$comment]);
    }

    public static function validatedForHome(): array
    {
        return DB::pdo()->query("SELECT r.rating,r.comment,r.created_at,u.first_name
                                 FROM reviews r JOIN users u ON u.id=r.user_id
                                 WHERE r.status='valide' ORDER BY r.created_at DESC LIMIT 10")->fetchAll();
    }

    public static function pending(): array
    {
        return DB::pdo()->query("SELECT r.*, u.email FROM reviews r JOIN users u ON u.id=r.user_id
                                 WHERE r.status='en_attente' ORDER BY r.created_at DESC")->fetchAll();
    }

    public static function setStatus(int $id, string $status): void
    {
        DB::pdo()->prepare("UPDATE reviews SET status=:s WHERE id=:id")->execute(['s'=>$status,'id'=>$id]);
    }
}
