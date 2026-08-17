<?php

namespace App\Libraries\PaymentGateway\Services;

use App\Libraries\NotificationService;
use App\Models\MerchantModel;
use App\Models\PayoutModel;
use App\Models\TransactionModel;
use App\Models\WithdrawalRequestModel;

/**
 * Batches each merchant's settled, unpaid transactions into a payout
 * (design doc section 4.4). In production this would trigger a real bank
 * transfer via FIJICLEAR or a mobile-wallet payout; here it just records
 * the payout as 'processed' immediately so the reconciliation flow is
 * visible without a banking integration.
 */
class PayoutService
{
    protected TransactionModel $transactions;
    protected PayoutModel $payouts;
    protected WithdrawalRequestModel $withdrawalRequests;
    protected MerchantModel $merchants;

    public function __construct(
        ?TransactionModel $transactions = null,
        ?PayoutModel $payouts = null,
        ?WithdrawalRequestModel $withdrawalRequests = null,
        ?MerchantModel $merchants = null
    ) {
        $this->transactions       = $transactions ?? model(TransactionModel::class);
        $this->payouts            = $payouts ?? model(PayoutModel::class);
        $this->withdrawalRequests = $withdrawalRequests ?? model(WithdrawalRequestModel::class);
        $this->merchants          = $merchants ?? model(MerchantModel::class);
    }

    /**
     * @return list<array{merchant_id: int, payout_id: int, net_amount: float, transaction_count: int}>
     */
    public function processPayouts(): array
    {
        $summary = [];

        foreach ($this->transactions->merchantIdsWithUnpaidSettlement() as $merchantId) {
            $result = $this->processForMerchant($merchantId);

            if ($result !== null) {
                $summary[] = $result;
            }
        }

        return $summary;
    }

    /**
     * Batches a single merchant's settled, unpaid transactions into a payout.
     *
     * @return array{merchant_id: int, payout_id: int, net_amount: float, transaction_count: int}|null
     */
    public function processForMerchant(int $merchantId): ?array
    {
        $result = $this->bundle($merchantId, $this->transactions->unpaidSettledForMerchant($merchantId));

        if ($result !== null) {
            $this->resolveFullyCoveredWithdrawal($merchantId, $result['payout_id']);
        }

        return $result;
    }

    /**
     * Bundles a specific, already-settled set of transactions into a
     * payout — used by the withdrawal-approval flow, which must pay out
     * only the transactions snapshotted against that request, not every
     * settled-unpaid transaction the merchant happens to have by then.
     *
     * @param list<int> $transactionIds
     *
     * @return array{merchant_id: int, payout_id: int, net_amount: float, transaction_count: int}|null
     */
    public function processTransactions(int $merchantId, array $transactionIds): ?array
    {
        if ($transactionIds === []) {
            return null;
        }

        $transactions = $this->transactions
            ->whereIn('id', $transactionIds)
            ->where('status', 'settled')
            ->where('payout_id', null)
            ->findAll();

        return $this->bundle($merchantId, $transactions);
    }

    /**
     * @return array{merchant_id: int, payout_id: int, net_amount: float, transaction_count: int}|null
     */
    protected function bundle(int $merchantId, array $transactions): ?array
    {
        if ($transactions === []) {
            return null;
        }

        $totalAmount = array_sum(array_column($transactions, 'amount'));
        $totalFees   = array_sum(array_column($transactions, 'fee_amount'));
        $netAmount   = $totalAmount - $totalFees;

        $payoutId = $this->payouts->insert([
            'merchant_id'  => $merchantId,
            'total_amount' => $totalAmount,
            'fee_amount'   => $totalFees,
            'net_amount'   => $netAmount,
            'method'       => 'bank_transfer',
            'status'       => 'processed',
            'payout_date'  => date('Y-m-d H:i:s'),
        ], true);

        foreach ($transactions as $transaction) {
            $this->transactions->update($transaction['id'], ['payout_id' => $payoutId]);
        }

        return [
            'merchant_id'       => $merchantId,
            'payout_id'         => $payoutId,
            'net_amount'        => $netAmount,
            'transaction_count' => count($transactions),
        ];
    }

    /**
     * A bulk payout run (the admin "Run Payout Batch" button, or the
     * scheduled ProcessPayouts command) has no visibility into a merchant's
     * pending withdrawal request — it just bundles whatever settled/unpaid
     * transactions it finds. If that happens to include every transaction
     * snapshotted against a pending request, resolve that request as
     * processed now, using this same payout. Otherwise the admin's later
     * explicit approve() would find those transactions already paid out
     * (payout_id no longer null) and wrongly auto-reject the request for
     * having nothing left to process.
     */
    protected function resolveFullyCoveredWithdrawal(int $merchantId, int $payoutId): void
    {
        $pending = $this->withdrawalRequests->pendingForMerchant($merchantId);

        if ($pending === null) {
            return;
        }

        $snapshotted = $this->transactions->forWithdrawalRequest($pending['id']);

        if ($snapshotted === []) {
            return;
        }

        foreach ($snapshotted as $transaction) {
            if ($transaction['payout_id'] === null) {
                return;
            }
        }

        $this->withdrawalRequests->update($pending['id'], [
            'status'    => 'processed',
            'payout_id' => $payoutId,
        ]);

        $merchant = $this->merchants->find($merchantId);

        if ($merchant !== null) {
            (new NotificationService())->sendWithdrawalProcessing($merchant, $pending);
        }
    }
}
