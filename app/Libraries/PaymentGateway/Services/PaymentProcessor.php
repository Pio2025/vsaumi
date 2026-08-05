<?php

namespace App\Libraries\PaymentGateway\Services;

use App\Libraries\PaymentGateway\Core\Interfaces\PaymentGatewayInterface;
use App\Models\TransactionModel;
use Config\Payment;
use RuntimeException;

class PaymentProcessor
{
    protected Payment $config;
    protected TransactionModel $transactions;

    public function __construct(?Payment $config = null, ?TransactionModel $transactions = null)
    {
        $this->config       = $config ?? config(Payment::class);
        $this->transactions = $transactions ?? model(TransactionModel::class);
    }

    /**
     * Initiate a payment for a merchant and persist the resulting transaction.
     *
     * @param array{id: int} $merchant
     * @param array{amount: float, currency?: string, customer_email?: string, customer_msisdn?: string} $data
     */
    public function initiatePayment(array $merchant, string $paymentMethod, array $data): array
    {
        $adapter   = $this->resolveAdapter($paymentMethod);
        $reference = $this->generateReference($paymentMethod);

        $result = $adapter->processPayment($data + ['reference' => $reference]);

        $transactionId = $this->transactions->insert([
            'merchant_id'     => $merchant['id'],
            'reference'       => $reference,
            'customer_email'  => $data['customer_email'] ?? null,
            'customer_msisdn' => $data['customer_msisdn'] ?? null,
            'amount'          => $data['amount'],
            'currency'        => $data['currency'] ?? 'FJD',
            'payment_method'  => $paymentMethod,
            'status'          => $result['status'] ?? 'pending',
            'psp_reference'   => $result['psp_reference'] ?? null,
            'metadata'        => json_encode($data['metadata'] ?? []),
        ], true);

        return [
            'transaction_id' => $transactionId,
            'reference'      => $reference,
            'psp_reference'  => $result['psp_reference'] ?? null,
            'status'         => $result['status'] ?? 'pending',
            'message'        => $result['message'] ?? '',
        ];
    }

    /**
     * Demo-mode only: simulate a provider's outcome for a transaction we
     * initiated ourselves, without a real webhook round-trip. Used by the
     * browser checkout flow to stand in for "customer approved on their
     * phone" / "card issuer authorized" until real provider APIs are wired
     * up — at which point this is replaced by genuine applyWebhook() calls
     * arriving from Vodafone/Digicel/the card processor.
     *
     * @param 'captured'|'failed' $outcome
     */
    public function confirmDemoPayment(string $reference, string $outcome): array
    {
        $transaction = $this->transactions->findByReference($reference);

        if ($transaction === null) {
            throw new RuntimeException("No transaction found for reference: {$reference}");
        }

        $this->transactions->update($transaction['id'], ['status' => $outcome]);

        return $this->transactions->find($transaction['id']);
    }

    /**
     * Apply an incoming webhook payload from a provider to its matching transaction.
     */
    public function applyWebhook(string $paymentMethod, array $payload): array
    {
        $adapter = $this->resolveAdapter($paymentMethod);
        $result  = $adapter->handleWebhook($payload);

        if (empty($result['psp_reference'])) {
            return $result;
        }

        $transaction = $this->transactions->where('psp_reference', $result['psp_reference'])->first();

        if ($transaction !== null) {
            $this->transactions->update($transaction['id'], ['status' => $result['status']]);
        }

        return $result;
    }

    protected function resolveAdapter(string $paymentMethod): PaymentGatewayInterface
    {
        $adapterClass = $this->config->adapters[$paymentMethod] ?? null;

        if ($adapterClass === null) {
            throw new RuntimeException("Unsupported payment method: {$paymentMethod}");
        }

        return new $adapterClass();
    }

    protected function generateReference(string $paymentMethod): string
    {
        return strtoupper($paymentMethod) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
    }
}
