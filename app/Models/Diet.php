<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\DB;

final class Diet { public static function all(): array { return DB::pdo()->query("SELECT id,name FROM diets ORDER BY name")->fetchAll(); } }
