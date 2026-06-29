<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\JobOfferResource;
use App\Models\Application;
use App\Models\JobOffer;
use App\Notifications\NewApplicationReceived;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    /** GET /offers — filtres: city, category, salary_min, salary_max, q, distance (lat,lng,radius km) */
    public function index(Request $request)
    {
        $q = JobOffer::published()->with(['category', 'employer'])->withCount('applications');

        if ($city = $request->string('city')->trim()->value()) {
            $q->where('city', 'like', "%{$city}%");
        }
        if ($request->filled('category')) {
            $q->where('category_id', $request->integer('category'));
        }
        if ($request->filled('salary_min')) {
            $q->where('salary_amount', '>=', (float) $request->input('salary_min'));
        }
        if ($request->filled('salary_max')) {
            $q->where('salary_amount', '<=', (float) $request->input('salary_max'));
        }
        if ($term = $request->string('q')->trim()->value()) {
            $q->where(fn ($s) => $s->where('title', 'like', "%{$term}%")->orWhere('description', 'like', "%{$term}%"));
        }

        // Géo-recherche par rayon (Haversine) si lat/lng/radius fournis
        if ($request->filled(['lat', 'lng'])) {
            $lat = (float) $request->input('lat');
            $lng = (float) $request->input('lng');
            $radius = (float) $request->input('radius', 10);
            $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";
            $q->whereNotNull('latitude')->selectRaw("*, {$haversine} AS distance_km")
                ->having('distance_km', '<=', $radius)->orderBy('distance_km');
        } else {
            $q->latest();
        }

        return JobOfferResource::collection($q->paginate(15));
    }

    public function show(JobOffer $offer)
    {
        $offer->increment('views');

        return new JobOfferResource($offer->load(['category', 'employer'])->loadCount('applications'));
    }

    /** POST /offers — employeur uniquement */
    public function store(Request $request)
    {
        abort_unless($request->user()->isEmployer(), 403, 'Réservé aux employeurs.');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'salary_amount' => ['nullable', 'numeric', 'min:0'],
            'salary_period' => ['nullable', 'in:hour,day,month'],
            'schedule' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'contract_type' => ['nullable', 'in:ponctuel,journalier,permanent'],
            'status' => ['nullable', 'in:draft,published'],
        ]);

        $offer = JobOffer::create([...$data, 'employer_id' => $request->user()->id, 'status' => $data['status'] ?? 'published']);

        return new JobOfferResource($offer->load(['category', 'employer']));
    }

    /** POST /offers/{offer}/apply — travailleur uniquement */
    public function apply(Request $request, JobOffer $offer)
    {
        abort_unless($request->user()->isWorker(), 403, 'Réservé aux travailleurs.');

        $data = $request->validate(['message' => ['nullable', 'string', 'max:1000']]);

        $application = Application::firstOrCreate(
            ['job_offer_id' => $offer->id, 'worker_id' => $request->user()->id],
            ['status' => 'sent', 'message' => $data['message'] ?? null],
        );

        if ($application->wasRecentlyCreated) {
            $application->load(['worker', 'jobOffer']);
            $offer->employer->notify(new NewApplicationReceived($application));
        }

        return (new ApplicationResource($application->load('jobOffer')))
            ->response()->setStatusCode($application->wasRecentlyCreated ? 201 : 200);
    }
}
