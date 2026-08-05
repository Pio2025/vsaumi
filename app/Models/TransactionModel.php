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
        'merchant_id',
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
    ];

    public function findByReference(string $reference): ?array
    {
        return $this->where('reference', $reference)->first();
    }

    /**
     * Transactions that are settled but not yet attached to a payout batch.
     */
    public function unpaidSettledForMerchant(int $merchantId): array
    {
        return $this->where('merchant_id', $merchantId)
            ->where('status', 'settled')
            ->where('payout_id', null)
            ->findAll();
    }

    /**
     * Distinct merchant IDs that currently have settled, unpaid transactions.
     *
     * @return list<int>
     */
    public function merchantIdsWithUnpaidSettlement(): array
    {
        $rows = $this->select('merchant_id')
            ->where('status', 'settled')
            ->where('payout_id', null)
            ->groupBy('merchant_id')
            ->findAll();

        return array_map(static fn (array $row) => (int) $row['merchant_id'], $rows);
    }
}
