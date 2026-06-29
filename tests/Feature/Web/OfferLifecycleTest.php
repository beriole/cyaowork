<?php

namespace Tests\Feature\Web;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferLifecycleTest extends TestCase
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

    public function test_archiver_puis_republier_une_offre(): void
    {
        $offer = JobOffer::where('employer_id', $this->employer()->id)->where('status', 'published')->first();

        $this->actingAs($this->employer())
            ->patch(route('employer.offer.archive', $offer))
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('archived', $offer->fresh()->status);

        $this->actingAs($this->employer())->patch(route('employer.offer.archive', $offer));
        $this->assertSame('published', $offer->fresh()->status);
    }

    public function test_supprimer_une_offre_et_ses_candidatures(): void
    {
        $offer = JobOffer::where('employer_id', $this->employer()->id)->whereHas('applications')->first();
        $appIds = $offer->applications()->pluck('id');

        $this->actingAs($this->employer())
            ->delete(route('employer.offer.destroy', $offer))
            ->assertRedirect(route('employer.dashboard'))->assertSessionHas('status');

        $this->assertDatabaseMissing('job_offers', ['id' => $offer->id]);
        foreach ($appIds as $id) {
            $this->assertDatabaseMissing('applications', ['id' => $id]);
        }
    }

    public function test_un_employeur_ne_peut_pas_supprimer_l_offre_d_autrui(): void
    {
        $offer = JobOffer::where('employer_id', $this->employer()->id)->first();
        $autre = User::factory()->create(['role' => 'employer']);
        $autre->assignRole('employer');

        $this->actingAs($autre)->delete(route('employer.offer.destroy', $offer))->assertForbidden();
        $this->assertDatabaseHas('job_offers', ['id' => $offer->id]);
    }
}
