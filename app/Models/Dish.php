<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;
use PDO;

final class Dish
{
    private static bool $schemaChecked = false;

    private static function ensureSchema(): void
    {
        if (self::$schemaChecked) return;
        self::$schemaChecked = true;

        $pdo = DB::pdo();
        try {
            $cols = $pdo->query('SHOW COLUMNS FROM dishes')->fetchAll(PDO::FETCH_COLUMN);
            $cols = array_map('strtolower', $cols ?: []);

            if (!in_array('type', $cols, true)) {
                $pdo->exec("ALTER TABLE dishes ADD COLUMN type VARCHAR(20) NOT NULL DEFAULT 'plat'");
            }
            if (!in_array('regimes', $cols, true)) {
                $pdo->exec("ALTER TABLE dishes ADD COLUMN regimes TEXT NULL");
            }
            if (!in_array('allergens', $cols, true)) {
                $pdo->exec("ALTER TABLE dishes ADD COLUMN allergens TEXT NULL");
            }
        } catch (\Throwable $e) {
            // The host may forbid ALTER in some environments.
        }
    }

    /** @return array<int, array<string,mixed>> */
    public static function all(): array
    {
        self::ensureSchema();
        return DB::pdo()->query("SELECT id, name, description, type, regimes, allergens FROM dishes ORDER BY name ASC")->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function find(int $id): ?array
    {
        self::ensureSchema();
        $st = DB::pdo()->prepare("SELECT id, name, description, type, regimes, allergens FROM dishes WHERE id=:id LIMIT 1");
        $st->execute(['id'=>$id]);
        $row = $st->fetch();
        return $row ?: null;
    }

    public static function create(array $data): int
    {
        self::ensureSchema();
        $st = DB::pdo()->prepare(
            "INSERT INTO dishes (name, description, type, regimes, allergens)
             VALUES (:name, :description, :type, :regimes, :allergens)"
        );
        $st->execute([
            'name' => (string)($data['name'] ?? ''),
            'description' => (string)($data['description'] ?? ''),
            'type' => (string)($data['type'] ?? 'plat'),
            'regimes' => $data['regimes'] ?? null,
            'allergens' => $data['allergens'] ?? null,
        ]);
        return (int)DB::pdo()->lastInsertId();
    }

    public static function update(int $id, array $data): void
    {
        self::ensureSchema();
        $st = DB::pdo()->prepare(
            "UPDATE dishes
             SET name=:name, description=:description, type=:type, regimes=:regimes, allergens=:allergens
             WHERE id=:id"
        );
        $st->execute([
            'id'=>$id,
            'name'=>(string)($data['name'] ?? ''),
            'description'=>(string)($data['description'] ?? ''),
            'type'=>(string)($data['type'] ?? 'plat'),
            'regimes'=>$data['regimes'] ?? null,
            'allergens'=>$data['allergens'] ?? null,
        ]);
    }

    public static function delete(int $id): void
    {
        // On supprime les liens menu_dishes pour éviter les FK
        DB::pdo()->prepare("DELETE FROM menu_dishes WHERE dish_id=:id")->execute(['id'=>$id]);
        DB::pdo()->prepare("DELETE FROM dishes WHERE id=:id")->execute(['id'=>$id]);
    }
}
