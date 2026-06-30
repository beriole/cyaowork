<?php

namespace Tests\Feature;

use App\Services\Sms\SmsProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpSmsTest extends TestCase
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

    public function test_l_inscription_envoie_le_code_par_sms(): void
    {
        $sms = $this->spySms();

        $this->post(route('register'), [
            'name' => 'Test User',
            'phone' => '+237699112233',
            'password' => 'secret123',
            'role' => 'worker',
        ])->assertRedirect(route('otp.show'));

        $this->assertCount(1, $sms->sent);
        $this->assertSame('+237699112233', $sms->sent[0]['to']);
        $this->assertStringContainsString('CyaoWork', $sms->sent[0]['message']);
    }

    public function test_le_driver_sms_est_selectionnable_par_config(): void
    {
        config(['services.sms.driver' => 'http', 'services.sms.http' => ['endpoint' => 'https://x', 'token' => 't', 'sender' => 'CyaoWork']]);
        $this->assertInstanceOf(\App\Services\Sms\HttpSmsProvider::class, app(SmsProvider::class));

        config(['services.sms.driver' => 'log']);
        $this->app->forgetInstance(SmsProvider::class);
        $this->assertInstanceOf(\App\Services\Sms\LogSmsProvider::class, app(SmsProvider::class));
    }
}
