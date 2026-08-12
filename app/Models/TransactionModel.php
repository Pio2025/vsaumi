<?php

namespace App\Models;

use CodeIgniter\Model;

class TransactionModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'app_id',
        'reference',
        'customer_email',
        'customer_msisdn',
        'amount',
        'currency',
        'payment_method',
        'status',
        'psp_reference',
        'fee_amount',
        'metadata',
        'payout_id',
        'product_name',
        'quantity',
        'unit_of_measure',
        'unit_price',
        'product_description',
    ];

    public function findByReference(string $reference): ?array
    {
        return $this->where('reference', $reference)->first();
    }

    /**
     * Transactions belong to an application; a merchant's transactions are
     * everything from any application it owns. Every merchant-scoped query
     * below joins through applications rather than filtering a merchant_id
     * column directly.
     */
    public function joinedForMerchant(int $merchantId): self
    {
        return $this->select('transactions.*')
            ->join('applications', 'applications.id = transactions.app_id')
            ->where('applications.merchant_id', $merchantId);
    }

    /**
     * Ownership-checked single lookup for the transaction detail view.
     */
    public function findForMerchant(int $id, int $merchantId): ?array
    {
        return $this->select('transactions.*, applications.name as application_name')
            ->join('applications', 'applications.id = transactions.app_id')
            ->where('transactions.id', $id)
            ->where('applications.merchant_id', $merchantId)
            ->first();
    }

    /**
     * Transactions that are settled but not yet attached to a payout batch.
     */
    public function unpaidSettledForMerchant(int $merchantId): array
    {
        return $this->joinedForMerchant($merchantId)
            ->where('transactions.status', 'settled')
            ->where('transactions.payout_id', null)
            ->findAll();
    }

    /**
     * Distinct merchant IDs that currently have settled, unpaid transactions.
     *
     * @return list<int>
     */
    public function merchantIdsWithUnpaidSettlement(): array
    {
        $rows = $this->select('applications.merchant_id')
            ->join('applications', 'applications.id = transactions.app_id')
            ->where('transactions.status', 'settled')
            ->where('transactions.payout_id', null)
            ->groupBy('applications.merchant_id')
            ->findAll();

        return array_map(static fn (array $row) => (int) $row['merchant_id'], $rows);
    }

    /**
     * Transactions the merchant has earned but hasn't been paid out for yet —
     * captured (fee not assessed till settlement) or already-settled, either
     * way not yet attached to a payout. This is what "Available balance" on
     * the dashboard shows; it's deliberately broader than
     * unpaidSettledForMerchant(), which only PayoutService may bundle into an
     * actual payout, since a captured transaction's fee isn't final until it
     * settles.
     */
    public function unpaidEarnedForMerchant(int $merchantId): array
    {
        return $this->joinedForMerchant($merchantId)
            ->whereIn('transactions.status', ['captured', 'settled'])
            ->where('transactions.payout_id', null)
            ->findAll();
    }

    /**
     * Net (fee-deducted where known) balance of earned transactions not yet
     * paid out.
     */
    public function availableBalanceForMerchant(int $merchantId): float
    {
        $transactions = $this->unpaidEarnedForMerchant($merchantId);

        return array_sum(array_column($transactions, 'amount')) - array_sum(array_column($transactions, 'fee_amount'));
    }

    /**
     * Snapshot the merchant's currently earned-unpaid transactions against a
     * withdrawal request at the moment it's created, so the admin "View"
     * detail and the approve/reject actions later act on exactly what was
     * requested rather than a live re-query that could drift if more sales
     * land before the admin acts on it.
     */
    public function snapshotForWithdrawalRequest(int $withdrawalRequestId, int $merchantId): void
    {
        $transactions = $this->unpaidEarnedForMerchant($merchantId);

        if ($transactions === []) {
            return;
        }

        $rows = array_map(static fn (array $transaction): array => [
            'withdrawal_request_id' => $withdrawalRequestId,
            'transaction_id'        => $transaction['id'],
            'created_at'            => date('Y-m-d H:i:s'),
        ], $transactions);

        $this->db->table('withdrawal_request_transactions')->insertBatch($rows);
    }

    /**
     * The exact transactions snapshotted against a withdrawal request —
     * what "View" shows, and what approve()/reject() act on, regardless of
     * any status changes to those transactions since.
     */
    public function forWithdrawalRequest(int $withdrawalRequestId): array
    {
        return $this->select('transactions.*')
            ->join('withdrawal_request_transactions', 'withdrawal_request_transactions.transaction_id = transactions.id')
            ->where('withdrawal_request_transactions.withdrawal_request_id', $withdrawalRequestId)
            ->findAll();
    }

    /**
     * Marks transactions as 'voided' — an admin forfeiting a rejected
     * withdrawal request's funds. Excluded from every balance/settlement/
     * payout query going forward, since those all whitelist specific
     * statuses rather than exclude one.
     */
    public function voidTransactions(array $transactionIds): int
    {
        foreach ($transactionIds as $id) {
            $this->update($id, ['status' => 'voided']);
        }

        return count($transactionIds);
    }

    /**
     * Gross lifetime revenue from all settled transactions, regardless of payout status.
     */
    public function totalRevenueForMerchant(int $merchantId): float
    {
        $row = $this->select('SUM(transactions.amount) as amount')
            ->join('applications', 'applications.id = transactions.app_id')
            ->where('applications.merchant_id', $merchantId)
            ->where('transactions.status', 'settled')
            ->first();

        return (float) ($row['amount'] ?? 0);
    }
}
