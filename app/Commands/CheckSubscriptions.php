<?php

namespace App\Commands;

use App\Models\MerchantModel;
use App\Models\SubscriptionModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Intended to run daily (design doc section 3.1). Windows/WAMP has no cron;
 * schedule this via Task Scheduler running `php spark subscriptions:check`,
 * or trigger it manually while testing.
 */
class CheckSubscriptions extends BaseCommand
{
    protected $group       = 'Payments';
    protected $name        = 'subscriptions:check';
    protected $description = 'Suspend merchants whose subscription has expired.';

    public function run(array $params)
    {
        $merchants     = model(MerchantModel::class);
        $subscriptions = model(SubscriptionModel::class);

        $expired = $subscriptions->where('status', 'active')
            ->where('expires_at <', date('Y-m-d H:i:s'))
            ->findAll();

        foreach ($expired as $subscription) {
            $subscriptions->update($subscription['id'], ['status' => 'expired']);

            $merchant = $merchants->find($subscription['merchant_id']);

            if ($merchant !== null && $merchant['status'] === 'active') {
                $merchants->update($merchant['id'], ['status' => 'suspended']);
                CLI::write("Suspended merchant #{$merchant['id']} ({$merchant['business_name']}) — subscription expired.", 'yellow');
            }
        }

        if ($expired === []) {
            CLI::write('No expired subscriptions found.', 'green');
        }
    }
}
