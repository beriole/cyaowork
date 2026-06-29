<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Document;
use App\Models\JobOffer;
use App\Notifications\NewApplicationReceived;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerController extends Controller
{
    /** Postuler à une offre en 1 clic. */
    public function apply(JobOffer $offer): RedirectResponse
    {
        $application = Application::firstOrCreate(
            ['job_offer_id' => $offer->id, 'worker_id' => Auth::id()],
            ['status' => 'sent'],
        );

        if ($application->wasRecentlyCreated) {
            $offer->employer->notify(new NewApplicationReceived($application->load(['worker', 'jobOffer'])));

            return back()->with('status', "Candidature envoyée pour « {$offer->title} ».");
        }

        return back()->with('status', 'Vous avez déjà postulé à cette offre.');
    }

    /** Mettre à jour la photo de profil. */
    public function uploadPhoto(Request $request): RedirectResponse
    {
        $request->validate(['photo' => ['required', 'image', 'max:4096']]);
        $profile = $request->user()->workerProfile()->firstOrCreate([]);
        $profile->addMediaFromRequest('photo')->toMediaCollection('avatar');

        return back()->with('status', 'Photo de profil mise à jour.');
    }

    /** Déposer une pièce justificative (CNI…). */
    public function uploadDocument(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:cni,passeport,cv,diplome'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:8192'],
        ]);

        $document = Document::create([
            'user_id' => $request->user()->id, 'type' => $data['type'], 'file_path' => '', 'status' => 'pending',
        ]);
        $media = $document->addMediaFromRequest('file')->toMediaCollection('file');
        $document->update(['file_path' => $media->getPath()]);

        if (in_array($data['type'], ['cni', 'passeport'], true)) {
            $request->user()->workerProfile()->firstOrCreate([])->update(['verification_status' => 'pending']);
        }

        return back()->with('status', 'Document envoyé. Vérification sous 24-48h.');
    }

    public function dashboard()
    {
        $worker = Auth::user()->load(['workerProfile.category', 'workerProfile.skills']);

        $profile = $worker->workerProfile;

        // Complétion de profil (champs renseignés)
        $checks = [$profile->photo, $profile->bio, $profile->headline, $profile->category_id, $profile->city, $profile->skills->isNotEmpty()];
        $completion = (int) round(collect($checks)->filter()->count() / count($checks) * 100);

        // Offres recommandées (scoring simple : catégorie + ville)
        $offers = JobOffer::published()->with('category')->latest()->take(3)->get()
            ->map(function ($o) use ($profile) {
                $score = 72;
                if ($o->category_id === $profile->category_id) $score += 20;
                if ($o->city === $profile->city) $score += 6;
                $o->match = min(99, $score + ($o->id % 3));
                return $o;
            })
            ->sortByDesc('match')->values();

        $applications = $worker->applications()->with(['jobOffer', 'contract'])->latest()->get();

        // Contrats où la signature du travailleur est encore attendue.
        $contractsToSign = $applications->map->contract->filter(fn ($c) => $c && ! $c->worker_signed_at)->values();

        $conversations = Conversation::where('worker_id', $worker->id)
            ->with(['employer', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')->take(4)->get();

        $stats = [
            ['i' => 'send',          'label' => 'Candidatures',  'val' => (string) $applications->count(),                                  'sub' => $applications->where('status', 'interview')->count().' en entretien', 'g' => 'from-grape to-violet-600'],
            ['i' => 'star',          'label' => 'Note moyenne',  'val' => number_format($profile->rating_avg, 1, ',', ' '),                  'sub' => $profile->reviews_count.' avis',                                        'g' => 'from-amber-400 to-orange-600'],
            ['i' => 'check-circle',  'label' => 'Missions',      'val' => (string) $applications->where('status', 'accepted')->count(),      'sub' => 'acceptées',                                                            'g' => 'from-accent to-teal'],
            ['i' => 'briefcase',     'label' => 'Offres dispo.', 'val' => (string) JobOffer::published()->count(),                          'sub' => 'près de vous',                                                         'g' => 'from-sky-400 to-blue-600'],
        ];

        return view('worker.dashboard', compact('worker', 'profile', 'completion', 'offers', 'applications', 'conversations', 'stats', 'contractsToSign'));
    }
}
