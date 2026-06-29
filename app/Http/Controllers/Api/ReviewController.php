<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** POST /reviews — évaluation après mission (bidirectionnelle). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reviewee_id' => ['required', 'exists:users,id', 'different:'.$request->user()->id],
            'application_id' => ['nullable', 'exists:applications,id'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $review = Review::create([...$data, 'reviewer_id' => $request->user()->id]);

        // Recalcule la note moyenne du profil travailleur évalué.
        $reviewee = User::find($data['reviewee_id']);
        if ($reviewee?->isWorker() && $profile = $reviewee->workerProfile) {
            $stats = Review::where('reviewee_id', $reviewee->id)->selectRaw('AVG(rating) avg, COUNT(*) c')->first();
            $profile->update(['rating_avg' => round($stats->avg, 2), 'reviews_count' => $stats->c]);
        }

        return response()->json(['id' => $review->id, 'message' => 'Avis enregistré.'], 201);
    }
}
