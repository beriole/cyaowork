<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageReceived extends Notification
{
    use Queueable;

    public function __construct(public Message $message, public string $senderName) {}

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'message.new',
            'title' => "Message de {$this->senderName}",
            'message' => Str::limit($this->message->body, 80),
            'icon' => 'message-circle',
            'conversation_id' => $this->message->conversation_id,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
