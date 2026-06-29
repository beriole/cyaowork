<?php

namespace Tests\Feature;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Payment\FapshiProvider;
use App\Services\Payment\PaymentProvider;
use App\Services\Payment\PaymentService;
use App\Services\Payment\SandboxProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FapshiPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.fapshi' => [
            'base_url' => 'https://sandbox.fapshi.com',
            'api_user' => 'test-user',
            'api_key' => 'test-key',
        ]]);
    }

    private function employer(): User
    {
        return User::where('email', 'employeur@cyaowork.cm')->first();
    }

    public function test_le_driver_est_fapshi_quand_la_config_le_demande(): void
    {
        config(['services.payment.driver' => 'fapshi']);
        $this->assertInstanceOf(FapshiProvider::class, app(PaymentProvider::class));

        config(['services.payment.driver' => 'sandbox']);
        $this->app->forgetInstance(PaymentProvider::class);
        $this->assertInstanceOf(SandboxProvider::class, app(PaymentProvider::class));
    }

    public function test_initiate_appelle_fapshi_direct_pay_et_cree_une_transaction_pending(): void
    {
        Http::fake([
            'sandbox.fapshi.com/direct-pay' => Http::response([
                'message' => 'Accepted', 'transId' => 'FAP123XYZ', 'dateInitiated' => now()->toIso8601String(),
            ], 200),
        ]);

        $provider = new FapshiProvider(config('services.fapshi'));
        $service = new PaymentService($provider);

        $tx = $service->initiate($this->employer(), 'subscription', 15000, '+237680123456', ['plan' => 'pro']);

        $this->assertSame('pending', $tx->status);
        $this->assertSame('FAP123XYZ', $tx->meta['operator_reference']);
        $this->assertSame('momo', $tx->provider); // 680 → MTN

        Http::assertSent(function ($request) use ($tx) {
            return str_contains($request->url(), '/direct-pay')
                && $request['medium'] === 'mobile money'
                && $request['phone'] === '680123456'
                && $request['externalId'] === $tx->reference;
        });
    }

    public function test_orange_money_detecte_pour_un_numero_69x(): void
    {
        $provider = new FapshiProvider(config('services.fapshi'));
        $this->assertSame('orange', $provider->operatorFor('+237690000000'));
        $this->assertSame('mtn', $provider->operatorFor('+237670000000'));
    }

    public function test_normalisation_des_statuts_fapshi(): void
    {
        $p = new FapshiProvider(config('services.fapshi'));
        $this->assertSame('success', $p->normalize('SUCCESSFUL'));
        $this->assertSame('failed', $p->normalize('EXPIRED'));
        $this->assertSame('failed', $p->normalize('FAILED'));
        $this->assertSame('pending', $p->normalize('PENDING'));
    }

    public function test_le_webhook_fapshi_confirme_et_active_l_abonnement(): void
    {
        // Transaction en attente comme si initiée par Fapshi.
        $tx = Transaction::create([
            'user_id' => $this->employer()->id, 'type' => 'subscription', 'amount' => 15000,
            'provider' => 'momo', 'phone' => '+237680123456', 'reference' => 'CW-TEST-001',
            'status' => 'pending', 'meta' => ['plan' => 'pro', 'operator_reference' => 'FAP123XYZ'],
        ]);
        $avant = Subscription::where('employer_id', $this->employer()->id)->count();

        $this->postJson('/api/v1/payments/fapshi/webhook', [
            'transId' => 'FAP123XYZ', 'status' => 'SUCCESSFUL', 'externalId' => 'CW-TEST-001',
        ])->assertOk()->assertJson(['reference' => 'CW-TEST-001', 'status' => 'success']);

        $this->assertSame('success', $tx->fresh()->status);
        $this->assertSame($avant + 1, Subscription::where('employer_id', $this->employer()->id)->count());
    }

    public function test_le_webhook_fapshi_marque_echec_sans_activer(): void
    {
        $tx = Transaction::create([
            'user_id' => $this->employer()->id, 'type' => 'boost', 'amount' => 2500,
            'provider' => 'om', 'phone' => '+237690123456', 'reference' => 'CW-TEST-002',
            'status' => 'pending', 'meta' => ['offer_id' => 1, 'operator_reference' => 'FAPFAIL'],
        ]);

        $this->postJson('/api/v1/payments/fapshi/webhook', [
            'transId' => 'FAPFAIL', 'status' => 'FAILED', 'externalId' => 'CW-TEST-002',
        ])->assertOk();

        $this->assertSame('failed', $tx->fresh()->status);
    }
}
