<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Session;
use App\Models\OpeningHours;

final class OpeningHoursController extends Controller
{
    public function editForm(): void
    {
        Auth::requireRole(['employee','admin']);
        $this->view('employee/opening_hours', ['hours'=>OpeningHours::all()]);
    }

    public function update(): void
    {
        Auth::requireRole(['employee','admin']);
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) $this->redirect('/espace-employe/horaires');

        $rows = [];
        foreach ($_POST['day'] ?? [] as $day => $vals) {
            $rows[] = [
                'day_of_week'=>(int)$day,
                'open_time'=>(string)($vals['open'] ?? '09:00'),
                'close_time'=>(string)($vals['close'] ?? '18:00'),
                'is_closed'=>!empty($vals['closed']),
            ];
        }
        OpeningHours::updateMany($rows);
        Session::flash('success','Horaires mis à jour.');
        $this->redirect('/espace-employe/horaires');
    }
}
