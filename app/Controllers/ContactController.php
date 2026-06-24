<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\RateLimiter;
use App\Services\Mailer;

final class ContactController extends Controller
{
    public function form(): void { $this->view('contact/form'); }

    public function send(): void
    {
        if (!RateLimiter::hit('contact',8,60)) { Session::flash('error','Trop de messages.'); $this->redirect('/contact'); }
        if (!Session::checkCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Session expirée.'); $this->redirect('/contact'); }

        if (!empty($_POST['website'] ?? '')) { Session::flash('success','Message envoyé.'); $this->redirect('/contact'); } // honeypot

        $email = trim((string)($_POST['email'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $message = trim((string)($_POST['message'] ?? ''));

        if ($email==='' || $title==='' || $message==='') { Session::flash('error','Champs obligatoires.'); $this->redirect('/contact'); }

        Mailer::send('contact@vite-gourmand.test', 'Contact : '.$title, "De : {$email}\n\n{$message}");
        Session::flash('success','Demande envoyée.');
        $this->redirect('/contact');
    }
}
