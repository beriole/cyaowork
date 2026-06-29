<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Notifications\ContractFullySigned;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContractController extends Controller
{
    private function participantRole(Contract $contract): string
    {
        $contract->loadMissing('application.jobOffer');
        $id = Auth::id();

        return match (true) {
            $contract->application->jobOffer->employer_id === $id => 'employer',
            $contract->application->worker_id === $id => 'worker',
            default => abort(403),
        };
    }

    /** Affiche le contrat (détails, signatures, téléchargement). */
    public function show(Contract $contract): View
    {
        $role = $this->participantRole($contract);
        $contract->load('application.jobOffer.category', 'application.worker', 'application.jobOffer.employer');

        return view('contracts.show', compact('contract', 'role'));
    }

    /** Signe électroniquement le contrat pour la partie connectée (horodaté). */
    public function sign(Contract $contract): RedirectResponse
    {
        $role = $this->participantRole($contract);
        $field = $role === 'employer' ? 'employer_signed_at' : 'worker_signed_at';

        if (! $contract->$field) {
            $contract->update([$field => now()]);
        }

        if ($contract->fresh()->isFullySigned()) {
            $contract->application->update(['status' => 'accepted']);
            $contract->load('application.jobOffer.employer', 'application.worker');
            $contract->application->jobOffer->employer->notify(new ContractFullySigned($contract));
            $contract->application->worker->notify(new ContractFullySigned($contract));

            return back()->with('status', 'Contrat signé par les deux parties. Mission confirmée !');
        }

        return back()->with('status', 'Votre signature a été enregistrée. En attente de l\'autre partie.');
    }
}
