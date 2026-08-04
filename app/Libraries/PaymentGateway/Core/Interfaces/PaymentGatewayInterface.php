<?php

namespace App\Libraries\PaymentGateway\Core\Interfaces;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment with the provider.
     *
     * @param array $paymentData Standardized payment data: amount, currency,
     *                           reference, customer details, etc.
     *
     * @return array{status: string, psp_reference: string, message: string}
     */
    public function processPayment(array $paymentData): array;

    /**
     * Handle an asynchronous webhook/callback payload from the provider.
     * Implementations must verify the payload's authenticity before trusting it.
     */
    public function handleWebhook(array $payload): array;

    /**
     * Query the provider for the current status of a previously initiated transaction.
     *
     * @return array{status: string}
     */
    public function verifyTransaction(string $referenceId): array;
}
