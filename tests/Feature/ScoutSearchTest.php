<?php

namespace Tests\Feature;

use App\Models\JobOffer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScoutSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['scout.driver' => 'database']);
        $this->seed();
    }

    public function test_la_recherche_scout_trouve_une_offre_par_mot_cle(): void
    {
        $offer = JobOffer::published()->first();
        $word = explode(' ', $offer->title)[0];

        $found = JobOffer::search($word)->keys();

        $this->assertTrue($found->contains($offer->id));
    }

    public function test_l_api_offers_utilise_la_recherche(): void
    {
        $offer = JobOffer::published()->create([
            'employer_id' => \App\Models\User::where('role', 'employer')->value('id'),
            'title' => 'Repasseuse expérimentée unique',
            'salary_amount' => 2000, 'salary_period' => 'day', 'contract_type' => 'permanent',
            'city' => 'Douala', 'status' => 'published',
        ]);

        $this->getJson('/api/v1/offers?q=Repasseuse')
            ->assertOk()
            ->assertJsonFragment(['id' => $offer->id]);
    }
}
