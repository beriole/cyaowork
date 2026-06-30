<?php

namespace Tests\Feature\Web;

use App\Models\JobOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicOffersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'database']);
        $this->seed();
    }

    public function test_la_liste_publique_affiche_les_offres_publiees(): void
    {
        $offer = JobOffer::published()->first();

        $this->get(route('offers.index'))
            ->assertOk()
            ->assertSee($offer->title);
    }

    public function test_la_liste_filtre_par_recherche_scout(): void
    {
        $offer = JobOffer::published()->first();
        $word = explode(' ', $offer->title)[0];

        $this->get(route('offers.index', ['q' => $word]))
            ->assertOk()
            ->assertSee($offer->title);
    }

    public function test_le_detail_d_une_offre_est_public_et_incremente_les_vues(): void
    {
        $offer = JobOffer::published()->first();
        $views = $offer->views;

        $this->get(route('offers.show', $offer))
            ->assertOk()
            ->assertSee($offer->title)
            ->assertSee('Se connecter pour postuler');

        $this->assertSame($views + 1, $offer->fresh()->views);
    }

    public function test_un_worker_voit_le_bouton_postuler(): void
    {
        $worker = User::where('email', 'worker3@cyaowork.cm')->first();
        $offer = JobOffer::published()
            ->whereDoesntHave('applications', fn ($q) => $q->where('worker_id', $worker->id))->first();

        $this->actingAs($worker)->get(route('offers.show', $offer))
            ->assertOk()->assertSee('Postuler en 1 clic');
    }

    public function test_une_offre_non_publiee_renvoie_404(): void
    {
        $draft = JobOffer::where('status', '!=', 'published')->first()
            ?? JobOffer::factory()->create(['status' => 'draft']);

        $this->get(route('offers.show', $draft))->assertNotFound();
    }
}
