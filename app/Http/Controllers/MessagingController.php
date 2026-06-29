<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessagingController extends Controller
{
    /** POST /messagerie/{conversation}/messages — persiste + diffuse en temps réel. */
    public function store(Request $request, Conversation $conversation)
    {
        $me = Auth::user();
        abort_unless($conversation->employer_id === $me->id || $conversation->worker_id === $me->id, 403);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->messages()->create(['sender_id' => $me->id, 'body' => $data['body']]);
        $conversation->update(['last_message_at' => now()]);

        broadcast(new MessageSent($message))->toOthers();

        $recipient = $conversation->employer_id === $me->id ? $conversation->worker : $conversation->employer;
        $recipient?->notify(new \App\Notifications\NewMessageReceived($message, $me->name));

        return response()->json([
            'id' => $message->id,
            'body' => $message->body,
            'created_at' => $message->created_at->format('H:i'),
        ], 201);
    }

    public function index(Request $request)
    {
        $me = Auth::user();

        // L'utilisateur participe aux conversations selon son rôle.
        $column = $me->isEmployer() ? 'employer_id' : 'worker_id';

        $conversations = Conversation::where($column, $me->id)
            ->with(['employer', 'worker', 'jobOffer', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')->get();

        $activeId = $request->integer('c') ?: $conversations->first()?->id;

        $active = $activeId
            ? Conversation::where($column, $me->id)
                ->with(['employer', 'worker', 'jobOffer', 'messages' => fn ($q) => $q->oldest()])->find($activeId)
            : null;

        return view('messaging.index', compact('me', 'conversations', 'active'));
    }
}
