<?php
declare(strict_types=1);

use App\Core\Url;

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        return Url::to($path);
    }
}
