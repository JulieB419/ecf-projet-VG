<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Theme { public static function all(): array { return DB::pdo()->query("SELECT id,name FROM themes ORDER BY name")->fetchAll(); } }
