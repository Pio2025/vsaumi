<?php

namespace App\Libraries\PaymentGateway\Adapters;

use App\Libraries\PaymentGateway\Core\Interfaces\PaymentGatewayInterface;
use Config\Payment;

/**
 * Shared logic for card-network adapters (Visa, Mastercard).
 * Both networks are processed through the same acquiring/processor API;
 * only the `cardNetwork` value sent to the processor differs.
 */
abstract class CardAdapter implements PaymentGatewayInterface
{
    protected string $cardNetwork;
    protected string $apiUrl;
    protected string $apiKey;
    protected string $apiSecret;

    public function __construct()
    {
        $config          = config(Payment::class);
        $this->apiUrl    = $config->cardApiUrl;
        $this->apiKey    = $config->cardApiKey;
        $this->apiSecret = $config->cardApiSecret;
    }

    public function processPayment(array $paymentData): array
    {
        // TODO: replace with a real call to the card processor/acquiring bank API.
        // Expected $paymentData keys: amount, currency, reference, card_token (never raw PAN/CVV).
        log_message('info', "Processing {$this->cardNetwork} payment: " . json_encode([
            'reference' => $paymentData['reference'] ?? null,
            'amount'    => $paymentData['amount'] ?? null,
        ]));

        return [
            'status'        => 'pending',
            'psp_reference' => strtoupper($this->cardNetwork) . '-' . bin2hex(random_bytes(8)),
            'message'       => 'Payment authorization initiated.',
        ];
    }

    public function handleWebhook(array $payload): array
    {
        // TODO: verify the processor's webhook signature before trusting $payload.
        log_message('info', "{$this->cardNetwork} webhook received: " . json_encode($payload));

        return [
            'psp_reference' => $payload['psp_reference'] ?? '',
            'status'        => $payload['status'] ?? 'unknown',
        ];
    }

    public function verifyTransaction(string $referenceId): array
    {
        // TODO: call the processor's status/lookup endpoint.
        return ['status' => 'unknown'];
    }
}
