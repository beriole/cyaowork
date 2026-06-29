<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Models\Application;
use App\Notifications\ApplicationStatusUpdated;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /** GET /applications — pour un worker: ses candidatures ; pour un employeur: celles reçues. */
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isEmployer()) {
            $apps = Application::whereHas('jobOffer', fn ($q) => $q->where('employer_id', $user->id))
                ->with(['worker', 'jobOffer'])->latest()->paginate(20);
        } else {
            $apps = $user->applications()->with('jobOffer.category')->latest()->paginate(20);
        }

        return ApplicationResource::collection($apps);
    }

    /** PATCH /applications/{application} — l'employeur change le statut. */
    public function updateStatus(Request $request, Application $application)
    {
        abort_unless($application->jobOffer->employer_id === $request->user()->id, 403);

        $data = $request->validate(['status' => ['required', 'in:seen,interview,accepted,rejected']]);
        $application->update($data);

        $application->load('jobOffer');
        $application->worker->notify(new ApplicationStatusUpdated($application));

        return new ApplicationResource($application->load(['worker', 'jobOffer']));
    }
}
