<?php

namespace App\Models;

use CodeIgniter\Model;

class MerchantModel extends Model
{
    protected $table            = 'merchants';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'business_name',
        'contact_email',
        'contact_phone',
        'status',
        'api_key',
        'api_secret_encrypted',
        'subscription_plan',
        'subscription_expiry',
        'bank_account_details',
        'mpaisa_wallet_id',
        'mycash_wallet_id',
    ];

    public function findByApiKey(string $apiKey): ?array
    {
        return $this->where('api_key', $apiKey)->first();
    }

    public function isSubscriptionActive(array $merchant): bool
    {
        if ($merchant['status'] !== 'active') {
            return false;
        }

        if (empty($merchant['subscription_expiry'])) {
            return false;
        }

        return strtotime($merchant['subscription_expiry']) > time();
    }
}
