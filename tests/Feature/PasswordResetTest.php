<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\Sms\SmsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function spySms(): object
    {
        $spy = new class implements SmsProvider
        {
            public array $sent = [];

            public function send(string $to, string $message): bool
            {
                $this->sent[] = compact('to', 'message');

                return true;
            }
        };
        $this->app->instance(SmsProvider::class, $spy);

        return $spy;
    }

    private function worker(): User
    {
        return User::where('email', 'worker1@cyaowork.cm')->first();
    }

    public function test_demande_de_reinitialisation_envoie_un_code_sms(): void
    {
        $sms = $this->spySms();
        $user = $this->worker();

        $this->post(route('password.email'), ['login' => $user->phone])
            ->assertRedirect(route('password.reset'))
            ->assertSessionHas('pwd_reset_user', $user->id);

        $this->assertCount(1, $sms->sent);
        $this->assertSame($user->phone, $sms->sent[0]['to']);
        $this->assertNotNull(Cache::get("pwd_reset:{$user->id}"));
    }

    public function test_numero_inconnu_redirige_sans_envoyer_de_sms(): void
    {
        $sms = $this->spySms();

        $this->post(route('password.email'), ['login' => '+237600000999'])
            ->assertRedirect(route('password.reset'));

        $this->assertCount(0, $sms->sent);
    }

    public function test_reinitialisation_avec_le_bon_code(): void
    {
        $this->spySms();
        $user = $this->worker();

        $this->post(route('password.email'), ['login' => $user->phone]);
        $code = Cache::get("pwd_reset:{$user->id}");

        $this->post(route('password.update'), [
            'code' => $code,
            'password' => 'nouveauMDP1',
            'password_confirmation' => 'nouveauMDP1',
        ])->assertRedirect(route('login'))->assertSessionHas('status');

        $this->assertTrue(Hash::check('nouveauMDP1', $user->fresh()->password));
        $this->assertNull(Cache::get("pwd_reset:{$user->id}"));
    }

    public function test_code_invalide_est_rejete(): void
    {
        $this->spySms();
        $user = $this->worker();
        $this->post(route('password.email'), ['login' => $user->phone]);

        $this->post(route('password.update'), [
            'code' => '000000',
            'password' => 'nouveauMDP1',
            'password_confirmation' => 'nouveauMDP1',
        ])->assertSessionHasErrors('code');

        $this->assertFalse(Hash::check('nouveauMDP1', $user->fresh()->password));
    }
}
