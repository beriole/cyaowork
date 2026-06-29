<?php

namespace App\Notifications;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewApplicationReceived extends Notification
{
    use Queueable;

    public function __construct(public Application $application) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'application.received',
            'title' => 'Nouvelle candidature',
            'message' => "{$this->application->worker->name} a postulé à « {$this->application->jobOffer->title} ».",
            'icon' => 'user-plus',
            'application_id' => $this->application->id,
            'offer_id' => $this->application->job_offer_id,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nouvelle candidature sur CyaoWork')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("{$this->application->worker->name} a postulé à votre offre « {$this->application->jobOffer->title} ».")
            ->action('Voir la candidature', url('/'))
            ->line('Connectez-vous pour répondre.');
    }
}
