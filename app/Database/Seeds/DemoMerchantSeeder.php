<?php

namespace App\Database\Seeds;

use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use CodeIgniter\Database\Seeder;

/**
 * Seeds an already-active, already-subscribed merchant ("Vinaka Store") with
 * two applications — an online storefront and a POS kiosk — so the homepage
 * can link straight into a working demo checkout and the dashboard has more
 * than one application to show. The full signup -> admin approval ->
 * subscribe flow is still there and unaffected — this just gives visitors
 * something to click immediately.
 */
class DemoMerchantSeeder extends Seeder
{
    public function run()
    {
        $existing = $this->db->table('merchants')->where('contact_email', 'demo@vsaumi.fj')->get()->getFirstRow();

        if ($existing) {
            return;
        }

        $this->db->table('merchants')->insert([
            'business_name'    => 'Vinaka Store',
            'contact_email'    => 'demo@vsaumi.fj',
            'contact_phone'    => '9791234567',
            'business_address' => '15 Renwick Road, Suva, Fiji',
            'password_hash'    => password_hash('demo1234', PASSWORD_DEFAULT),
            'status'           => 'active',
            'created_at'       => date('Y-m-d H:i:s'),
            'updated_at'       => date('Y-m-d H:i:s'),
        ]);

        $merchantId = $this->db->insertID();

        $bank = $this->db->table('payout_providers')->where('type', 'bank')->orderBy('id', 'ASC')->get()->getFirstRow('array');

        if ($bank) {
            $this->db->table('merchant_payout_accounts')->insert([
                'merchant_id'        => $merchantId,
                'payout_provider_id' => $bank['id'],
                'account_number'     => '9988776655',
                'account_name'       => 'Vinaka Store',
                'account_type'       => 'savings',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);
        }

        $applications = [
            ['name' => 'Vinaka Store — Online', 'website_url' => 'https://vinaka.fj', 'plan' => 'starter', 'amount' => 29],
            ['name' => 'Vinaka Store — POS Kiosk', 'website_url' => null, 'plan' => 'growth', 'amount' => 79],
        ];

        foreach ($applications as $app) {
            $this->db->table('applications')->insert([
                'merchant_id'           => $merchantId,
                'name'                  => $app['name'],
                'website_url'           => $app['website_url'],
                'status'                => 'active',
                'api_key'               => 'demo_' . bin2hex(random_bytes(16)),
                'api_secret_encrypted'  => SecurityHelper::encryptSecret(SecurityHelper::generateSecret()),
                'created_at'            => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ]);

            $applicationId = $this->db->insertID();

            $this->db->table('subscriptions')->insert([
                'merchant_id'    => $merchantId,
                'application_id' => $applicationId,
                'plan'           => $app['plan'],
                'amount'         => $app['amount'],
                'status'         => 'active',
                'started_at'     => date('Y-m-d H:i:s'),
                'expires_at'     => date('Y-m-d H:i:s', strtotime('+1 year')),
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
