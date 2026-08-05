<?php

namespace App\Database\Seeds;

use App\Libraries\PaymentGateway\Helpers\SecurityHelper;
use CodeIgniter\Database\Seeder;

/**
 * Seeds an already-active, already-subscribed merchant ("Vinaka Store") so
 * the homepage can link straight into a working demo checkout. The full
 * signup -> admin approval -> subscribe flow is still there and unaffected
 * — this just gives visitors something to click immediately.
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
            'business_name'        => 'Vinaka Store',
            'contact_email'        => 'demo@vsaumi.fj',
            'contact_phone'        => '9791234567',
            'password_hash'        => password_hash('demo1234', PASSWORD_DEFAULT),
            'status'                => 'active',
            'api_key'               => 'demo_' . bin2hex(random_bytes(16)),
            'api_secret_encrypted'  => SecurityHelper::encryptSecret(SecurityHelper::generateSecret()),
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);

        $merchantId = $this->db->insertID();

        $this->db->table('subscriptions')->insert([
            'merchant_id' => $merchantId,
            'plan'        => 'starter',
            'amount'      => 29,
            'status'      => 'active',
            'started_at'  => date('Y-m-d H:i:s'),
            'expires_at'  => date('Y-m-d H:i:s', strtotime('+1 year')),
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
