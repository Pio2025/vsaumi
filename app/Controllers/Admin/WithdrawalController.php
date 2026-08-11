<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Libraries\PaymentGateway\Services\PayoutService;
use App\Libraries\PaymentGateway\Services\SettlementService;
use App\Models\MerchantModel;
use App\Models\WithdrawalRequestModel;

class WithdrawalController extends BaseController
{
    public function index()
    {
        return view('admin/withdrawals', [
            'pageTitle' => 'Withdrawal Requests',
            'requests'  => model(WithdrawalRequestModel::class)->allWithMerchantNames(),
        ]);
    }

    public function approve(int $id)
    {
        $withdrawalModel = model(WithdrawalRequestModel::class);
        $request         = $withdrawalModel->find($id);

        if ($request === null || $request['status'] !== 'pending') {
            return redirect()->to('admin/withdrawals')->with('error', 'Withdrawal request cannot be approved from its current status.');
        }

        $merchant = model(MerchantModel::class)->find($request['merchant_id']);

        // Approving a withdrawal is what triggers settlement for this
        // merchant — the balance they requested against includes captured-
        // but-not-yet-settled funds, so true those up (fee assessed, status
        // flipped) before PayoutService bundles only 'settled' transactions.
        (new SettlementService())->runBatchForMerchant($request['merchant_id']);

        $result = (new PayoutService())->processForMerchant($request['merchant_id']);

        if ($result === null) {
            $withdrawalModel->update($id, [
                'status'     => 'rejected',
                'admin_note' => 'No settled funds available at time of processing.',
            ]);

            return redirect()->to('admin/withdrawals')->with('error', 'No settled funds were available for this merchant — request was rejected.');
        }

        $withdrawalModel->update($id, [
            'status'    => 'processed',
            'payout_id' => $result['payout_id'],
        ]);

        (new NotificationService())->sendWithdrawalProcessing($merchant, $request);

        return redirect()->to('admin/withdrawals')->with('success', "Withdrawal approved and processed — {$merchant['business_name']} will receive it within 3 working days.");
    }

    public function reject(int $id)
    {
        $withdrawalModel = model(WithdrawalRequestModel::class);
        $request         = $withdrawalModel->find($id);

        if ($request === null || $request['status'] !== 'pending') {
            return redirect()->to('admin/withdrawals')->with('error', 'Withdrawal request cannot be rejected from its current status.');
        }

        $withdrawalModel->update($id, [
            'status'     => 'rejected',
            'admin_note' => $this->request->getPost('admin_note') ?: null,
        ]);

        return redirect()->to('admin/withdrawals')->with('success', 'Withdrawal request rejected.');
    }
}
