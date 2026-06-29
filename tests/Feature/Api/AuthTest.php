<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_un_travailleur_peut_s_inscrire_et_recoit_un_token(): void
    {
        $res = $this->postJson('/api/v1/register', [
            'name' => 'Nouveau Worker',
            'phone' => '+237690000999',
            'password' => 'secret123',
            'role' => 'worker',
        ]);

        $res->assertCreated()
            ->assertJsonStructure(['user' => ['id', 'name', 'role'], 'token'])
            ->assertJsonPath('user.role', 'worker');

        $user = User::where('phone', '+237690000999')->first();
        $this->assertNotNull($user->workerProfile, 'Un profil travailleur doit être créé.');
        $this->assertTrue($user->hasRole('worker'));
    }

    public function test_la_connexion_renvoie_un_token(): void
    {
        $this->postJson('/api/v1/login', [
            'login' => 'employeur@cyaowork.cm',
            'password' => 'password',
        ])->assertOk()->assertJsonStructure(['user', 'token']);
    }

    public function test_mauvais_identifiants_rejetes(): void
    {
        $this->postJson('/api/v1/login', [
            'login' => 'employeur@cyaowork.cm',
            'password' => 'mauvais',
        ])->assertStatus(422);
    }

    public function test_me_exige_une_authentification(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
    }
}
