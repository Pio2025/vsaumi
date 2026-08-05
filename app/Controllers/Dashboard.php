<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\PayoutModel;
use App\Models\SubscriptionModel;
use App\Models\TransactionModel;

class Dashboard extends BaseController
{
    protected array $plans = [
        'starter'    => ['label' => 'Starter', 'price' => 29, 'note' => 'Up to 500 transactions/mo'],
        'growth'     => ['label' => 'Growth', 'price' => 79, 'note' => 'Up to 5,000 transactions/mo'],
        'enterprise' => ['label' => 'Enterprise', 'price' => 199, 'note' => 'Unlimited, priority support'],
    ];

    protected function currentMerchant(): array
    {
        return model(MerchantModel::class)->find(session()->get('merchant_id'));
    }

    public function index()
    {
        $merchant = $this->currentMerchant();
        $txModel  = model(TransactionModel::class);

        $stats = [
            'total'   => $txModel->where('merchant_id', $merchant['id'])->countAllResults(false),
            'pending' => $txModel->where('merchant_id', $merchant['id'])->whereIn('status', ['pending', 'authorized'])->countAllResults(false),
            'settled' => $txModel->where('merchant_id', $merchant['id'])->where('status', 'settled')->countAllResults(false),
            'failed'  => $txModel->where('merchant_id', $merchant['id'])->where('status', 'failed')->countAllResults(),
        ];

        return view('dashboard/index', [
            'pageTitle'    => 'Dashboard',
            'merchant'     => $merchant,
            'stats'        => $stats,
            'subscription' => model(SubscriptionModel::class)->latestForMerchant($merchant['id']),
            'apiSecretOnce' => session()->getFlashdata('api_secret_once'),
            'plans'        => $this->plans,
        ]);
    }

    public function subscribe()
    {
        $plan = $this->request->getPost('plan');
        $merchant = $this->currentMerchant();

        if (! isset($this->plans[$plan])) {
            return redirect()->to('/dashboard')->with('error', 'Please choose a valid plan.');
        }

        if (! in_array($merchant['status'], ['pending', 'approved', 'suspended'], true)) {
            return redirect()->to('/dashboard')->with('error', 'Subscription already active.');
        }

        model(SubscriptionModel::class)->insert([
            'merchant_id' => $merchant['id'],
            'plan'        => $plan,
            'amount'      => $this->plans[$plan]['price'],
            'status'      => 'active',
            'started_at'  => date('Y-m-d H:i:s'),
            'expires_at'  => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);

        model(MerchantModel::class)->update($merchant['id'], ['status' => 'active']);

        return redirect()->to('/dashboard')->with('success', "Subscribed to the {$this->plans[$plan]['label']} plan (simulated payment). Your API is now live for 30 days.");
    }

    public function subscriptions()
    {
        $merchant = $this->currentMerchant();

        $subscriptions = model(SubscriptionModel::class)->allForMerchant($merchant['id']);

        return view('dashboard/subscriptions', [
            'pageTitle'     => 'Subscription History',
            'merchant'      => $merchant,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function transactions()
    {
        $merchant = $this->currentMerchant();

        $transactions = model(TransactionModel::class)
            ->where('merchant_id', $merchant['id'])
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('dashboard/transactions', [
            'pageTitle'    => 'Transactions',
            'merchant'     => $merchant,
            'transactions' => $transactions,
        ]);
    }

    public function payouts()
    {
        $merchant = $this->currentMerchant();

        $payouts = model(PayoutModel::class)
            ->where('merchant_id', $merchant['id'])
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('dashboard/payouts', [
            'pageTitle' => 'Payouts',
            'merchant'  => $merchant,
            'payouts'   => $payouts,
        ]);
    }
}
