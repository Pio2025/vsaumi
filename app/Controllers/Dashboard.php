<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\PayoutModel;
use App\Models\SubscriptionModel;
use App\Models\TransactionModel;

class Dashboard extends BaseController
{
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
            'subscriptions' => model(SubscriptionModel::class)->allForMerchant($merchant['id']),
            'applications' => model(ApplicationModel::class)->allForMerchant($merchant['id']),
            'newApplicationCredentials' => session()->getFlashdata('new_application_credentials'),
        ]);
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
