<?php

namespace App\Models;

use CodeIgniter\Model;

class DisbursementItemModel extends Model
{
    protected $table            = 'disbursement_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'disbursement_batch_id',
        'payout_id',
        'merchant_id',
        'payment_type',
        'provider_name',
        'account_name',
        'account_number',
        'bsb_code',
        'amount',
        'created_at',
    ];

    /**
     * All items in a batch, with payout and merchant detail joined in —
     * used by the Disbursement Listing report, grouped by payment mode then
     * merchant so the pay clerk can work through the batch method by method.
     */
    public function forBatch(int $batchId): array
    {
        return $this->select('disbursement_items.*, payouts.payout_date, payouts.total_amount as payout_total_amount, payouts.fee_amount as payout_fee_amount, merchants.business_name, merchants.contact_email, merchants.contact_phone')
            ->join('payouts', 'payouts.id = disbursement_items.payout_id')
            ->join('merchants', 'merchants.id = disbursement_items.merchant_id')
            ->where('disbursement_items.disbursement_batch_id', $batchId)
            ->orderBy('disbursement_items.payment_type', 'ASC')
            ->orderBy('merchants.business_name', 'ASC')
            ->findAll();
    }
}
