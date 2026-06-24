<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Auth;
use App\Core\RateLimiter;
use App\Core\DB;
use App\Models\User;
use App\Services\Mailer;

final class AuthController extends Controller
{
    /**
     * Construit le "base path" de l'app (ex: /vite-gourmand/public)
     * pour éviter les redirections vers http://localhost/... sans le préfixe.
     */
    private function basePath(): string
    {
        // SCRIPT_NAME = /vite-gourmand/public/index.php
        $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        return $dir === '' ? '' : $dir;
    }

    /**
     * Préfixe les chemins internes avec le basePath.
     * Ex: /connexion -> /vite-gourmand/public/connexion
     */
    private function toUrl(string $path): string
    {
        $path = '/' . ltrim($path, '/');
        return $this->basePath() . $path;
    }

    public function registerForm(): void
    {
        $this->view('auth/register');
    }

    public function register(): void
    {
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Session expirée.');
            $this->redirect($this->toUrl('/inscription'));
        }

        $first = trim((string)($_POST['first_name'] ?? ''));
        $last  = trim((string)($_POST['last_name'] ?? ''));
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $phone = trim((string)($_POST['phone'] ?? ''));
        $addr  = trim((string)($_POST['address'] ?? ''));
        $pw    = (string)($_POST['password'] ?? '');

        if (!$this->isPasswordStrong($pw)) {
            Session::flash('error', 'Mot de passe trop faible.');
            $this->redirect($this->toUrl('/inscription'));
        }

        if (User::findByEmail($email)) {
            Session::flash('error', 'Email déjà utilisé.');
            $this->redirect($this->toUrl('/inscription'));
        }

        User::create([
            'role'          => 'user',
            'email'         => $email,
            'password_hash' => password_hash($pw, PASSWORD_DEFAULT),
            'first_name'    => $first,
            'last_name'     => $last,
            'phone'         => $phone,
            'address'       => $addr,
        ]);

