<?php

namespace Tests\Feature\Web;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerProfileEditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function worker(): User
    {
        return User::where('email', 'worker1@cyaowork.cm')->first();
    }

    public function test_le_formulaire_de_profil_se_charge(): void
    {
        $this->actingAs($this->worker())
            ->get(route('worker.profile.edit'))
            ->assertOk()->assertSee('Mon profil');
    }

    public function test_le_travailleur_met_a_jour_son_profil_et_ses_competences(): void
    {
        $skillIds = Skill::limit(2)->pluck('id')->all();

        $this->actingAs($this->worker())
            ->put(route('worker.profile.update'), [
                'headline' => 'Cuisinière polyvalente',
                'bio' => 'Dix ans en restauration.',
                'availability' => 'week',
                'salary_period' => 'month',
                'expected_salary' => 90000,
                'city' => 'Yaoundé',
                'experience_years' => 10,
                'skills' => $skillIds,
            ])
            ->assertRedirect(route('worker.dashboard'))->assertSessionHas('status');

        $profile = $this->worker()->workerProfile->fresh();
        $this->assertSame('Cuisinière polyvalente', $profile->headline);
        $this->assertSame('Yaoundé', $profile->city);
        $this->assertEqualsCanonicalizing($skillIds, $profile->skills->pluck('id')->all());
    }
}
