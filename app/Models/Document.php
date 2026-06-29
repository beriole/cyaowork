<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Pièce justificative stockée sur un disque privé (non public). */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile()->useDisk('local');
    }

    public function fileUrl(): ?string
    {
        // Le fichier est privé : aperçu via une route admin protégée.
        return $this->getFirstMedia('file') ? route('documents.preview', $this) : null;
    }
}
