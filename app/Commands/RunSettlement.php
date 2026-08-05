<?php

namespace App\Commands;

use App\Libraries\PaymentGateway\Services\SettlementService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RunSettlement extends BaseCommand
{
    protected $group       = 'Payments';
    protected $name        = 'settlement:run';
    protected $description = 'Move captured transactions to settled, simulating the acquirer settlement batch.';

    public function run(array $params)
    {
        $result = (new SettlementService())->runBatch();

        CLI::write("Settled {$result['settled_count']} transaction(s).", 'green');
        CLI::write('Total amount: ' . number_format($result['total_amount'], 2));
        CLI::write('Total fees:   ' . number_format($result['total_fees'], 2));
    }
}
