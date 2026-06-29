<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ApplicationStatusUpdated extends Notification
{
    use Queueable;

    private array $labels = [
        'seen' => 'a été consultée',
        'interview' => 'passe en entretien',
        'accepted' => 'a été acceptée',
        'rejected' => 'n\'a pas été retenue',
    ];

    public function __construct(public Application $application) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        $label = $this->labels[$this->application->status] ?? 'a été mise à jour';

        return [
            'type' => 'application.status',
            'title' => 'Candidature mise à jour',
            'message' => "Votre candidature à « {$this->application->jobOffer->title} » {$label}.",
            'icon' => $this->application->status === 'accepted' ? 'check-circle' : 'bell',
            'status' => $this->application->status,
            'application_id' => $this->application->id,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
