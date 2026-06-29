<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmed extends Notification
{
    use Queueable;

    private array $labels = [
        'subscription' => 'Votre abonnement est activé',
        'boost' => 'Votre offre est boostée',
        'commission' => 'Paiement de commission reçu',
    ];

    public function __construct(public Transaction $transaction) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $amount = number_format($this->transaction->amount, 0, ',', ' ');

        return [
            'type' => 'payment.confirmed',
            'title' => 'Paiement confirmé',
            'message' => ($this->labels[$this->transaction->type] ?? 'Paiement confirmé')." — {$amount} FCFA.",
            'icon' => 'wallet',
            'reference' => $this->transaction->reference,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
