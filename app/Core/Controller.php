<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function view(string $template, array $data = []): void
    {
        extract($data);
        $flashSuccess = Session::flash('success');
        $flashError = Session::flash('error');
        require __DIR__ . '/../../views/layout.php';
    }

    protected function redirect(string $to): void
    {
        // Sous XAMPP, l'appli tourne souvent dans un sous-dossier
        // (ex : /vite-gourmand/public). On préfixe donc les redirections
        // avec le "base path" du front controller (public/index.php).
        if (preg_match('#^https?://#i', $to)) {
            header('Location: ' . $to);
            exit;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = rtrim(str_replace('/index.php', '', $scriptName), '/');

        if ($to === '') {
            $to = '/';
        }
        if ($to[0] !== '/') {
            $to = '/' . $to;
        }

        // Sécurité : si $to contient déjà le base path (ex: /vite-gourmand/public/administration),
        // on évite de le préfixer une 2e fois.
        if ($base !== '' && strpos($to, $base . '/') === 0) {
            header('Location: ' . $to);
            exit;
        }

        header('Location: ' . $base . $to);
        exit;
    }

    protected function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
