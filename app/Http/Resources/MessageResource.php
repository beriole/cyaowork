<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id' => $this->sender_id,
            'body' => $this->body,
            'read_at' => $this->read_at,
            'is_mine' => $request->user() && $request->user()->id === $this->sender_id,
            'created_at' => $this->created_at,
        ];
    }
}
