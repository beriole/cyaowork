<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->whenLoaded('user', fn () => $this->user->name),
            'headline' => $this->headline,
            'bio' => $this->bio,
            'photo_url' => $this->photoUrl(320),
            'experience_years' => $this->experience_years,
            'availability' => $this->availability,
            'expected_salary' => $this->expected_salary,
            'salary_period' => $this->salary_period,
            'city' => $this->city,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'verification_status' => $this->verification_status,
            'is_verified' => $this->verification_status === 'verified',
            'rating_avg' => (float) $this->rating_avg,
            'reviews_count' => $this->reviews_count,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'skills' => $this->whenLoaded('skills', fn () => $this->skills->pluck('name')),
        ];
    }
}
