<?php
declare(strict_types=1);

namespace App\Core;

final class Session
{
    public static function start(): void
    {
        $alreadyActive = (session_status() === PHP_SESSION_ACTIVE);

        if (!$alreadyActive) {
            $secure = false; // en prod : true (https)
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);

            session_start();
        }

        // Toujours garantir un token CSRF, même si la session était déjà active
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(16));
        }
    }

    public static function csrf(): string
    {
        return (string)($_SESSION['csrf'] ?? '');
    }

    public static function checkCsrf(?string $token): bool
    {
        return is_string($token) && hash_equals(self::csrf(), $token);
    }

    public static function flash(string $key, ?string $value = null): ?string
    {
        if ($value !== null) { $_SESSION['_flash'][$key] = $value; return null; }
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
}
