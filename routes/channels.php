<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Canal privé d'une conversation : réservé à ses deux participants.
Broadcast::channel('conversation.{conversation}', function ($user, Conversation $conversation) {
    return $user->id === $conversation->employer_id || $user->id === $conversation->worker_id;
});
