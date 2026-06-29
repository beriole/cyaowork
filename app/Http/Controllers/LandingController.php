<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\WorkerProfile;

class LandingController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('workerProfiles')->get();

        $profiles = WorkerProfile::with(['user', 'category'])
            ->orderByDesc('rating_avg')
            ->take(6)
            ->get();

        return view('landing', compact('categories', 'profiles'));
    }
}
