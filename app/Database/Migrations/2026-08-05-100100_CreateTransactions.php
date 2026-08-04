<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'merchant_id'     => ['type' => 'BIGINT', 'unsigned' => true],
            'reference'       => ['type' => 'VARCHAR', 'constraint' => 64],
            'customer_email'  => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'customer_msisdn' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'amount'          => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'currency'        => ['type' => 'VARCHAR', 'constraint' => 3, 'default' => 'FJD'],
            'payment_method'  => ['type' => 'ENUM', 'constraint' => ['visa', 'mastercard', 'mpaisa', 'mycash']],
            'status'          => ['type' => 'ENUM', 'constraint' => ['pending', 'authorized', 'captured', 'settled', 'failed'], 'default' => 'pending'],
            'psp_reference'   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'fee_amount'      => ['type' => 'DECIMAL', 'constraint' => '12,2', 'default' => 0],
            'metadata'        => ['type' => 'TEXT', 'null' => true],
            'payout_id'       => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'created_at'      => ['type' => 'DATETIME', 'null' => true],
            'updated_at'      => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('reference');
        $this->forge->addKey('merchant_id');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', '', 'CASCADE');
        $this->forge->createTable('transactions', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('transactions');
    }
}
