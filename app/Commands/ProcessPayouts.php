<?php

namespace App\Commands;

use App\Libraries\PaymentGateway\Services\PayoutService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ProcessPayouts extends BaseCommand
{
    protected $group       = 'Payments';
    protected $name        = 'payouts:process';
    protected $description = 'Batch each merchant\'s settled transactions into a payout.';

    public function run(array $params)
    {
        $summary = (new PayoutService())->processPayouts();

        if ($summary === []) {
            CLI::write('No merchants had settled, unpaid transactions.', 'yellow');

            return;
        }

        foreach ($summary as $payout) {
            CLI::write(sprintf(
                'Merchant #%d: payout #%d, net %s, %d transaction(s).',
                $payout['merchant_id'],
                $payout['payout_id'],
                number_format($payout['net_amount'], 2),
                $payout['transaction_count']
            ), 'green');
        }
    }
}
