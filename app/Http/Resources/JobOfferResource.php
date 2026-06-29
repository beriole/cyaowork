<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JobOfferResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'salary_amount' => $this->salary_amount,
            'salary_period' => $this->salary_period,
            'schedule' => $this->schedule,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'contract_type' => $this->contract_type,
            'status' => $this->status,
            'is_boosted' => (bool) $this->is_boosted,
            'views' => $this->views,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'employer' => $this->whenLoaded('employer', fn () => [
                'id' => $this->employer->id,
                'name' => $this->employer->name,
                'is_verified' => (bool) $this->employer->is_verified,
            ]),
            'applications_count' => $this->when(isset($this->applications_count), $this->applications_count),
            'match_score' => $this->when(isset($this->match_score), fn () => $this->match_score),
            'distance_km' => $this->when(isset($this->distance_km), fn () => round((float) $this->distance_km, 1)),
            'created_at' => $this->created_at,
        ];
    }
}
