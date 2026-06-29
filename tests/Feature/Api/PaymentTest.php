<?php

namespace Tests\Feature\Api;

use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaymentTest extends TestCase
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

    public function test_paiement_abonnement_initie_puis_confirme_active_l_abonnement(): void
    {
        $employer = $this->employer();
        $avant = Subscription::where('employer_id', $employer->id)->count();

        // 1) Initiation (numéro MTN).
        Sanctum::actingAs($employer);
        $res = $this->postJson('/api/v1/payments/initiate', [
            'type' => 'subscription', 'amount' => 15000, 'phone' => '+237680123456', 'plan' => 'pro',
        ])->assertCreated()
            ->assertJsonPath('status', 'pending')
            ->assertJsonPath('provider', 'momo');

        $reference = $res->json('reference');
        $this->assertDatabaseHas('transactions', ['reference' => $reference, 'status' => 'pending']);

        // 2) Callback opérateur (public).
        $this->postJson('/api/v1/payments/callback', ['reference' => $reference, 'status' => 'success'])
            ->assertOk()->assertJsonPath('status', 'success');

        // 3) Effet métier : un nouvel abonnement actif.
        $this->assertSame($avant + 1, Subscription::where('employer_id', $employer->id)->count());
        $this->assertSame('success', Transaction::where('reference', $reference)->first()->status);
    }

    public function test_detection_operateur_orange(): void
    {
        Sanctum::actingAs($this->employer());

        $this->postJson('/api/v1/payments/initiate', [
            'type' => 'boost', 'amount' => 2500, 'phone' => '+237690999888',
        ])->assertCreated()->assertJsonPath('provider', 'om');
    }
}
