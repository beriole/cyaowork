<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OfferApplicationTest extends TestCase
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
        return User::where('email', 'worker1@cyaowork.cm')->first();
    }

    public function test_un_employeur_peut_publier_une_offre(): void
    {
        Sanctum::actingAs($this->employer());

        $this->postJson('/api/v1/offers', [
            'title' => 'Repassage hebdomadaire',
            'salary_amount' => 2000,
            'salary_period' => 'day',
            'city' => 'Douala',
        ])->assertCreated()->assertJsonPath('data.title', 'Repassage hebdomadaire');
    }

    public function test_un_travailleur_ne_peut_pas_publier_une_offre(): void
    {
        Sanctum::actingAs($this->worker());

        $this->postJson('/api/v1/offers', ['title' => 'X'])->assertForbidden();
    }

    public function test_postuler_cree_une_candidature_et_notifie_l_employeur(): void
    {
        $employer = $this->employer();

        // L'employeur publie une offre fraîche.
        Sanctum::actingAs($employer);
        $offerId = $this->postJson('/api/v1/offers', [
            'title' => 'Nettoyage bureau', 'salary_amount' => 3000, 'salary_period' => 'day', 'city' => 'Douala',
        ])->json('data.id');

        // Le travailleur postule.
        Sanctum::actingAs($this->worker());
        $this->postJson("/api/v1/offers/{$offerId}/apply", ['message' => 'Disponible'])
            ->assertCreated()->assertJsonPath('data.status', 'sent');

        // L'employeur a reçu une notification en base.
        $this->assertSame(1, $employer->fresh()->unreadNotifications()->count());
        $this->assertSame('application.received', $employer->unreadNotifications()->first()->data['type']);
    }

    public function test_postuler_deux_fois_ne_duplique_pas(): void
    {
        $employer = $this->employer();
        Sanctum::actingAs($employer);
        $offerId = $this->postJson('/api/v1/offers', [
            'title' => 'Jardin', 'salary_amount' => 4000, 'salary_period' => 'day', 'city' => 'Douala',
        ])->json('data.id');

        Sanctum::actingAs($this->worker());
        $this->postJson("/api/v1/offers/{$offerId}/apply")->assertCreated();
        $this->postJson("/api/v1/offers/{$offerId}/apply")->assertOk(); // 200, pas 201
    }
}
