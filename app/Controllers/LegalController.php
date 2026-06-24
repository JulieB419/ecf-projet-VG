<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\SitePage;

final class LegalController extends Controller
{
    public function legal(): void
    {
        $page = SitePage::get('mentions_legales');
        if (!$page) {
            $default = @file_get_contents(__DIR__ . '/../Views/legal/legal_default.html')
                ?: @file_get_contents(__DIR__ . '/../Views/legal/legal.php')
                ?: '';
            SitePage::upsert('mentions_legales', 'Mentions légales', $default);
            $page = SitePage::get('mentions_legales');
        }

        $this->view('legal/legal', ['page' => $page]);
    }

    public function cgv(): void
    {
        $page = SitePage::get('cgv');
        if (!$page) {
            $default = @file_get_contents(__DIR__ . '/../Views/legal/cgv_default.html')
                ?: @file_get_contents(__DIR__ . '/../Views/legal/cgv.php')
                ?: '';
            SitePage::upsert('cgv', 'Conditions générales de vente', $default);
            $page = SitePage::get('cgv');
        }

        $this->view('legal/cgv', ['page' => $page]);
    }
}
