<?php

namespace Tests\Feature\Web;

use App\Models\Application;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerContractsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_le_travailleur_voit_ses_contrats(): void
    {
        $application = Application::with('jobOffer')->first();
        $contract = $application->contract()->create([
            'terms' => Contract::defaultTerms($application->jobOffer, $application->worker, $application->jobOffer->employer),
        ]);

        $this->actingAs($application->worker)
            ->get(route('worker.contracts'))
            ->assertOk()
            ->assertSee('Mes contrats')
            ->assertSee($application->jobOffer->title)
            ->assertSee($contract->reference());
    }

    public function test_un_travailleur_sans_contrat_voit_l_etat_vide(): void
    {
        $worker = User::where('email', 'worker3@cyaowork.cm')->first();

        $this->actingAs($worker)
            ->get(route('worker.contracts'))
            ->assertOk()
            ->assertSee('Aucun contrat');
    }

    public function test_la_page_contrats_exige_le_role_worker(): void
    {
        $employer = User::where('email', 'employeur@cyaowork.cm')->first();

        $this->actingAs($employer)->get(route('worker.contracts'))->assertForbidden();
    }
}
