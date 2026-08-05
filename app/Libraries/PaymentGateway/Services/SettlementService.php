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
        $captured = $this->transactions->where('status', 'captured')->findAll();

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
