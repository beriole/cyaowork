<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'role' => $this->role,
            'is_verified' => (bool) $this->is_verified,
            'avatar_url' => $this->avatar ? "https://images.unsplash.com/photo-{$this->avatar}?w=160&h=160&fit=crop&q=78" : null,
        ];
    }
}
