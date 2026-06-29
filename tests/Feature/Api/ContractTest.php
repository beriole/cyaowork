<?php

namespace Tests\Feature\Api;

use App\Models\Application;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('local'); // archive PDF côté serveur
    }

    public function test_cycle_complet_du_contrat(): void
    {
        $application = Application::with('jobOffer', 'worker')->first();
        $employer = $application->jobOffer->employer;
        $worker = $application->worker;

        // 1) L'employeur génère le contrat.
        Sanctum::actingAs($employer);
        $contractId = $this->postJson("/api/v1/applications/{$application->id}/contract")
            ->assertCreated()->json('id');

        // 2) Les deux parties signent.
        $this->postJson("/api/v1/contracts/{$contractId}/sign")
            ->assertOk()->assertJsonPath('fully_signed', false);

        Sanctum::actingAs($worker);
        $this->postJson("/api/v1/contracts/{$contractId}/sign")
            ->assertOk()->assertJsonPath('fully_signed', true);

        // 3) La candidature est passée à "accepted".
        $this->assertSame('accepted', $application->fresh()->status);

        // 4) Le PDF se télécharge.
        Sanctum::actingAs($employer);
        $this->get("/api/v1/contracts/{$contractId}/pdf")
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_un_tiers_ne_peut_pas_generer_le_contrat(): void
    {
        $application = Application::first();
        $autre = User::factory()->create(['role' => 'employer']);
        $autre->assignRole('employer');

        Sanctum::actingAs($autre);
        $this->postJson("/api/v1/applications/{$application->id}/contract")->assertForbidden();
    }
}
