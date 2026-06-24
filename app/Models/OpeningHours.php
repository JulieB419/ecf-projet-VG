<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class OpeningHours
{
    public static function all(): array
    {
        $rows = DB::pdo()->query("SELECT day_of_week,open_time,close_time,is_closed FROM opening_hours ORDER BY day_of_week")->fetchAll();

        // If the table is empty (fresh install), return sensible defaults for Mon..Sun (0..6)
        if (!$rows) {
            $defaults = [];
            for ($d = 0; $d <= 6; $d++) {
                $defaults[] = [
                    'day_of_week' => $d,
                    'open_time' => '09:00:00',
                    'close_time' => '18:00:00',
                    'is_closed' => 0,
                ];
            }
            return $defaults;
        }

        return $rows;
    }

    public static function updateMany(array $rows): void
    {
        $pdo = DB::pdo();
        $pdo->beginTransaction();

        // Requires a UNIQUE or PRIMARY KEY on (day_of_week). If missing, add it in SQL:
        // ALTER TABLE opening_hours ADD PRIMARY KEY(day_of_week);
        $st = $pdo->prepare(
            "INSERT INTO opening_hours (day_of_week, open_time, close_time, is_closed)
             VALUES (:d, :o, :c, :cl)
             ON DUPLICATE KEY UPDATE open_time=VALUES(open_time), close_time=VALUES(close_time), is_closed=VALUES(is_closed)"
        );

        foreach ($rows as $r) {
            $st->execute([
                'd' => (int)$r['day_of_week'],
                'o' => $r['open_time'],
                'c' => $r['close_time'],
                'cl' => $r['is_closed'] ? 1 : 0,
            ]);
        }

        $pdo->commit();
    }
}
