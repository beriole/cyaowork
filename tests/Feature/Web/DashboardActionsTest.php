<?php

namespace Tests\Feature\Web;

use App\Models\Application;
use App\Models\JobOffer;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DashboardActionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function employer(): User
    {
        return User::where('email', 'employeur@cyaowork.cm')->first();
    }

    private function worker(): User
    {
        return User::where('email', 'worker3@cyaowork.cm')->first(); // Flore (pending, peu de candidatures)
    }

    public function test_worker_postule_depuis_le_dashboard(): void
    {
        $offer = JobOffer::published()->whereDoesntHave('applications', fn ($q) => $q->where('worker_id', $this->worker()->id))->first();

        $this->actingAs($this->worker())
            ->post(route('worker.apply', $offer))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('applications', [
            'job_offer_id' => $offer->id, 'worker_id' => $this->worker()->id, 'status' => 'sent',
        ]);
        $this->assertSame(1, $offer->employer->fresh()->unreadNotifications()->count());
    }

    public function test_worker_depose_une_cni_depuis_le_dashboard(): void
    {
        Storage::fake('local');

        $this->actingAs($this->worker())
            ->post(route('worker.documents'), [
                'type' => 'cni',
                'file' => UploadedFile::fake()->image('cni.jpg'),
            ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('pending', $this->worker()->workerProfile->fresh()->verification_status);
    }

    public function test_employeur_accepte_une_candidature(): void
    {
        $application = Application::with('jobOffer')->first();

        $this->actingAs($application->jobOffer->employer)
            ->post(route('employer.application.decision', [$application, 'accepter']))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame('accepted', $application->fresh()->status);
        $this->assertSame(1, $application->worker->fresh()->unreadNotifications()->count());
    }

    public function test_employeur_genere_un_contrat(): void
    {
        $application = Application::first();

        $this->actingAs($application->jobOffer->employer)
            ->post(route('employer.contract', $application))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertDatabaseHas('contracts', ['application_id' => $application->id]);
    }

    public function test_employeur_booste_une_offre(): void
    {
        $offer = JobOffer::where('employer_id', $this->employer()->id)->where('is_boosted', false)->first();

        $this->actingAs($this->employer())
            ->post(route('employer.boost', $offer))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertTrue($offer->fresh()->is_boosted);
        $this->assertDatabaseHas('transactions', ['type' => 'boost', 'status' => 'success']);
    }

    public function test_employeur_renouvelle_l_abonnement(): void
    {
        $employer = $this->employer();
        $avant = Subscription::where('employer_id', $employer->id)->count();

        $this->actingAs($employer)
            ->post(route('employer.subscription.renew'))
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame($avant + 1, Subscription::where('employer_id', $employer->id)->count());
    }

    public function test_un_employeur_ne_peut_pas_agir_sur_la_candidature_d_un_autre(): void
    {
        $application = Application::first();
        $autre = User::factory()->create(['role' => 'employer']);
        $autre->assignRole('employer');

        $this->actingAs($autre)
            ->post(route('employer.application.decision', [$application, 'accepter']))
            ->assertForbidden();
    }
}
