<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $guarded = [];

    public function workerProfiles(): BelongsToMany
    {
        return $this->belongsToMany(WorkerProfile::class, 'profile_skill');
    }
}
