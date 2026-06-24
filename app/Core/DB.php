<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
    $host = 'mysql-jdevv.alwaysdata.net';
    $port = 3306;
    $dbname = 'jdevv_vite_gourmand';
    $user = 'jdevv';
    $pass = 'Adminn1234!!';

    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

    self::$pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}

return self::$pdo;
    }
}
