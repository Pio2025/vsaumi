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
        'business_address',
        'password_hash',
        'status',
    ];

    public function findByEmail(string $email): ?array
    {
        return $this->where('contact_email', $email)->first();
    }

    public function verifyPassword(array $merchant, string $password): bool
    {
        return password_verify($password, $merchant['password_hash']);
    }

    public function isSubscriptionActive(array $merchant): bool
    {
        if ($merchant['status'] !== 'active') {
            return false;
        }

        return model(SubscriptionModel::class)->hasActiveFor($merchant['id']);
    }
}
