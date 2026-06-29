<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Contract;
use App\Notifications\ContractFullySigned;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    /** POST /applications/{application}/contract — l'employeur génère le contrat. */
    public function store(Request $request, Application $application)
    {
        $application->load('jobOffer');
        abort_unless($application->jobOffer->employer_id === $request->user()->id, 403, 'Réservé à l\'employeur de l\'offre.');

        $contract = $application->contract()->firstOrCreate([], [
            'terms' => Contract::defaultTerms($application->jobOffer, $application->worker, $application->jobOffer->employer),
        ]);

        return response()->json([
            'id' => $contract->id,
            'reference' => $contract->reference(),
            'fully_signed' => $contract->isFullySigned(),
            'pdf_url' => url("/api/v1/contracts/{$contract->id}/pdf"),
        ], $contract->wasRecentlyCreated ? 201 : 200);
    }

    /** POST /contracts/{contract}/sign — acceptation électronique (horodatée). */
    public function sign(Request $request, Contract $contract)
    {
        $contract->load('application.jobOffer');
        $user = $request->user();
        $isEmployer = $contract->application->jobOffer->employer_id === $user->id;
        $isWorker = $contract->application->worker_id === $user->id;
        abort_unless($isEmployer || $isWorker, 403);

        $field = $isEmployer ? 'employer_signed_at' : 'worker_signed_at';
        if (! $contract->$field) {
            $contract->update([$field => now()]);
        }

        // Une fois signé par les deux : la candidature passe à "accepted" + notifications.
        if ($contract->fresh()->isFullySigned()) {
            $contract->application->update(['status' => 'accepted']);
            $employer = $contract->application->jobOffer->employer;
            $worker = $contract->application->worker;
            $employer->notify(new ContractFullySigned($contract));
            $worker->notify(new ContractFullySigned($contract));
        }

        return response()->json([
            'reference' => $contract->reference(),
            'signed_as' => $isEmployer ? 'employer' : 'worker',
            'fully_signed' => $contract->fresh()->isFullySigned(),
        ]);
    }

    /** GET /contracts/{contract}/pdf — génère et télécharge le PDF. */
    public function pdf(Request $request, Contract $contract)
    {
        $contract->load('application.jobOffer');
        $user = $request->user();
        abort_unless(
            $contract->application->jobOffer->employer_id === $user->id || $contract->application->worker_id === $user->id,
            403
        );

        $pdf = Pdf::loadView('pdf.contract', $contract->viewData())->setPaper('a4');

        // Archive une copie côté serveur (traçabilité).
        $path = "contracts/{$contract->reference()}.pdf";
        Storage::disk('local')->put($path, $pdf->output());
        if ($contract->pdf_path !== $path) {
            $contract->update(['pdf_path' => $path]);
        }

        return $pdf->download("contrat-{$contract->reference()}.pdf");
    }
}
