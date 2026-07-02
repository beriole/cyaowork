<?php

namespace Tests\Feature;

use App\Models\JobOffer;
use App\Models\OfferView;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferViewsChartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_consulter_une_offre_enregistre_une_vue(): void
    {
        $offer = JobOffer::published()->first();
        $before = OfferView::where('job_offer_id', $offer->id)->count();

        $this->get(route('offers.show', $offer))->assertOk();

        $this->assertSame($before + 1, OfferView::where('job_offer_id', $offer->id)->count());
    }

    public function test_le_dashboard_employeur_expose_un_graphe_de_vues_reel(): void
    {
        $employer = User::where('email', 'employeur@cyaowork.cm')->first();

        $response = $this->actingAs($employer)->get(route('employer.dashboard'))->assertOk();

        // Le graphe est calculé (7 points) et provient des vraies vues seedées.
        $chart = $response->viewData('chart');
        $this->assertCount(7, $chart);
        $this->assertGreaterThan(0, array_sum($chart));
    }
}
