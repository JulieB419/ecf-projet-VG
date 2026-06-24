<?php
declare(strict_types=1);

namespace App\Core;

final class Env
{
    public static function load(string $envPath, string $fallbackPath): void
    {
        $path = is_file($envPath) ? $envPath : $fallbackPath;
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;

            [$k, $v] = array_pad(explode('=', $line, 2), 2, '');
            $k = trim($k);
            $v = trim($v);
            if ($k === '') continue;

            if (!isset($_ENV[$k])) {
                $_ENV[$k] = $v;
                putenv($k . '=' . $v);
            }
        }
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $val = $_ENV[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') return $default;
        return (string)$val;
    }
}
