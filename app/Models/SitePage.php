<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class SitePage
{
    private static bool $ready = false;

    private static function ensureTable(): void
    {
        if (self::$ready) {
            return;
        }

        $pdo = DB::pdo();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_pages (
                slug VARCHAR(100) NOT NULL PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                content LONGTEXT NULL,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");

        self::$ready = true;
    }

    public static function get(string $slug): ?array
    {
        self::ensureTable();

        $pdo = DB::pdo();
        $st = $pdo->prepare("
            SELECT slug, title, content, updated_at
            FROM site_pages
            WHERE slug = ?
            LIMIT 1
        ");
        $st->execute([$slug]);

        $page = $st->fetch();
        return $page !== false ? $page : null;
    }

    public static function upsert(string $slug, string $title, string $content): void
    {
        self::ensureTable();

        $pdo = DB::pdo();
        $st = $pdo->prepare("
            INSERT INTO site_pages (slug, title, content)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE
                title = VALUES(title),
                content = VALUES(content),
                updated_at = CURRENT_TIMESTAMP
        ");
        $st->execute([$slug, $title, $content]);
    }
}
