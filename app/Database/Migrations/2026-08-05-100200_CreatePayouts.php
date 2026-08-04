<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePayouts extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'           => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'merchant_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'total_amount' => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'fee_amount'   => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'net_amount'   => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'method'       => ['type' => 'ENUM', 'constraint' => ['bank_transfer', 'mpaisa', 'mycash'], 'default' => 'bank_transfer'],
            'status'       => ['type' => 'ENUM', 'constraint' => ['pending', 'processed', 'failed'], 'default' => 'pending'],
            'payout_date'  => ['type' => 'DATETIME', 'null' => true],
            'created_at'   => ['type' => 'DATETIME', 'null' => true],
            'updated_at'   => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('merchant_id');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', '', 'CASCADE');
        $this->forge->createTable('payouts', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('payouts');
    }
}
