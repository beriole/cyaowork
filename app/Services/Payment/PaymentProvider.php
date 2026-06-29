<?php

namespace App\Services\Payment;

interface PaymentProvider
{
    /**
     * Initie une collecte Mobile Money.
     *
     * @return array{operator_reference:string, status:string}
     */
    public function initiate(float $amount, string $phone, string $reference, string $description): array;

    /** Nom de l'opérateur (mtn / orange) déduit du numéro, sinon défaut. */
    public function operatorFor(string $phone): string;

    /** Statut normalisé d'une transaction côté opérateur : pending | success | failed. */
    public function status(string $operatorReference): string;
}
