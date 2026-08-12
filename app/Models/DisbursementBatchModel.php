<?php

namespace App\Models;

use CodeIgniter\Model;

class DisbursementBatchModel extends Model
{
    protected $table            = 'disbursement_batches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useTimestamps    = true;

    protected $allowedFields = ['reference', 'total_amount', 'item_count'];
}
