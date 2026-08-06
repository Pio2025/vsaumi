<?php

namespace App\Models;

use CodeIgniter\Model;

class PayoutProviderModel extends Model
{
    protected $table            = 'payout_providers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = ['type', 'name', 'bsb_code'];

    public function banks(): array
    {
        return $this->where('type', 'bank')->orderBy('name', 'ASC')->findAll();
    }

    public function digitalWallets(): array
    {
        return $this->where('type', 'digital_wallet')->orderBy('name', 'ASC')->findAll();
    }
}
