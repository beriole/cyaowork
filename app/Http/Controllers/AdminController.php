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

    /** Ignore un signalement (retire le drapeau, l'avis reste publié). */
    public function ignoreReport(Review $review): RedirectResponse
    {
        $review->update(['is_flagged' => false]);

        return back()->with('status', 'Signalement ignoré, l\'avis reste publié.');
    }

    /** Sanctionne : supprime l'avis signalé et recalcule la note du profil concerné. */
    public function sanctionReport(Review $review): RedirectResponse
    {
        $reviewee = $review->reviewee;
        $review->delete();

        if ($reviewee?->isWorker() && $profile = $reviewee->workerProfile) {
            $stats = Review::where('reviewee_id', $reviewee->id)->selectRaw('AVG(rating) avg, COUNT(*) c')->first();
            $profile->update(['rating_avg' => round($stats->avg ?? 0, 2), 'reviews_count' => (int) $stats->c]);
        }

        return back()->with('status', 'Avis supprimé et note recalculée.');
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

        [$chart, $chartMax, $revenueDelta] = $this->revenueChart();

        return view('admin.dashboard', compact('kpis', 'verifications', 'reports', 'transactions', 'revenue', 'chart', 'chartMax', 'revenueDelta'));
    }

    /** Revenus quotidiens (transactions réussies) sur 7 jours + variation. */
    private function revenueChart(): array
    {
        $byDay = Transaction::where('status', 'success')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->get(['created_at', 'amount'])
            ->groupBy(fn ($t) => $t->created_at->toDateString())
            ->map(fn ($g) => (float) $g->sum('amount'));

        $chart = collect(range(6, 0))
            ->map(fn ($d) => (float) $byDay->get(now()->subDays($d)->toDateString(), 0))
            ->all();

        $thisWeek = array_sum($chart);
        $prevWeek = (float) Transaction::where('status', 'success')
            ->whereBetween('created_at', [now()->subDays(13)->startOfDay(), now()->subDays(7)->endOfDay()])
            ->sum('amount');
        $delta = $prevWeek > 0 ? (int) round(($thisWeek - $prevWeek) / $prevWeek * 100) : ($thisWeek > 0 ? 100 : 0);

        return [$chart, max(max($chart), 1), $delta];
    }
}
