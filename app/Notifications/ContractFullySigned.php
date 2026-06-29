<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ContractFullySigned extends Notification
{
    use Queueable;

    public function __construct(public Contract $contract) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'contract.signed',
            'title' => 'Contrat signé',
            'message' => "Le contrat {$this->contract->reference()} est signé par les deux parties.",
            'icon' => 'file-check',
            'contract_id' => $this->contract->id,
            'reference' => $this->contract->reference(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
