<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Review;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->view('home', ['reviews'=>Review::validatedForHome()]);
    }
}
