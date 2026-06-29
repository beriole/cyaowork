<?php

namespace Tests\Feature\Web;

use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminVerificationWebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function admin(): User
    {
        return User::where('email', 'admin@cyaowork.cm')->first();
    }

    private function pendingProfile(): WorkerProfile
    {
        return WorkerProfile::where('verification_status', 'pending')->firstOrFail();
    }

    public function test_admin_valide_un_profil_depuis_le_dashboard(): void
    {
        $profile = $this->pendingProfile();

        $this->actingAs($this->admin())
            ->post(route('admin.verifications.approve', $profile))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertSame('verified', $profile->fresh()->verification_status);
        $this->assertTrue($profile->user->fresh()->is_verified);
    }

    public function test_admin_rejette_un_profil(): void
    {
        $profile = $this->pendingProfile();

        $this->actingAs($this->admin())
            ->post(route('admin.verifications.reject', $profile))
            ->assertRedirect();

        $this->assertSame('rejected', $profile->fresh()->verification_status);
    }

    public function test_un_non_admin_ne_peut_pas_valider(): void
    {
        $worker = User::where('email', 'worker1@cyaowork.cm')->first();

        $this->actingAs($worker)
            ->post(route('admin.verifications.approve', $this->pendingProfile()))
            ->assertForbidden();
    }
}
