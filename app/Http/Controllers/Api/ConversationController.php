<?php

namespace App\Http\Controllers\Api;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user();
        $column = $me->isEmployer() ? 'employer_id' : 'worker_id';

        $conversations = Conversation::where($column, $me->id)
            ->with(['employer', 'worker', 'jobOffer', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')->get();

        return ConversationResource::collection($conversations);
    }

    public function show(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($request, $conversation);

        // Marquer comme lus les messages reçus
        $conversation->messages()->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')->update(['read_at' => now()]);

        return new ConversationResource(
            $conversation->load(['employer', 'worker', 'jobOffer', 'messages' => fn ($q) => $q->oldest()])
        );
    }

    public function storeMessage(Request $request, Conversation $conversation)
    {
        $this->authorizeParticipant($request, $conversation);

        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $message = $conversation->messages()->create([
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $conversation->update(['last_message_at' => now()]);

        broadcast(new MessageSent($message))->toOthers();

        $recipient = $conversation->employer_id === $request->user()->id ? $conversation->worker : $conversation->employer;
        $recipient?->notify(new \App\Notifications\NewMessageReceived($message, $request->user()->name));

        return (new MessageResource($message))->response()->setStatusCode(201);
    }

    private function authorizeParticipant(Request $request, Conversation $conversation): void
    {
        $id = $request->user()->id;
        abort_unless($conversation->employer_id === $id || $conversation->worker_id === $id, 403);
    }
}
