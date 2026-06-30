<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class JobOffer extends Model
{
    use Searchable;

    protected $guarded = [];

    /** Champs indexés pour la recherche (Scout). */
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'city' => $this->city,
        ];
    }

    /** Seules les offres publiées sont cherchables. */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'published';
    }

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'salary_amount' => 'float',
        'is_boosted' => 'boolean',
    ];

    public function employer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
