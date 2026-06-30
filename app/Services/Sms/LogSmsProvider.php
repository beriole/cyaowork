<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

/** Driver de développement : écrit le SMS dans les logs au lieu de l'envoyer. */
class LogSmsProvider implements SmsProvider
{
    public function send(string $to, string $message): bool
    {
        Log::channel(config('logging.default'))->info("[SMS] → {$to} : {$message}");

        return true;
    }
}
