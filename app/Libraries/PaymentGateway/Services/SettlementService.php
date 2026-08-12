<?php

namespace App\Libraries\PaymentGateway\Services;

use App\Models\TransactionModel;
use Config\Payment;

/**
 * Simulates the acquirer/provider's settlement batching step (design doc
 * section 3.3.1): captured funds are periodically swept into a "settled"
 * state before they're eligible for payout to a merchant. In production
 * this state change would arrive via provider webhook/reconciliation
 * reports on their own schedule (e.g. T+1/T+2); here it's a batch you run
 * on demand so the workflow is visible end to end without waiting.
 */
class SettlementService
{
    protected TransactionModel $transactions;
    protected Payment $config;

    public function __construct(?TransactionModel $transactions = null, ?Payment $config = null)
    {
        $this->transactions = $transactions ?? model(TransactionModel::class);
        $this->config        = $config ?? config(Payment::class);
    }

    /**
     * Move every 'captured' transaction to 'settled', assessing the
     * platform fee at settlement time.
     *
     * @return array{settled_count: int, total_amount: float, total_fees: float}
     */
    public function runBatch(): array
    {
        return $this->settle($this->transactions->where('status', 'captured')->findAll());
    }

    /**
     * Settle a single merchant's captured transactions. Called when an
     * admin approves a withdrawal request, so the request's declared
     * balance — which includes not-yet-settled captured amounts — is
     * trued up (fee assessed, status flipped) before PayoutService bundles
     * it into an actual payout.
     *
     * @return array{settled_count: int, total_amount: float, total_fees: float}
     */
    public function runBatchForMerchant(int $merchantId): array
    {
        return $this->settle($this->transactions->joinedForMerchant($merchantId)->where('transactions.status', 'captured')->findAll());
    }

    /**
     * Settle an explicit, already-fetched list of captured transactions —
     * used by the withdrawal-approval flow, which must settle only the
     * transactions snapshotted against that specific request, not every
     * captured transaction the merchant happens to have by approval time.
     *
     * @param list<array<string, mixed>> $transactions
     *
     * @return array{settled_count: int, total_amount: float, total_fees: float}
     */
    public function settleTransactions(array $transactions): array
    {
        return $this->settle($transactions);
    }

    /**
     * @param list<array<string, mixed>> $captured
     *
     * @return array{settled_count: int, total_amount: float, total_fees: float}
     */
    protected function settle(array $captured): array
    {
        $totalAmount = 0.0;
        $totalFees   = 0.0;

        foreach ($captured as $transaction) {
            $fee = round((float) $transaction['amount'] * $this->config->platformFeeRate, 2);

            $this->transactions->update($transaction['id'], [
                'status'     => 'settled',
                'fee_amount' => $fee,
            ]);

            $totalAmount += (float) $transaction['amount'];
            $totalFees   += $fee;
        }

        return [
            'settled_count' => count($captured),
            'total_amount'  => $totalAmount,
            'total_fees'    => $totalFees,
        ];
    }
}
