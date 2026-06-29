<?php

namespace App\Services\Payment;

use Illuminate\Support\Str;

/**
 * Driver de démonstration (sandbox) simulant un agrégateur local (Campay / MeSomb).
 * En production, remplacer par un driver appelant l'API réelle ; l'interface reste identique.
 */
class SandboxProvider implements PaymentProvider
{
    public function initiate(float $amount, string $phone, string $reference, string $description): array
    {
        // Un vrai agrégateur déclencherait ici le push USSD vers le téléphone.
        return [
            'operator_reference' => 'OP-'.Str::upper(Str::random(10)),
            'status' => 'pending',
        ];
    }

    public function operatorFor(string $phone): string
    {
        // Préfixes indicatifs Cameroun : MTN (650-654, 67, 680-684), Orange (655-659, 69, 685-689).
        $digits = preg_replace('/\D/', '', $phone);
        $local = Str::substr($digits, -9); // 6XXXXXXXX
        $p3 = Str::substr($local, 0, 3);

        if (in_array($p3, ['655', '656', '657', '658', '659'], true) || Str::startsWith($local, '69')) {
            return 'orange';
        }

        return 'mtn';
    }

    public function status(string $operatorReference): string
    {
        // En sandbox, la confirmation se fait via le callback de démo.
        return 'pending';
    }
}
