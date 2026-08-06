<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBusinessAddressToMerchants extends Migration
{
    public function up()
    {
        $this->forge->addColumn('merchants', [
            'business_address' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'contact_phone',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('merchants', 'business_address');
    }
}
