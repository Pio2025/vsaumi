<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayoutProviders extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'         => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'type'       => ['type' => 'ENUM', 'constraint' => ['bank', 'digital_wallet']],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 100],
            'bsb_code'   => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['type', 'name']);
        $this->forge->createTable('payout_providers', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('payout_providers');
    }
}
