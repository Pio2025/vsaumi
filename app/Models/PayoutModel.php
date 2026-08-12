<?php

namespace App\Models;

use CodeIgniter\Model;

class PayoutModel extends Model
{
    protected $table            = 'payouts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'merchant_id',
        'total_amount',
        'fee_amount',
        'net_amount',
        'method',
        'status',
        'payout_date',
    ];

    /**
     * Processed payouts that haven't been picked up by a disbursement batch
     * yet, with the merchant's chosen payout account joined in so the
     * disbursement run can be grouped by payment mode (bank vs digital
     * wallet). A payout is "picked up" once it has a matching
     * disbursement_items row, checked via a left-join antijoin rather than a
     * status flag on payouts itself.
     */
    public function eligibleForDisbursement(): array
    {
        return $this->select('payouts.*, merchants.business_name, merchants.contact_email,
                merchant_payout_accounts.account_number, merchant_payout_accounts.account_name,
                merchant_payout_accounts.account_type,
                payout_providers.type as provider_type, payout_providers.name as provider_name,
                payout_providers.bsb_code as provider_bsb_code')
            ->join('merchants', 'merchants.id = payouts.merchant_id')
            ->join('merchant_payout_accounts', 'merchant_payout_accounts.merchant_id = payouts.merchant_id', 'left')
            ->join('payout_providers', 'payout_providers.id = merchant_payout_accounts.payout_provider_id', 'left')
            ->join('disbursement_items', 'disbursement_items.payout_id = payouts.id', 'left')
            ->where('payouts.status', 'processed')
            ->where('disbursement_items.id', null)
            ->orderBy('payout_providers.type', 'ASC')
            ->orderBy('merchants.business_name', 'ASC')
            ->findAll();
    }
}
