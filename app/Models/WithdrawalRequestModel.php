<?php

namespace App\Models;

use CodeIgniter\Model;

class WithdrawalRequestModel extends Model
{
    protected $table            = 'withdrawal_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = [
        'merchant_id',
        'amount',
        'status',
        'payout_id',
        'admin_note',
        'fund_disposition',
    ];

    public function pendingForMerchant(int $merchantId): ?array
    {
        return $this->where('merchant_id', $merchantId)
            ->where('status', 'pending')
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function allForMerchant(int $merchantId): array
    {
        return $this->where('merchant_id', $merchantId)
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    /**
     * @return list<array>
     */
    public function allWithMerchantNames(): array
    {
        $requests      = $this->orderBy('id', 'DESC')->findAll();
        $merchantModel = model(MerchantModel::class);
        $merchantNames = [];

        foreach ($requests as &$request) {
            if (! isset($merchantNames[$request['merchant_id']])) {
                $merchant = $merchantModel->find($request['merchant_id']);
                $merchantNames[$request['merchant_id']] = $merchant['business_name'] ?? 'Unknown';
            }

            $request['merchant_name'] = $merchantNames[$request['merchant_id']];
        }
        unset($request);

        return $requests;
    }
}
