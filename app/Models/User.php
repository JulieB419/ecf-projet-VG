<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class User
{
    public int $id;
    public string $role;
    public string $email;
    public string $password_hash;
    public string $first_name;
    public string $last_name;

    public static function findByEmail(string $email): ?self
    {
        $st = DB::pdo()->prepare("SELECT * FROM users WHERE email = :e LIMIT 1");
        $st->execute(['e'=>$email]);
        $r = $st->fetch();
        return $r ? self::fromRow($r) : null;
    }

    public static function findById(int $id): ?self
    {
        $st = DB::pdo()->prepare("SELECT * FROM users WHERE id = :id LIMIT 1");
        $st->execute(['id'=>$id]);
        $r = $st->fetch();
        return $r ? self::fromRow($r) : null;
    }

    public static function create(array $data): self
    {
        DB::pdo()->prepare("
          INSERT INTO users (role,email,password_hash,first_name,last_name,phone,address,is_active,created_at)
          VALUES (:role,:email,:password_hash,:first_name,:last_name,:phone,:address,1,NOW())
        ")->execute($data);

        return self::findById((int)DB::pdo()->lastInsertId());
    }

    public static function setActive(int $id, bool $active): void
    {
        DB::pdo()->prepare("UPDATE users SET is_active=:a WHERE id=:id")->execute(['a'=>$active?1:0,'id'=>$id]);
    }

    public static function isActive(int $id): bool
    {
        $st = DB::pdo()->prepare("SELECT is_active FROM users WHERE id=:id");
        $st->execute(['id'=>$id]);
        $r = $st->fetch();
        return $r ? ((int)$r['is_active']===1) : false;
    }

    private static function fromRow(array $r): self
    {
        $u = new self();
        $u->id = (int)$r['id'];
        $u->role = (string)$r['role'];
        $u->email = (string)$r['email'];
        $u->password_hash = (string)$r['password_hash'];
        $u->first_name = (string)$r['first_name'];
        $u->last_name = (string)$r['last_name'];
        return $u;
    }
}
