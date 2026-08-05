<?php

namespace App\Models;

use CodeIgniter\Model;

class ApplicationModel extends Model
{
    protected $table            = 'applications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'merchant_id',
        'name',
        'website_url',
        'status',
        'api_key',
        'api_secret_encrypted',
    ];

    public function findByApiKey(string $apiKey): ?array
    {
        return $this->where('api_key', $apiKey)->first();
    }

    public function allForMerchant(int $merchantId): array
    {
        return $this->where('merchant_id', $merchantId)->orderBy('id', 'DESC')->findAll();
    }

    public function findForMerchant(int $merchantId, int $id): ?array
    {
        return $this->where('merchant_id', $merchantId)->where('id', $id)->first();
    }

    public function isSubscriptionActive(array $application): bool
    {
        if ($application['status'] !== 'active') {
            return false;
        }

        return model(SubscriptionModel::class)->hasActiveForApplication($application['id']);
    }
}
