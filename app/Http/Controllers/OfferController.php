<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\JobOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OfferController extends Controller
{
    /** Liste publique des offres publiées (filtres : recherche, ville, catégorie). */
    public function index(Request $request): View
    {
        $query = JobOffer::published()->with(['category', 'employer'])->withCount('applications');

        if ($q = $request->string('q')->trim()->value()) {
            $query->whereIn('id', JobOffer::search($q)->keys());
        }
        if ($city = $request->string('city')->trim()->value()) {
            $query->where('city', 'like', "%{$city}%");
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->integer('category'));
        }

        $offers = $query->latest()->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        $appliedIds = Auth::user()?->isWorker()
            ? Auth::user()->applications()->pluck('job_offer_id')->all()
            : [];

        return view('offers.index', compact('offers', 'categories', 'appliedIds'));
    }

    /** Détail public d'une offre. */
    public function show(JobOffer $offer): View
    {
        abort_unless($offer->status === 'published', 404);
        $offer->increment('views');
        \App\Models\OfferView::record($offer->id);
        $offer->load(['category', 'employer'])->loadCount('applications');

        $hasApplied = Auth::user()?->isWorker()
            && $offer->applications()->where('worker_id', Auth::id())->exists();

        return view('offers.show', compact('offer', 'hasApplied'));
    }
}
