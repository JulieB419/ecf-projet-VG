<?php
declare(strict_types=1);

namespace App\Core;

final class Url
{
    public static function basePath(): string
    {
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
        $base = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

        if ($base === '/' || $base === '.') {
            return '';
        }

        return $base;
    }

    public static function to(string $path = ''): string
    {
        $base = self::basePath();

        if ($path === '') {
            return $base !== '' ? $base . '/' : '/';
        }

        return ($base !== '' ? $base : '') . '/' . ltrim($path, '/');
    }

    public static function redirect(string $to): void
    {
        if (preg_match('#^https?://#i', $to)) {
            header('Location: ' . $to);
            exit;
        }

        if ($to === '') {
            $to = '/';
        }

        if ($to[0] !== '/') {
            $to = '/' . $to;
        }

        $base = self::basePath();
        if ($base !== '' && str_starts_with($to, $base . '/')) {
            header('Location: ' . $to);
            exit;
        }

        header('Location: ' . $base . $to);
        exit;
    }
}
