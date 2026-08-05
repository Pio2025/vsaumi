<?php

namespace App\Models;

use CodeIgniter\Model;

class SubscriptionModel extends Model
{
    protected $table            = 'subscriptions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'merchant_id',
        'plan',
        'amount',
        'status',
        'started_at',
        'expires_at',
    ];

    public function latestForMerchant(int $merchantId): ?array
    {
        return $this->where('merchant_id', $merchantId)->orderBy('id', 'DESC')->first();
    }

    public function allForMerchant(int $merchantId): array
    {
        return $this->where('merchant_id', $merchantId)->orderBy('id', 'DESC')->findAll();
    }

    public function hasActiveFor(int $merchantId): bool
    {
        $latest = $this->latestForMerchant($merchantId);

        if ($latest === null || $latest['status'] !== 'active') {
            return false;
        }

        return strtotime($latest['expires_at']) > time();
    }
}
