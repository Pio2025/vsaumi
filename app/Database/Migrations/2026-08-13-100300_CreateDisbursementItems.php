<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDisbursementItems extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                     => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'disbursement_batch_id'  => ['type' => 'BIGINT', 'unsigned' => true],
            'payout_id'              => ['type' => 'BIGINT', 'unsigned' => true],
            'merchant_id'            => ['type' => 'BIGINT', 'unsigned' => true],
            'payment_type'           => ['type' => 'ENUM', 'constraint' => ['bank', 'digital_wallet']],
            'provider_name'          => ['type' => 'VARCHAR', 'constraint' => 100],
            'account_name'           => ['type' => 'VARCHAR', 'constraint' => 150],
            'account_number'         => ['type' => 'VARCHAR', 'constraint' => 50],
            'bsb_code'               => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'amount'                 => ['type' => 'DECIMAL', 'constraint' => '12,2'],
            'created_at'             => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('disbursement_batch_id');
        $this->forge->addUniqueKey('payout_id');
        $this->forge->addForeignKey('disbursement_batch_id', 'disbursement_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('payout_id', 'payouts', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('disbursement_items', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('disbursement_items');
    }
}
