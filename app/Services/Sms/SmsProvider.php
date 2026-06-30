<?php

namespace App\Services\Sms;

interface SmsProvider
{
    /** Envoie un SMS au numéro donné. Retourne true si accepté par la passerelle. */
    public function send(string $to, string $message): bool;
}
