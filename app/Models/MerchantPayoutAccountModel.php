<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantPayoutAccountModel extends Model
{
    protected $table            = 'merchant_payout_accounts';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = ['merchant_id', 'payout_provider_id', 'account_number', 'account_name', 'account_type'];

    public function hasPayoutInfo(int $merchantId): bool
    {
        return $this->where('merchant_id', $merchantId)->countAllResults() > 0;
    }

    public function forMerchant(int $merchantId): ?array
    {
        return $this->select('merchant_payout_accounts.*, payout_providers.name as provider_name, payout_providers.type as provider_type, payout_providers.bsb_code as provider_bsb_code')
            ->join('payout_providers', 'payout_providers.id = merchant_payout_accounts.payout_provider_id')
            ->where('merchant_id', $merchantId)
            ->first();
    }

    public function upsertForMerchant(int $merchantId, array $data): void
    {
        $existing = $this->where('merchant_id', $merchantId)->first();

        if ($existing !== null) {
            $this->update($existing['id'], $data);

            return;
        }

        $this->insert($data + ['merchant_id' => $merchantId]);
    }
}
