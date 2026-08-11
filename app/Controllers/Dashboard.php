<?php

namespace App\Controllers;

use App\Models\ApplicationModel;
use App\Models\MerchantModel;
use App\Models\PayoutModel;
use App\Models\SubscriptionModel;
use App\Models\TransactionModel;
use App\Models\WithdrawalRequestModel;

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
            'total'   => $txModel->joinedForMerchant($merchant['id'])->countAllResults(),
            'pending' => $txModel->joinedForMerchant($merchant['id'])->whereIn('transactions.status', ['pending', 'authorized'])->countAllResults(),
            'settled' => $txModel->joinedForMerchant($merchant['id'])->where('transactions.status', 'settled')->countAllResults(),
            'failed'  => $txModel->joinedForMerchant($merchant['id'])->where('transactions.status', 'failed')->countAllResults(),
        ];

        return view('dashboard/index', [
            'pageTitle'    => 'Dashboard',
            'merchant'     => $merchant,
            'stats'        => $stats,
            'availableBalance' => $txModel->availableBalanceForMerchant($merchant['id']),
            'totalRevenue'     => $txModel->totalRevenueForMerchant($merchant['id']),
            'pendingWithdrawal' => model(WithdrawalRequestModel::class)->pendingForMerchant($merchant['id']),
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
            ->joinedForMerchant($merchant['id'])
            ->orderBy('transactions.id', 'DESC')
            ->findAll();

        return view('dashboard/transactions', [
            'pageTitle'    => 'Transactions',
            'merchant'     => $merchant,
            'transactions' => $transactions,
        ]);
    }

    public function transactionView(int $id)
    {
        $merchant    = $this->currentMerchant();
        $transaction = model(TransactionModel::class)->findForMerchant($id, $merchant['id']);

        if ($transaction === null) {
            return redirect()->to('dashboard/transactions')->with('error', 'Transaction not found.');
        }

        return view('dashboard/transaction_view', [
            'pageTitle'   => 'Transaction ' . $transaction['reference'],
            'merchant'    => $merchant,
            'transaction' => $transaction,
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
