<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'job_offer' => new JobOfferResource($this->whenLoaded('jobOffer')),
            'worker' => $this->whenLoaded('worker', fn () => new UserResource($this->worker)),
            'created_at' => $this->created_at,
        ];
    }
}
