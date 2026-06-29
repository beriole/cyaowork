<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\Payment\FapshiProvider;
use App\Services\Payment\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $payments) {}

    /** POST /payments/initiate — démarre une collecte Mobile Money. */
    public function initiate(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:subscription,boost,commission'],
            'amount' => ['required', 'numeric', 'min:1'],
            'phone' => ['required', 'string', 'max:30'],
            'plan' => ['nullable', 'in:pro,premium'],
            'offer_id' => ['nullable', 'integer', 'exists:job_offers,id'],
        ]);

        $meta = array_filter([
            'plan' => $data['plan'] ?? null,
            'offer_id' => $data['offer_id'] ?? null,
        ]);

        $tx = $this->payments->initiate($request->user(), $data['type'], (float) $data['amount'], $data['phone'], $meta);

        return response()->json([
            'reference' => $tx->reference,
            'provider' => $tx->provider,
            'status' => $tx->status,
            'amount' => $tx->amount,
            'message' => 'Validez le paiement sur votre téléphone (push USSD).',
        ], 201);
    }

    /** POST /payments/callback — webhook opérateur (sandbox : confirme par référence). */
    public function callback(Request $request)
    {
        $data = $request->validate([
            'reference' => ['required', 'exists:transactions,reference'],
            'status' => ['nullable', 'in:success,failed'],
        ]);

        $tx = Transaction::where('reference', $data['reference'])->firstOrFail();
        $tx = $this->payments->confirm($tx, ($data['status'] ?? 'success') === 'success');

        return response()->json(['reference' => $tx->reference, 'status' => $tx->status]);
    }

    /**
     * POST /payments/fapshi/webhook — notification de Fapshi sur changement de statut.
     * Fapshi renvoie l'objet transaction (transId, status, externalId…). On retrouve la
     * transaction par notre référence (externalId) et on applique le résultat.
     */
    public function fapshiWebhook(Request $request)
    {
        $data = $request->validate([
            'transId' => ['required', 'string'],
            'status' => ['required', 'string'],
            'externalId' => ['nullable', 'string'],
        ]);

        $tx = Transaction::query()
            ->when($data['externalId'] ?? null, fn ($q, $ref) => $q->where('reference', $ref))
            ->when(empty($data['externalId']), fn ($q) => $q->where('meta->operator_reference', $data['transId']))
            ->first();

        if (! $tx) {
            return response()->json(['message' => 'Transaction inconnue.'], 404);
        }

        $normalized = (new FapshiProvider(config('services.fapshi')))->normalize($data['status']);
        if ($normalized !== 'pending') {
            $this->payments->confirm($tx, $normalized === 'success');
        }

        return response()->json(['reference' => $tx->reference, 'status' => $tx->fresh()->status]);
    }

    /** GET /payments — historique des transactions de l'utilisateur. */
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->transactions()->latest()->take(20)->get(['id', 'type', 'amount', 'provider', 'reference', 'status', 'created_at'])
        );
    }
}
