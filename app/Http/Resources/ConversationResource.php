<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    public function toArray($request): array
    {
        $me = $request->user();
        $other = $me && $me->isEmployer() ? $this->worker : $this->employer;

        return [
            'id' => $this->id,
            'counterpart' => $other ? [
                'id' => $other->id,
                'name' => $other->name,
                'is_verified' => (bool) $other->is_verified,
                'avatar_url' => $other->avatar ? "https://images.unsplash.com/photo-{$other->avatar}?w=160&h=160&fit=crop&q=78" : null,
            ] : null,
            'job_offer' => new JobOfferResource($this->whenLoaded('jobOffer')),
            'last_message' => $this->whenLoaded('messages', fn () => optional($this->messages->first())->body),
            'last_message_at' => $this->last_message_at,
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
        ];
    }
}
