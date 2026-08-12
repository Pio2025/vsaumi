<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\NotificationService;
use App\Libraries\PaymentGateway\Services\PayoutService;
use App\Libraries\PaymentGateway\Services\SettlementService;
use App\Models\MerchantModel;
use App\Models\TransactionModel;
use App\Models\WithdrawalRequestModel;

class WithdrawalController extends BaseController
{
    public function index()
    {
        $transactionModel = model(TransactionModel::class);
        $requests         = model(WithdrawalRequestModel::class)->allWithMerchantNames();

        $transactionsByRequest = [];

        foreach ($requests as $request) {
            $transactionsByRequest[$request['id']] = $this->resolveTransactions($transactionModel, $request);
        }

        return view('admin/withdrawals', [
            'pageTitle'             => 'Withdrawal Requests',
            'requests'              => $requests,
            'transactionsByRequest' => $transactionsByRequest,
        ]);
    }

    public function approve(int $id)
    {
        $withdrawalModel = model(WithdrawalRequestModel::class);
        $request         = $withdrawalModel->find($id);

        if ($request === null || $request['status'] !== 'pending') {
            return redirect()->to('admin/withdrawals')->with('error', 'Withdrawal request cannot be approved from its current status.');
        }

        $merchant         = model(MerchantModel::class)->find($request['merchant_id']);
        $transactionModel = model(TransactionModel::class);
        $transactions      = $this->resolveTransactions($transactionModel, $request);

        // Approving a withdrawal is what triggers settlement — the
        // snapshotted transactions may still be 'captured' (fee not yet
        // assessed) or already 'settled', so only settle the ones that
        // still need it before bundling all of them into the payout.
        $capturedTransactions = array_values(array_filter(
            $transactions,
            static fn (array $transaction): bool => $transaction['status'] === 'captured'
        ));

        if ($capturedTransactions !== []) {
            (new SettlementService())->settleTransactions($capturedTransactions);
        }

        $result = (new PayoutService())->processTransactions($request['merchant_id'], array_column($transactions, 'id'));

        if ($result === null) {
            $withdrawalModel->update($id, [
                'status'     => 'rejected',
                'admin_note' => 'No settled funds were available at time of processing.',
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

        $disposition = $this->request->getPost('disposition');
        $reason      = trim((string) $this->request->getPost('admin_note'));

        if (! in_array($disposition, ['returned', 'forfeited'], true) || $reason === '') {
            return redirect()->to('admin/withdrawals')->with('error', 'Please provide a rejection reason and choose what happens to the funds.');
        }

        $merchant = model(MerchantModel::class)->find($request['merchant_id']);

        if ($disposition === 'forfeited') {
            $transactionModel = model(TransactionModel::class);
            $transactions      = $this->resolveTransactions($transactionModel, $request);
            $transactionModel->voidTransactions(array_column($transactions, 'id'));
        }

        $withdrawalModel->update($id, [
            'status'           => 'rejected',
            'admin_note'       => $reason,
            'fund_disposition' => $disposition,
        ]);

        (new NotificationService())->sendWithdrawalRejected($merchant, $request, $reason, $disposition);

        return redirect()->to('admin/withdrawals')->with('success', 'Withdrawal request rejected.');
    }

    /**
     * Prefers the snapshot taken when the request was created; falls back
     * to a live re-query for requests created before that snapshot existed
     * (a still-pending legacy request) or a payout-linked lookup (a
     * processed legacy request), so nothing breaks for pre-existing data.
     */
    private function resolveTransactions(TransactionModel $transactionModel, array $request): array
    {
        $transactions = $transactionModel->forWithdrawalRequest($request['id']);

        if ($transactions !== []) {
            return $transactions;
        }

        if ($request['status'] === 'processed' && $request['payout_id'] !== null) {
            return $transactionModel->joinedForMerchant($request['merchant_id'])
                ->where('transactions.payout_id', $request['payout_id'])
                ->findAll();
        }

        if ($request['status'] === 'pending') {
            return $transactionModel->unpaidEarnedForMerchant($request['merchant_id']);
        }

        return [];
    }
}
