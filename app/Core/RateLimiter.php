<?php
declare(strict_types=1);

namespace App\Core;

final class RateLimiter
{
    public static function hit(string $key, int $maxAttempts, int $windowSeconds): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $file = __DIR__ . '/../../storage/cache/rl_' . md5($key . '|' . $ip) . '.json';

        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $now = time();
        $data = ['count'=>0, 'reset'=>$now + $windowSeconds];

        if (is_file($file)) {
            $tmp = json_decode((string)file_get_contents($file), true);
            if (is_array($tmp)) $data = array_merge($data, $tmp);
        }

        if ($now > (int)$data['reset']) $data = ['count'=>0,'reset'=>$now + $windowSeconds];

        $data['count'] = (int)$data['count'] + 1;
        file_put_contents($file, json_encode($data));

        return $data['count'] <= $maxAttempts;
    }
}
