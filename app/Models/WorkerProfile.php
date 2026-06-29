<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class WorkerProfile extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $guarded = [];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'rating_avg' => 'float',
        'expected_salary' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'profile_skill');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->fit(Fit::Crop, 320, 320)->nonQueued();
    }

    /**
     * URL de la photo : priorité à l'avatar uploadé (medialibrary),
     * sinon repli sur l'identifiant Unsplash de l'attribut `photo`.
     */
    public function photoUrl(int $size = 160): string
    {
        if ($this->relationLoaded('media') || $this->exists) {
            $url = $this->getFirstMediaUrl('avatar', 'thumb');
            if ($url) {
                return $url;
            }
        }

        if (! $this->photo) {
            return '';
        }

        return "https://images.unsplash.com/photo-{$this->photo}?w={$size}&h={$size}&fit=crop&q=78";
    }
}
