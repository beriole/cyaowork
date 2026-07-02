<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfferView extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['viewed_at' => 'datetime'];

    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    /** Enregistre une vue horodatée pour une offre. */
    public static function record(int $offerId): void
    {
        static::create(['job_offer_id' => $offerId, 'viewed_at' => now()]);
    }
}
