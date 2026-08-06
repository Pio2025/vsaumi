<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PayoutProviderSeeder extends Seeder
{
    public function run()
    {
        $providers = [
            ['type' => 'bank', 'name' => 'ANZ Bank (Fiji)', 'bsb_code' => '010-999'],
            ['type' => 'bank', 'name' => 'Bank of South Pacific (BSP)', 'bsb_code' => '020-999'],
            ['type' => 'bank', 'name' => 'Bred Bank (Fiji)', 'bsb_code' => '030-999'],
            ['type' => 'bank', 'name' => 'Bank of Baroda (Fiji)', 'bsb_code' => '040-999'],
            ['type' => 'bank', 'name' => 'HFC Bank', 'bsb_code' => '050-999'],
            ['type' => 'digital_wallet', 'name' => 'Vodafone M-PAiSA', 'bsb_code' => null],
            ['type' => 'digital_wallet', 'name' => 'Digicel MyCash', 'bsb_code' => null],
        ];

        foreach ($providers as $provider) {
            $existing = $this->db->table('payout_providers')
                ->where('type', $provider['type'])
                ->where('name', $provider['name'])
                ->get()
                ->getFirstRow();

            if ($existing) {
                continue;
            }

            $this->db->table('payout_providers')->insert([
                'type'       => $provider['type'],
                'name'       => $provider['name'],
                'bsb_code'   => $provider['bsb_code'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
