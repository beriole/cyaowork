<?php

namespace Tests\Feature\Web;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployerProfileTest extends TestCase
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

    public function test_le_formulaire_profil_employeur_se_charge(): void
    {
        $this->actingAs($this->employer())
            ->get(route('employer.profile.edit'))
            ->assertOk()->assertSee('Mon profil');
    }

    public function test_l_employeur_met_a_jour_son_profil_entreprise(): void
    {
        $employer = $this->employer();

        $this->actingAs($employer)
            ->put(route('employer.profile.update'), [
                'name' => 'Mme Tchoua',
                'email' => 'contact@maisontchoua.cm',
                'type' => 'company',
                'company_name' => 'Maison Tchoua',
                'city' => 'Douala',
            ])
            ->assertRedirect(route('employer.dashboard'))->assertSessionHas('status');

        $this->assertDatabaseHas('users', ['id' => $employer->id, 'email' => 'contact@maisontchoua.cm']);
        $this->assertDatabaseHas('employer_profiles', [
            'user_id' => $employer->id, 'type' => 'company', 'company_name' => 'Maison Tchoua', 'city' => 'Douala',
        ]);
    }
}
