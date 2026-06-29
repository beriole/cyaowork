<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Category;
use App\Models\Contract;
use App\Models\JobOffer;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Conversation;
use App\Notifications\ApplicationStatusUpdated;
use App\Services\Payment\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class EmployerController extends Controller
{
    /** Liste des candidats d'une offre. */
    public function candidates(JobOffer $offer): View
    {
        abort_unless($offer->employer_id === Auth::id(), 403);

        $offer->loadCount('applications')->load('category');
        $applications = $offer->applications()
            ->with(['worker.workerProfile.category', 'contract'])->latest()->get();

        return view('employer.candidates', compact('offer', 'applications'));
    }

    /** Formulaire de publication d'une offre. */
    public function createOffer(): View
    {
        $categories = Category::orderBy('name')->get();
        $offer = null;

        return view('employer.offer-create', compact('categories', 'offer'));
    }

    /** Formulaire d'édition d'une offre existante. */
    public function editOffer(JobOffer $offer): View
    {
        abort_unless($offer->employer_id === Auth::id(), 403);
        $categories = Category::orderBy('name')->get();

        return view('employer.offer-create', compact('categories', 'offer'));
    }

    /** Met à jour une offre existante. */
    public function updateOffer(Request $request, JobOffer $offer): RedirectResponse
    {
        abort_unless($offer->employer_id === Auth::id(), 403);

        $offer->update($this->validatedOffer($request));

        return redirect()->route('employer.dashboard')->with('status', "Offre « {$offer->title} » mise à jour.");
    }

    /** Enregistre une nouvelle offre (brouillon ou publiée). */
    public function storeOffer(Request $request): RedirectResponse
    {
        $offer = JobOffer::create([...$this->validatedOffer($request), 'employer_id' => Auth::id()]);

        $msg = $offer->status === 'published'
            ? "Offre « {$offer->title} » publiée."
            : "Offre « {$offer->title} » enregistrée en brouillon.";

        return redirect()->route('employer.dashboard')->with('status', $msg);
    }

    /** Archive une offre (ou la republie si déjà archivée). */
    public function archiveOffer(JobOffer $offer): RedirectResponse
    {
        abort_unless($offer->employer_id === Auth::id(), 403);

        $archiving = $offer->status !== 'archived';
        $offer->update(['status' => $archiving ? 'archived' : 'published']);

        return back()->with('status', $archiving
            ? "Offre « {$offer->title} » archivée."
            : "Offre « {$offer->title} » republiée.");
    }

    /** Supprime définitivement une offre et ses dépendances. */
    public function destroyOffer(JobOffer $offer): RedirectResponse
    {
        abort_unless($offer->employer_id === Auth::id(), 403);

        $title = $offer->title;
        DB::transaction(function () use ($offer) {
            $offer->load('applications.contract');
            $offer->applications->each(function ($application) {
                optional($application->contract)->delete();
                $application->delete();
            });
            Conversation::where('job_offer_id', $offer->id)->update(['job_offer_id' => null]);
            $offer->delete();
        });

        return redirect()->route('employer.dashboard')->with('status', "Offre « {$title} » supprimée.");
    }

    /** Règles de validation communes création/édition d'offre. */
    private function validatedOffer(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'salary_amount' => ['nullable', 'numeric', 'min:0'],
            'salary_period' => ['required', 'in:hour,day,month'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'contract_type' => ['required', 'in:ponctuel,journalier,permanent'],
            'status' => ['required', 'in:draft,published'],
        ]);
    }

    private function authorizeOwner(Application $application): void
    {
        abort_unless($application->jobOffer->employer_id === Auth::id(), 403);
    }

    /** Accepter ou refuser une candidature. */
    public function updateApplication(Application $application, string $decision): RedirectResponse
    {
        $application->load('jobOffer');
        $this->authorizeOwner($application);

        $status = $decision === 'accepter' ? 'accepted' : 'rejected';
        $application->update(['status' => $status]);
        $application->worker->notify(new ApplicationStatusUpdated($application));

        $label = $status === 'accepted' ? 'acceptée' : 'refusée';

        return back()->with('status', "Candidature de {$application->worker->name} {$label}.");
    }

    /** Générer le contrat de mission pour une candidature. */
    public function generateContract(Application $application): RedirectResponse
    {
        $application->load('jobOffer', 'worker');
        $this->authorizeOwner($application);

        $contract = $application->contract()->firstOrCreate([], [
            'terms' => Contract::defaultTerms($application->jobOffer, $application->worker, $application->jobOffer->employer),
        ]);

        return back()->with('status', "Contrat {$contract->reference()} généré. Téléchargeable en PDF.");
    }

    /** Booster une offre via Mobile Money. */
    public function boostOffer(JobOffer $offer, PaymentService $payments): RedirectResponse
    {
        abort_unless($offer->employer_id === Auth::id(), 403);

        $tx = $payments->initiate(Auth::user(), 'boost', 2500, Auth::user()->phone ?? '+237680000000', ['offer_id' => $offer->id]);

        if ($this->autoConfirm()) {
            $payments->confirm($tx, true);

            return back()->with('status', "Offre « {$offer->title} » boostée (réf. {$tx->reference}).");
        }

        return back()->with('status', "Validez le paiement du boost sur votre téléphone (réf. {$tx->reference}).");
    }

    /** Renouveler l'abonnement Pro (+30 jours) via Mobile Money. */
    public function renewSubscription(PaymentService $payments): RedirectResponse
    {
        $tx = $payments->initiate(Auth::user(), 'subscription', 15000, Auth::user()->phone ?? '+237680000000', ['plan' => 'pro']);

        if ($this->autoConfirm()) {
            $payments->confirm($tx, true);

            return back()->with('status', "Abonnement Pro renouvelé (+30 jours). Réf. {$tx->reference}.");
        }

        return back()->with('status', "Validez le paiement de l'abonnement sur votre téléphone (réf. {$tx->reference}).");
    }

    /** Le sandbox confirme immédiatement ; un agrégateur réel (Fapshi) confirme via webhook. */
    private function autoConfirm(): bool
    {
        return config('services.payment.driver', 'sandbox') === 'sandbox';
    }

    public function dashboard()
    {
        $employer = Auth::user();

        $offers = JobOffer::where('employer_id', $employer->id)
            ->with('category')->withCount('applications')->latest()->get();

        $applications = Application::whereHas('jobOffer', fn ($q) => $q->where('employer_id', $employer->id))
            ->with(['worker.workerProfile', 'jobOffer', 'contract'])->latest()->take(5)->get();

        $subscription = Subscription::where('employer_id', $employer->id)->latest()->first();

        $stats = [
            ['i' => 'briefcase', 'label' => 'Offres actives', 'val' => (string) $offers->where('status', 'published')->count(), 'sub' => $offers->where('is_boosted', true)->count().' boostées', 'g' => 'from-sky-400 to-blue-600'],
            ['i' => 'users',     'label' => 'Candidatures',   'val' => (string) $offers->sum('applications_count'),                  'sub' => 'au total',     'g' => 'from-grape to-violet-600'],
            ['i' => 'eye',       'label' => 'Vues des offres', 'val' => (string) $offers->sum('views'),                              'sub' => 'cumulées',     'g' => 'from-amber-400 to-orange-600'],
            ['i' => 'handshake', 'label' => 'Embauches',      'val' => (string) Application::whereHas('jobOffer', fn ($q) => $q->where('employer_id', $employer->id))->where('status', 'accepted')->count(), 'sub' => 'confirmées', 'g' => 'from-accent to-teal'],
        ];

        $transactions = Transaction::where('user_id', $employer->id)->latest()->take(4)->get();

        return view('employer.dashboard', compact('employer', 'offers', 'applications', 'subscription', 'stats', 'transactions'));
    }
}
