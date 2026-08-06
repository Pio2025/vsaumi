<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMerchantPayoutAccounts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                 => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'merchant_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'payout_provider_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'account_number'     => ['type' => 'VARCHAR', 'constraint' => 50],
            'account_name'       => ['type' => 'VARCHAR', 'constraint' => 150],
            'account_type'       => ['type' => 'ENUM', 'constraint' => ['savings', 'checking'], 'null' => true],
            'created_at'         => ['type' => 'DATETIME', 'null' => true],
            'updated_at'         => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('merchant_id');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('payout_provider_id', 'payout_providers', 'id', '', 'RESTRICT');
        $this->forge->createTable('merchant_payout_accounts', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('merchant_payout_accounts');
    }
}
