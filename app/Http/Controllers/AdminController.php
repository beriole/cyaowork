<?php

namespace App\Http\Controllers;

use App\Models\JobOffer;
use App\Models\Review;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\RedirectResponse;

class AdminController extends Controller
{
    /** Valide l'identité d'un travailleur depuis la file de vérification. */
    public function approveProfile(WorkerProfile $profile): RedirectResponse
    {
        $profile->update(['verification_status' => 'verified']);
        $profile->user->update(['is_verified' => true]);

        return back()->with('status', "{$profile->user->name} est maintenant vérifié(e).");
    }

    /** Rejette la pièce d'identité d'un travailleur. */
    public function rejectProfile(WorkerProfile $profile): RedirectResponse
    {
        $profile->update(['verification_status' => 'rejected']);

        return back()->with('status', "La vérification de {$profile->user->name} a été rejetée.");
    }

    public function dashboard()
    {
        $pendingCount = WorkerProfile::where('verification_status', 'pending')->count();
        $revenue = (float) Transaction::where('status', 'success')->sum('amount');

        $kpis = [
            ['i' => 'users',      'label' => 'Utilisateurs',         'val' => number_format(User::count(), 0, ',', ' '),         'sub' => 'comptes', 'g' => 'from-sky-400 to-blue-600'],
            ['i' => 'user-check', 'label' => 'En attente de vérif.', 'val' => (string) $pendingCount,                            'sub' => 'à traiter', 'g' => 'from-amber-400 to-orange-600'],
            ['i' => 'briefcase',  'label' => 'Offres publiées',      'val' => (string) JobOffer::published()->count(),           'sub' => 'actives', 'g' => 'from-grape to-violet-600'],
            ['i' => 'wallet',     'label' => 'Revenus',              'val' => number_format($revenue, 0, ',', ' '), 'unit' => 'FCFA', 'sub' => 'encaissés', 'g' => 'from-accent to-teal'],
        ];

        $verifications = WorkerProfile::where('verification_status', 'pending')
            ->with(['user', 'category'])->latest()->get();

        $reports = Review::where('is_flagged', true)->with(['reviewer', 'reviewee'])->latest()->take(5)->get();

        $transactions = Transaction::with('user')->latest()->take(6)->get();

        return view('admin.dashboard', compact('kpis', 'verifications', 'reports', 'transactions', 'revenue'));
    }
}
