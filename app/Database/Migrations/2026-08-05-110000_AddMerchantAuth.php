<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMerchantAuth extends Migration
{
    public function up()
    {
        $this->forge->addColumn('merchants', [
            'password_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'after' => 'contact_phone'],
        ]);

        $this->forge->modifyColumn('merchants', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'approved', 'active', 'suspended'],
                'default'    => 'pending',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('merchants', 'password_hash');

        $this->forge->modifyColumn('merchants', [
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'active', 'suspended'],
                'default'    => 'pending',
            ],
        ]);
    }
}
