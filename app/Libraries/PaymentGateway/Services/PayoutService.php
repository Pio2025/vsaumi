<?php

namespace App\Libraries\PaymentGateway\Services;

use App\Models\PayoutModel;
use App\Models\TransactionModel;

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

    public function __construct(?TransactionModel $transactions = null, ?PayoutModel $payouts = null)
    {
        $this->transactions = $transactions ?? model(TransactionModel::class);
        $this->payouts       = $payouts ?? model(PayoutModel::class);
    }

    /**
     * @return list<array{merchant_id: int, payout_id: int, net_amount: float, transaction_count: int}>
     */
    public function processPayouts(): array
    {
        $summary = [];

        foreach ($this->transactions->merchantIdsWithUnpaidSettlement() as $merchantId) {
            $transactions = $this->transactions->unpaidSettledForMerchant($merchantId);

            if ($transactions === []) {
                continue;
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

            $summary[] = [
                'merchant_id'       => $merchantId,
                'payout_id'         => $payoutId,
                'net_amount'        => $netAmount,
                'transaction_count' => count($transactions),
            ];
        }

        return $summary;
    }
}
