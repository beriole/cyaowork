<?php

namespace App\Services\Payment;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Driver Fapshi (https://fapshi.com) — agrégateur Mobile Money camerounais (MTN MoMo & Orange Money).
 *
 * Flux : direct-pay déclenche un push USSD vers le téléphone du payeur (statut PENDING),
 * puis Fapshi notifie l'application via webhook lorsque le paiement aboutit (SUCCESSFUL/FAILED/EXPIRED).
 * Docs : POST /direct-pay, GET /payment-status/{transId}.
 */
class FapshiProvider implements PaymentProvider
{
    /** @param array{base_url:string, api_user:?string, api_key:?string} $config */
    public function __construct(private array $config) {}

    private function client(): PendingRequest
    {
        if (empty($this->config['api_user']) || empty($this->config['api_key'])) {
            throw new RuntimeException('Identifiants Fapshi manquants (FAPSHI_API_USER / FAPSHI_API_KEY).');
        }

        return Http::baseUrl(rtrim($this->config['base_url'], '/'))
            ->withHeaders(['apiuser' => $this->config['api_user'], 'apikey' => $this->config['api_key']])
            ->acceptJson()
            ->timeout(30);
    }

    public function initiate(float $amount, string $phone, string $reference, string $description): array
    {
        $response = $this->client()->post('/direct-pay', [
            'amount' => (int) round($amount),                 // entier, >= 100 XAF
            'phone' => $this->localPhone($phone),             // 6XXXXXXXX (9 chiffres)
            'medium' => $this->operatorFor($phone) === 'orange' ? 'orange money' : 'mobile money',
            'externalId' => $reference,                       // notre référence (renvoyée par le webhook)
            'message' => Str::limit($description, 80, ''),
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Fapshi direct-pay a échoué : '.$response->json('message', $response->body()));
        }

        return [
            'operator_reference' => (string) $response->json('transId'),
            'status' => 'pending',
        ];
    }

    public function status(string $operatorReference): string
    {
        $response = $this->client()->get('/payment-status/'.$operatorReference);

        return $this->normalize($response->json('status', 'PENDING'));
    }

    public function operatorFor(string $phone): string
    {
        // Préfixes Cameroun : Orange (655-659, 69x), MTN sinon.
        $local = Str::substr(preg_replace('/\D/', '', $phone), -9);
        $p3 = Str::substr($local, 0, 3);

        return (in_array($p3, ['655', '656', '657', '658', '659'], true) || Str::startsWith($local, '69'))
            ? 'orange' : 'mtn';
    }

    /** Mappe les statuts Fapshi vers notre vocabulaire interne. */
    public function normalize(string $fapshiStatus): string
    {
        return match (Str::upper($fapshiStatus)) {
            'SUCCESSFUL' => 'success',
            'FAILED', 'EXPIRED' => 'failed',
            default => 'pending', // CREATED, PENDING
        };
    }

    private function localPhone(string $phone): string
    {
        return Str::substr(preg_replace('/\D/', '', $phone), -9);
    }
}
