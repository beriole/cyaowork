<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'icon' => $this->icon,
            'gradient' => $this->gradient,
            'workers_count' => $this->when(isset($this->worker_profiles_count), $this->worker_profiles_count),
        ];
    }
}
