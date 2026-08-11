<?php

namespace App\Controllers;

use App\Models\MerchantModel;
use App\Models\TransactionModel;
use App\Models\WithdrawalRequestModel;

class WithdrawalController extends BaseController
{
    protected function currentMerchant(): array
    {
        return model(MerchantModel::class)->find(session()->get('merchant_id'));
    }

    public function index()
    {
        $merchant = $this->currentMerchant();

        return view('dashboard/withdrawals', [
            'pageTitle' => 'My Withdrawal Request',
            'merchant'  => $merchant,
            'requests'  => model(WithdrawalRequestModel::class)->allForMerchant($merchant['id']),
        ]);
    }

    public function request()
    {
        $merchant           = $this->currentMerchant();
        $withdrawalModel    = model(WithdrawalRequestModel::class);

        if ($withdrawalModel->pendingForMerchant($merchant['id']) !== null) {
            return redirect()->to('/dashboard')->with('error', 'You already have a withdrawal request pending review.');
        }

        $balance = model(TransactionModel::class)->availableBalanceForMerchant($merchant['id']);

        if ($balance <= 0) {
            return redirect()->to('/dashboard')->with('error', 'You have no available balance to withdraw.');
        }

        $withdrawalModel->insert([
            'merchant_id' => $merchant['id'],
            'amount'      => $balance,
            'status'      => 'pending',
        ]);

        return redirect()->to('/dashboard')->with('success', 'Withdrawal request submitted for FJD ' . number_format($balance, 2) . ' — an admin will review it shortly.');
    }
}
