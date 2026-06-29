<?php

namespace Tests\Feature\Web;

use App\Models\Conversation;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function aWorkerProfile(): WorkerProfile
    {
        return WorkerProfile::with('user')->first();
    }

    public function test_le_profil_public_est_visible_par_les_invites(): void
    {
        $profile = $this->aWorkerProfile();

        $this->get(route('workers.show', $profile))
            ->assertOk()
            ->assertSee($profile->user->name)
            ->assertSee('Se connecter pour contacter');
    }

    public function test_un_employeur_contacte_un_travailleur_et_ouvre_la_conversation(): void
    {
        $employer = User::where('email', 'employeur@cyaowork.cm')->first();
        $profile = $this->aWorkerProfile();

        $response = $this->actingAs($employer)->post(route('workers.contact', $profile));

        $conversation = Conversation::where('employer_id', $employer->id)
            ->where('worker_id', $profile->user_id)->first();

        $this->assertNotNull($conversation);
        $response->assertRedirect(route('messaging.index', ['c' => $conversation->id]));

        // Idempotent : un 2e contact réutilise la même conversation.
        $this->actingAs($employer)->post(route('workers.contact', $profile));
        $this->assertSame(1, Conversation::where('employer_id', $employer->id)->where('worker_id', $profile->user_id)->count());
    }

    public function test_un_travailleur_ne_peut_pas_utiliser_contacter(): void
    {
        $worker = User::where('email', 'worker1@cyaowork.cm')->first();
        $profile = $this->aWorkerProfile();

        $this->actingAs($worker)->post(route('workers.contact', $profile))->assertForbidden();
    }
}
