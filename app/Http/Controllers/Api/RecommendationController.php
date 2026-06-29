<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobOfferResource;
use App\Http\Resources\WorkerProfileResource;
use App\Models\JobOffer;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    /**
     * GET /recommendations — moteur de scoring (V1, pondération de règles métier).
     * Worker → offres recommandées ; Employer → travailleurs recommandés.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isWorker()) {
            $profile = $user->workerProfile;

            $offers = JobOffer::published()->with(['category', 'employer'])->latest()->take(20)->get()
                ->map(function ($o) use ($profile) {
                    $score = 60;
                    if ($profile && $o->category_id === $profile->category_id) $score += 25;
                    if ($profile && $o->city === $profile->city) $score += 10;
                    if ($o->is_boosted) $score += 5;
                    $o->match_score = min(99, $score);

                    return $o;
                })
                ->sortByDesc('match_score')->take(10)->values();

            return JobOfferResource::collection($offers)
                ->additional(['meta' => ['type' => 'offers']]);
        }

        // Employeur : travailleurs vérifiés les mieux notés
        $workers = WorkerProfile::with(['user', 'category', 'skills'])
            ->where('verification_status', 'verified')
            ->orderByDesc('rating_avg')->take(10)->get();

        return WorkerProfileResource::collection($workers)
            ->additional(['meta' => ['type' => 'workers']]);
    }
}
