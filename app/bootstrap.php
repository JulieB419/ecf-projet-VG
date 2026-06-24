<?php
declare(strict_types=1);

require __DIR__ . '/Core/Autoload.php';

use App\Core\Env;
use App\Core\Session;

Env::load(__DIR__ . '/../.env', __DIR__ . '/../.env.example');
Session::start();

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
