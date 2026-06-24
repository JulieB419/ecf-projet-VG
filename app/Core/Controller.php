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
        require __DIR__ . '/../Views/layout.php';
    }

    protected function redirect(string $to): void
    {
        Url::redirect($to);
    }

    protected function json(array $payload): void
    {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
