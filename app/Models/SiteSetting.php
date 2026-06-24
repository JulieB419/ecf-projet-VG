<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

/**
 * Simple key/value store for global site settings (address, phone, etc.).
 * Table is created lazily on first access to avoid manual migrations on shared hosts.
 */
final class SiteSetting
{
    private static bool $ready = false;

    private static function ensureTable(): void
    {
        if (self::$ready) return;
        $pdo = DB::pdo();
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS site_settings (
                `key` VARCHAR(100) NOT NULL PRIMARY KEY,
                `value` TEXT NULL,
                `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        self::$ready = true;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        self::ensureTable();
        $pdo = DB::pdo();
        $st = $pdo->prepare("SELECT value FROM site_settings WHERE `key`=? LIMIT 1");
        $st->execute([$key]);
        $val = $st->fetchColumn();
        if ($val === false || $val === null || $val === '') return $default;
        return (string)$val;
    }

    public static function set(string $key, string $value): void
    {
        self::ensureTable();
        $pdo = DB::pdo();
        $st = $pdo->prepare("INSERT INTO site_settings(`key`,`value`) VALUES(?,?)
            ON DUPLICATE KEY UPDATE `value`=VALUES(`value`), updated_at=CURRENT_TIMESTAMP");
        $st->execute([$key, $value]);
    }

    /** @return array<string,string> */
    public static function all(): array
    {
        self::ensureTable();
        $pdo = DB::pdo();
        $rows = $pdo->query("SELECT `key`,`value` FROM site_settings")->fetchAll();
        $out = [];
        foreach ($rows as $r) $out[(string)$r['key']] = (string)($r['value'] ?? '');
        return $out;
    }

    public static function updatedAt(string $key): ?string
    {
        self::ensureTable();
        $pdo = DB::pdo();
        $st = $pdo->prepare("SELECT updated_at FROM site_settings WHERE `key`=? LIMIT 1");
        $st->execute([$key]);
        $val = $st->fetchColumn();
        return $val ? (string)$val : null;
    }
}
