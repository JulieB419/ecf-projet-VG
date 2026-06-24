<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth
{
    public static function user(): ?array { return $_SESSION['user'] ?? null; }
    public static function check(): bool { return !empty($_SESSION['user']); }

    public static function login(User $user): void
    {
        $_SESSION['user'] = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
        ];
        session_regenerate_id(true);
    }

    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    /**
     * Require one of the given roles.
     * - If not logged in: redirects to /connexion with a flash message.
     * - If logged in but not allowed: returns 403.
     */
    public static function requireRole(array $roles, ?string $loginMessage = null): void
    {
        $u = self::user();

        if (!$u) {
            // Remember intent so we can redirect back after login.
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            Session::flash('error', $loginMessage ?: 'Pour accéder à cette page, veuillez vous connecter.');
            self::redirect('/connexion');
        }

        if (!in_array($u['role'] ?? '', $roles, true)) {
            http_response_code(403);
            echo 'Accès refusé.';
            exit;
        }
    }

    /**
     * Helper de redirection interne (évite une fonction globale manquante).
     */
    private static function redirect(string $to): void
    {
        Url::redirect(trim($to));
    }
}
