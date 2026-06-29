<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Review;
use App\Models\WorkerProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkerProfileController extends Controller
{
    /** Profil public d'un travailleur (consultable par tous). */
    public function show(WorkerProfile $worker): View
    {
        $worker->load(['user', 'category', 'skills']);

        $reviews = Review::where('reviewee_id', $worker->user_id)
            ->with('reviewer:id,name')->latest()->take(10)->get();

        return view('workers.show', compact('worker', 'reviews'));
    }

    /** Démarre (ou rouvre) une conversation avec ce travailleur — employeurs uniquement. */
    public function contact(WorkerProfile $worker): RedirectResponse
    {
        $me = Auth::user();
        abort_unless($me && $me->isEmployer(), 403, 'Réservé aux employeurs.');

        $conversation = Conversation::firstOrCreate(
            ['employer_id' => $me->id, 'worker_id' => $worker->user_id],
            ['last_message_at' => now()],
        );

        return redirect()->route('messaging.index', ['c' => $conversation->id]);
    }
}
