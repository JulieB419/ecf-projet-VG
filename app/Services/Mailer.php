<?php
declare(strict_types=1);

namespace App\Services;

final class Mailer
{
    public static function send(string $to, string $subject, string $body): void
    {
        $line = sprintf("[%s] TO:%s | %s\n%s\n\n", date('c'), $to, $subject, $body);
        file_put_contents(__DIR__ . '/../../storage/logs/mail.log', $line, FILE_APPEND);
    }
}
