<?php

namespace App\Libraries\PaymentGateway\Adapters;

use App\Libraries\PaymentGateway\Core\Interfaces\PaymentGatewayInterface;
use Config\Payment;

/**
 * Digicel Fiji MyCash mobile money adapter.
 *
 * NOTE: endpoint paths, request/response shapes and signature scheme below are
 * placeholders. They must be replaced once Digicel Fiji provides API access
 * and documentation under a merchant agreement.
 */
class MyCashAdapter implements PaymentGatewayInterface
{
    private string $apiUrl;
    private string $apiKey;
    private string $merchantId;
    private string $webhookSecret;

    public function __construct()
    {
        $config              = config(Payment::class);
        $this->apiUrl        = $config->mycashApiUrl;
        $this->apiKey        = $config->mycashApiKey;
        $this->merchantId    = $config->mycashMerchantId;
        $this->webhookSecret = $config->mycashWebhookSecret;
    }

    public function processPayment(array $paymentData): array
    {
        // TODO: POST to Digicel MyCash's payment-request endpoint with
        // $paymentData['customer_msisdn'], amount, and a unique reference.
        log_message('info', 'Processing MyCash payment: ' . json_encode([
            'reference' => $paymentData['reference'] ?? null,
            'amount'    => $paymentData['amount'] ?? null,
        ]));

        return [
            'status'        => 'pending',
            'psp_reference' => 'MYCASH-' . bin2hex(random_bytes(8)),
            'message'       => 'MyCash payment request sent to customer for approval.',
        ];
    }

    public function handleWebhook(array $payload): array
    {
        if (! $this->verifyWebhookSignature($payload)) {
            log_message('warning', 'MyCash webhook signature verification failed.');

            return ['psp_reference' => '', 'status' => 'invalid'];
        }

        log_message('info', 'MyCash webhook received: ' . json_encode($payload));

        return [
            'psp_reference' => $payload['psp_reference'] ?? '',
            'status'        => $payload['status'] ?? 'unknown',
        ];
    }

    public function verifyTransaction(string $referenceId): array
    {
        // TODO: call Digicel's transaction status/lookup endpoint.
        return ['status' => 'unknown'];
    }

    private function verifyWebhookSignature(array $payload): bool
    {
        $signature = $payload['signature'] ?? '';
        unset($payload['signature']);

        $expected = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);

        return $signature !== '' && hash_equals($expected, $signature);
    }
}
