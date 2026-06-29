<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkerProfileResource;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /** GET /workers — recherche de travailleurs (filtres + géo) */
    public function workers(Request $request)
    {
        $q = WorkerProfile::with(['user', 'category', 'skills']);

        if ($term = $request->string('q')->trim()->value()) {
            $q->where(fn ($s) => $s->where('headline', 'like', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$term}%")));
        }
        if ($city = $request->string('city')->trim()->value()) {
            $q->where('city', 'like', "%{$city}%");
        }
        if ($request->filled('category')) {
            $q->where('category_id', $request->integer('category'));
        }
        if ($request->filled('salary_max')) {
            $q->where('expected_salary', '<=', (float) $request->input('salary_max'));
        }
        if ($request->filled('rating_min')) {
            $q->where('rating_avg', '>=', (float) $request->input('rating_min'));
        }
        if ($request->boolean('verified')) {
            $q->where('verification_status', 'verified');
        }

        if ($request->filled(['lat', 'lng'])) {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');
            $radius = (float) $request->input('radius', 10);
            $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";
            $q->whereNotNull('latitude')->selectRaw("*, {$haversine} AS distance_km")
                ->having('distance_km', '<=', $radius)->orderBy('distance_km');
        } else {
            $q->orderByDesc('rating_avg');
        }

        return WorkerProfileResource::collection($q->paginate(15));
    }
}
