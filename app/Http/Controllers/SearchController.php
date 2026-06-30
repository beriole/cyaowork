<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\WorkerProfile;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('workerProfiles')->get();

        $query = WorkerProfile::with(['user', 'category', 'skills']);

        // Filtre texte : Scout (métier/bio/ville) + repli sur le nom de l'utilisateur.
        if ($q = $request->string('q')->trim()->value()) {
            $scoutIds = WorkerProfile::search($q)->keys();
            $query->where(function ($sub) use ($scoutIds, $q) {
                $sub->whereIn('id', $scoutIds)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%"));
            });
        }

        // Filtre ville
        if ($city = $request->string('city')->trim()->value()) {
            $query->where('city', 'like', "%{$city}%");
        }

        // Filtre catégories (multi)
        if ($cats = array_filter((array) $request->input('categories', []))) {
            $query->whereIn('category_id', $cats);
        }

        // Salaire max
        if ($request->filled('salary_max')) {
            $query->where('expected_salary', '<=', (float) $request->input('salary_max'));
        }

        // Note minimale
        if ($request->filled('rating_min')) {
            $query->where('rating_avg', '>=', (float) $request->input('rating_min'));
        }

        // Vérifiés uniquement
        if ($request->boolean('verified', true)) {
            $query->where('verification_status', 'verified');
        }

        $workers = $query->orderByDesc('rating_avg')->get();

        return view('employer.search', compact('categories', 'workers'))
            ->with('filters', $request->only(['q', 'city', 'categories', 'salary_max', 'rating_min', 'verified']));
    }
}
