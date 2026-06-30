<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Driver générique pour une passerelle SMS HTTP (ex. opérateurs / agrégateurs camerounais).
 * Configurable via services.sms.http (endpoint, token, sender). L'interface reste identique
 * quel que soit le fournisseur.
 */
class HttpSmsProvider implements SmsProvider
{
    /** @param array{endpoint:?string, token:?string, sender:?string} $config */
    public function __construct(private array $config) {}

    public function send(string $to, string $message): bool
    {
        if (empty($this->config['endpoint'])) {
            Log::warning('Passerelle SMS non configurée (services.sms.http.endpoint).');

            return false;
        }

        $response = Http::withToken($this->config['token'] ?? '')
            ->asJson()
            ->post($this->config['endpoint'], [
                'to' => $to,
                'from' => $this->config['sender'] ?? 'CyaoWork',
                'message' => $message,
            ]);

        return $response->successful();
    }
}