        Mailer::send($email, 'Bienvenue', 'Votre compte est créé. Vous pouvez vous connecter.');
        Session::flash('success', 'Compte créé.');
        $this->redirect($this->toUrl('/connexion'));
    }

    public function loginForm(): void
    {
        $this->view('auth/login');
    }

    public function login(): void
    {
        if (!RateLimiter::hit('login', 10, 60)) {
            Session::flash('error', 'Trop de tentatives.');
            $this->redirect($this->toUrl('/connexion'));
        }

        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Session expirée.');
            $this->redirect($this->toUrl('/connexion'));
        }

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $pw    = (string)($_POST['password'] ?? '');
        $user  = User::findByEmail($email);

        if (!$user) {
            Session::flash('error', "Adresse e-mail inconnue.");
            $this->redirect($this->toUrl('/connexion'));
        }

        $hash = (string)$user->password_hash;
        $ok = false;
        if ($hash !== '' && (str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$argon2'))) {
            $ok = password_verify($pw, $hash);
        } else {
            // Si la base a été importée avec un mot de passe en clair, on accepte une fois et on re-hash.
            $ok = ($hash !== '' && hash_equals($hash, $pw));
            if ($ok) {
                $newHash = password_hash($pw, PASSWORD_DEFAULT);
                DB::pdo()->prepare('UPDATE users SET password_hash = :h WHERE id = :id')
                    ->execute(['h' => $newHash, 'id' => $user->id]);
                $user->password_hash = $newHash;
            }
        }

        if (!$ok) {
            Session::flash('error', "Mot de passe incorrect.");
            $this->redirect($this->toUrl('/connexion'));
        }

        if (!User::isActive($user->id)) {
            Session::flash('error', 'Compte désactivé.');
            $this->redirect($this->toUrl('/connexion'));
        }

        Auth::login($user);
        Session::flash('success', 'Connexion réussie.');

        // Retour à la page demandée si on vient d'une redirection (ex: /commander/2)
        $after = $_SESSION['redirect_after_login'] ?? null;
        unset($_SESSION['redirect_after_login']);
        if (is_string($after) && $after !== '' && $after !== '/connexion') {
            $this->redirect($after);
        }

        if ($user->role === 'admin') {
            $this->redirect($this->toUrl('/administration'));
        }

        if ($user->role === 'employee') {
            $this->redirect($this->toUrl('/espace-employe'));
        }

        $this->redirect($this->toUrl('/profil'));
    }

    public function logout(): void
    {
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            $this->redirect($this->toUrl('/'));
        }

        Auth::logout();
        Session::flash('success', 'Déconnexion.');
        $this->redirect($this->toUrl('/'));
    }

    public function forgotForm(): void
    {
        $this->view('auth/forgot');
    }

    public function forgot(): void
    {
        if (!RateLimiter::hit('forgot', 5, 60)) {
            Session::flash('error', 'Trop de demandes.');
            $this->redirect($this->toUrl('/mot-de-passe-oublie'));
        }

        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Session expirée.');
            $this->redirect($this->toUrl('/mot-de-passe-oublie'));
        }

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $user  = User::findByEmail($email);

        if ($user) {
            $token = bin2hex(random_bytes(16));
            $this->storeResetToken($user->id, $token);

            // Important : lien interne avec le basePath (sinon ça redirige vers /reinitialiser... à la racine)
            $link = $this->toUrl('/reinitialiser-mot-de-passe')
                  . '?email=' . urlencode($email)
                  . '&token=' . urlencode($token);

            Mailer::send($email, 'Réinitialisation mot de passe', "Lien : {$link}\nExpire dans 30 min.");
        }

        Session::flash('success', "Si l'email existe, un lien a été envoyé.");
        $this->redirect($this->toUrl('/connexion'));
    }

    public function resetForm(): void
    {
        $this->view('auth/reset', [
            'email' => (string)($_GET['email'] ?? ''),
            'token' => (string)($_GET['token'] ?? ''),
        ]);
    }

    public function reset(): void
    {
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) {
            Session::flash('error', 'Session expirée.');
            $this->redirect($this->toUrl('/connexion'));
        }

        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $token = (string)($_POST['token'] ?? '');
        $pw    = (string)($_POST['password'] ?? '');

        if (!$this->isPasswordStrong($pw)) {
            Session::flash('error', 'Mot de passe trop faible.');
            $this->redirect(
                $this->toUrl('/reinitialiser-mot-de-passe')
                . '?email=' . urlencode($email)
                . '&token=' . urlencode($token)
            );
        }

        $user = User::findByEmail($email);
        if (!$user || !$this->checkResetToken($user->id, $token)) {
            Session::flash('error', 'Lien invalide ou expiré.');
            $this->redirect($this->toUrl('/connexion'));
        }

        DB::pdo()->prepare("UPDATE users SET password_hash=:h WHERE id=:id")
            ->execute(['h' => password_hash($pw, PASSWORD_DEFAULT), 'id' => $user->id]);

        DB::pdo()->prepare("DELETE FROM password_resets WHERE user_id=:uid")
            ->execute(['uid' => $user->id]);

        Session::flash('success', 'Mot de passe mis à jour.');
        $this->redirect($this->toUrl('/connexion'));
    }

    private function isPasswordStrong(string $pw): bool
    {
        if (mb_strlen($pw) < 10) return false;
        if (!preg_match('/[A-Z]/', $pw)) return false;
        if (!preg_match('/[a-z]/', $pw)) return false;
        if (!preg_match('/\d/', $pw)) return false;
        if (!preg_match('/[^A-Za-z0-9]/', $pw)) return false;
        return true;
    }

    private function storeResetToken(int $userId, string $token): void
    {
        DB::pdo()->prepare("DELETE FROM password_resets WHERE user_id=:uid")
            ->execute(['uid' => $userId]);

        DB::pdo()->prepare("
            INSERT INTO password_resets (user_id, token_hash, expires_at, created_at)
            VALUES (:uid, :th, DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())
        ")->execute([
            'uid' => $userId,
            'th'  => hash('sha256', $token),
        ]);
    }

    private function checkResetToken(int $userId, string $token): bool
    {
        $st = DB::pdo()->prepare("SELECT token_hash, expires_at FROM password_resets WHERE user_id=:uid LIMIT 1");
        $st->execute(['uid' => $userId]);
        $r = $st->fetch();

        if (!$r) return false;
        if (strtotime((string)$r['expires_at']) < time()) return false;

        return hash_equals((string)$r['token_hash'], hash('sha256', $token));
    }
}
