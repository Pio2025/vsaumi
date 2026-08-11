<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWithdrawalRequests extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'merchant_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'amount'       => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'processed', 'rejected'], 'default' => 'pending'],
            'payout_id'    => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'admin_note'   => ['type' => 'TEXT', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('merchant_id');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', '', 'CASCADE');
        $this->forge->addForeignKey('payout_id', 'payouts', 'id', '', 'SET NULL');
        $this->forge->createTable('withdrawal_requests', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('withdrawal_requests');
    }
}
