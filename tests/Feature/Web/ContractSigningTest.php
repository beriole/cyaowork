<?php

namespace Tests\Feature\Web;

use App\Models\Application;
use App\Models\Contract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContractSigningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function contract(): Contract
    {
        $application = Application::with('jobOffer')->first();

        return $application->contract()->create([
            'terms' => Contract::defaultTerms($application->jobOffer, $application->worker, $application->jobOffer->employer),
        ]);
    }

    public function test_les_deux_parties_voient_le_contrat(): void
    {
        $contract = $this->contract();
        $employer = $contract->application->jobOffer->employer;
        $worker = $contract->application->worker;

        $this->actingAs($employer)->get(route('contracts.show', $contract))->assertOk()->assertSee($contract->reference());
        $this->actingAs($worker)->get(route('contracts.show', $contract))->assertOk();
    }

    public function test_un_tiers_ne_peut_pas_voir_le_contrat(): void
    {
        $contract = $this->contract();
        $tiers = User::factory()->create(['role' => 'worker']);
        $tiers->assignRole('worker');

        $this->actingAs($tiers)->get(route('contracts.show', $contract))->assertForbidden();
    }

    public function test_signature_des_deux_parties_finalise_le_contrat(): void
    {
        Notification::fake();
        $contract = $this->contract();
        $employer = $contract->application->jobOffer->employer;
        $worker = $contract->application->worker;

        $this->actingAs($employer)->post(route('contracts.sign', $contract))->assertRedirect();
        $this->assertNotNull($contract->fresh()->employer_signed_at);
        $this->assertFalse($contract->fresh()->isFullySigned());

        $this->actingAs($worker)->post(route('contracts.sign', $contract))->assertRedirect()->assertSessionHas('status');

        $contract->refresh();
        $this->assertTrue($contract->isFullySigned());
        $this->assertSame('accepted', $contract->application->fresh()->status);
    }
}
