<?php

namespace App\Controllers;

use App\Libraries\PaymentGateway\Services\PaymentProcessor;
use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\TransactionModel;

/**
 * Demo checkout flow. Stands in for a merchant's real "Pay with VSaumi"
 * button + hosted payment page. There is no real card processor or mobile
 * money network behind this — the "approve" / "decline" step below is a
 * stand-in for the customer's bank 3DS challenge or M-PAiSA/MyCash phone
 * prompt, until real provider credentials are wired into the adapters.
 */
class Checkout extends BaseController
{
    protected array $methods = [
        'visa'       => ['label' => 'Visa', 'kind' => 'card'],
        'mastercard' => ['label' => 'Mastercard', 'kind' => 'card'],
        'mpaisa'     => ['label' => 'M-PAiSA', 'kind' => 'mobile'],
        'mycash'     => ['label' => 'MyCash', 'kind' => 'mobile'],
    ];

    /**
     * Resolves the application a checkout link belongs to. Requires both
     * the parent merchant (KYC/approval gate) and the application itself
     * to be active — a suspended merchant shouldn't be able to keep
     * accepting payments through any of its surviving apps.
     */
    protected function applicationByApiKey(string $apiKey): ?array
    {
        $application = model(ApplicationModel::class)->findByApiKey($apiKey);

        if ($application === null || $application['status'] !== 'active') {
            return null;
        }

        $merchant = model(MerchantModel::class)->find($application['merchant_id']);

        if ($merchant === null || $merchant['status'] !== 'active') {
            return null;
        }

        return ['application' => $application, 'merchant' => $merchant];
    }

    public function methods(string $apiKey)
    {
        $ctx = $this->applicationByApiKey($apiKey);

        if ($ctx === null) {
            return redirect()->to('/')->with('error', 'This merchant is not available for checkout right now.');
        }

        $amount = (float) ($this->request->getGet('amount') ?? 0);

        if ($amount <= 0) {
            return redirect()->to('/')->with('error', 'Please provide a valid amount to check out with.');
        }

        return view('checkout/methods', [
            'pageTitle'   => 'Choose a payment method',
            'merchant'    => $ctx['merchant'],
            'application' => $ctx['application'],
            'amount'      => $amount,
            'methods'     => $this->methods,
        ]);
    }

    public function form(string $apiKey, string $method)
    {
        $ctx    = $this->applicationByApiKey($apiKey);
        $amount = (float) ($this->request->getGet('amount') ?? 0);

        if ($ctx === null || $amount <= 0 || ! isset($this->methods[$method])) {
            return redirect()->to('/')->with('error', 'Invalid checkout request.');
        }

        return view('checkout/form', [
            'pageTitle'   => $this->methods[$method]['label'] . ' checkout',
            'merchant'    => $ctx['merchant'],
            'application' => $ctx['application'],
            'amount'      => $amount,
            'method'      => $method,
            'methodInfo'  => $this->methods[$method],
        ]);
    }

    public function process(string $apiKey, string $method)
    {
        $ctx    = $this->applicationByApiKey($apiKey);
        $amount = (float) $this->request->getPost('amount');

        if ($ctx === null || $amount <= 0 || ! isset($this->methods[$method])) {
            return redirect()->to('/')->with('error', 'Invalid checkout request.');
        }

        $merchant = $ctx['merchant'];

        $data = [
            'amount'   => $amount,
            'currency' => 'FJD',
        ];

        if ($this->methods[$method]['kind'] === 'mobile') {
            $data['customer_msisdn'] = $this->request->getPost('msisdn');
        } else {
            $data['customer_email'] = $this->request->getPost('email');
            $data['metadata']       = ['card_last4' => substr((string) $this->request->getPost('card_number'), -4)];
        }

        $processor = new PaymentProcessor();
        $result    = $processor->initiatePayment($merchant, $method, $data);

        return redirect()->to('checkout/approve/' . $result['reference']);
    }

    public function approve(string $reference)
    {
        $transaction = model(TransactionModel::class)->findByReference($reference);

        if ($transaction === null) {
            return redirect()->to('/')->with('error', 'Transaction not found.');
        }

        $merchant = model(MerchantModel::class)->find($transaction['merchant_id']);

        if ($transaction['status'] !== 'pending') {
            return redirect()->to('checkout/result/' . $reference);
        }

        return view('checkout/approve', [
            'pageTitle'   => 'Confirm payment',
            'transaction' => $transaction,
            'merchant'    => $merchant,
            'methodInfo'  => $this->methods[$transaction['payment_method']] ?? ['label' => strtoupper($transaction['payment_method']), 'kind' => 'card'],
        ]);
    }

    public function confirm(string $reference)
    {
        $decision = $this->request->getPost('decision') === 'approve' ? 'captured' : 'failed';

        $processor = new PaymentProcessor();

        try {
            $processor->confirmDemoPayment($reference, $decision);
        } catch (\RuntimeException $e) {
            return redirect()->to('/')->with('error', $e->getMessage());
        }

        return redirect()->to('checkout/result/' . $reference);
    }

    public function result(string $reference)
    {
        $transaction = model(TransactionModel::class)->findByReference($reference);

        if ($transaction === null) {
            return redirect()->to('/')->with('error', 'Transaction not found.');
        }

        $merchant = model(MerchantModel::class)->find($transaction['merchant_id']);

        return view('checkout/result', [
            'pageTitle'   => 'Payment result',
            'transaction' => $transaction,
            'merchant'    => $merchant,
        ]);
    }
}
