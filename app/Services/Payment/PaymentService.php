<?php

namespace App\Services\Payment;

use App\Models\JobOffer;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Str;

class PaymentService
{
    public function __construct(private PaymentProvider $provider) {}

    /**
     * Initie un paiement et crée la transaction (statut pending).
     *
     * @param  string  $type  subscription | boost | commission
     */
    public function initiate(User $user, string $type, float $amount, string $phone, array $meta = []): Transaction
    {
        $reference = 'CW-'.now()->format('ymd').'-'.Str::upper(Str::random(6));
        $operator = $this->provider->operatorFor($phone);

        $result = $this->provider->initiate($amount, $phone, $reference, "CyaoWork {$type}");

        return Transaction::create([
            'user_id' => $user->id,
            'type' => $type,
            'amount' => $amount,
            'provider' => $operator === 'orange' ? 'om' : 'momo',
            'phone' => $phone,
            'reference' => $reference,
            'status' => $result['status'],
            'meta' => $meta + ['operator_reference' => $result['operator_reference']],
        ]);
    }

    /** Confirme une transaction (callback opérateur) et applique son effet métier. */
    public function confirm(Transaction $transaction, bool $success = true): Transaction
    {
        if ($transaction->status !== 'pending') {
            return $transaction;
        }

        $transaction->update(['status' => $success ? 'success' : 'failed']);

        if ($success) {
            $this->applyEffect($transaction);
            $transaction->user?->notify(new \App\Notifications\PaymentConfirmed($transaction));
        }

        return $transaction->refresh();
    }

    private function applyEffect(Transaction $t): void
    {
        match ($t->type) {
            'subscription' => $this->activateSubscription($t),
            'boost' => $this->boostOffer($t),
            default => null,
        };
    }

    private function activateSubscription(Transaction $t): void
    {
        $plan = $t->meta['plan'] ?? 'pro';
        $current = Subscription::where('employer_id', $t->user_id)->where('status', 'active')->latest()->first();
        $start = $current && $current->ends_at?->isFuture() ? $current->ends_at : now();

        Subscription::create([
            'employer_id' => $t->user_id, 'plan' => $plan, 'status' => 'active',
            'starts_at' => now(), 'ends_at' => $start->copy()->addDays(30),
        ]);
    }

    private function boostOffer(Transaction $t): void
    {
        if ($offerId = $t->meta['offer_id'] ?? null) {
            JobOffer::where('id', $offerId)->where('employer_id', $t->user_id)->update(['is_boosted' => true]);
        }
    }
}
