<?php

namespace Tests\Feature\Web;

use App\Models\Category;
use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferManagementTest extends TestCase
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

    public function test_la_page_candidats_affiche_les_candidatures_de_l_offre(): void
    {
        $offer = JobOffer::where('employer_id', $this->employer()->id)
            ->whereHas('applications')->first();

        $this->actingAs($this->employer())
            ->get(route('employer.offer.candidates', $offer))
            ->assertOk()
            ->assertSee($offer->title);
    }

    public function test_un_employeur_ne_voit_pas_les_candidats_d_une_offre_d_autrui(): void
    {
        $offer = JobOffer::where('employer_id', $this->employer()->id)->first();
        $autre = User::factory()->create(['role' => 'employer']);
        $autre->assignRole('employer');

        $this->actingAs($autre)
            ->get(route('employer.offer.candidates', $offer))
            ->assertForbidden();
    }

    public function test_le_formulaire_de_creation_d_offre_se_charge(): void
    {
        $this->actingAs($this->employer())
            ->get(route('employer.offer.create'))
            ->assertOk()->assertSee('Publier une offre');
    }

    public function test_publier_une_offre_la_cree(): void
    {
        $cat = Category::first();

        $this->actingAs($this->employer())
            ->post(route('employer.offer.store'), [
                'title' => 'Femme de ménage 2j/sem',
                'category_id' => $cat->id,
                'salary_amount' => 3000,
                'salary_period' => 'day',
                'contract_type' => 'permanent',
                'city' => 'Douala',
                'status' => 'published',
            ])
            ->assertRedirect(route('employer.dashboard'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('job_offers', [
            'title' => 'Femme de ménage 2j/sem',
            'employer_id' => $this->employer()->id,
            'status' => 'published',
        ]);
    }

    public function test_creation_offre_valide_les_champs_requis(): void
    {
        $this->actingAs($this->employer())
            ->post(route('employer.offer.store'), ['title' => ''])
            ->assertSessionHasErrors(['title', 'salary_period', 'contract_type', 'status']);
    }
}
