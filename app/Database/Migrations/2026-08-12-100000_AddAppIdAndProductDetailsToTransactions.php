<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Transactions move from merchant-level ownership to per-application
 * ownership — each app a merchant registers can take its own payments, and
 * we need to know which one a given transaction came from. app_id is added
 * nullable here; the follow-up migration backfills it and drops merchant_id
 * once every row has one.
 *
 * Also adds the product/service fields the merchant enters on the demo
 * checkout card: what's being sold, how much of it, and any other detail.
 */
class AddAppIdAndProductDetailsToTransactions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('transactions', [
            'app_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'merchant_id'],
        ]);

        $this->forge->addKey('app_id');
        $this->forge->addForeignKey('app_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('transactions');

        $this->forge->addColumn('transactions', [
            'product_name' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true, 'after' => 'metadata'],
            'quantity' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'default' => 1, 'after' => 'product_name'],
            'unit_of_measure' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true, 'after' => 'quantity'],
            'unit_price' => ['type' => 'DECIMAL', 'constraint' => '12,2', 'null' => true, 'after' => 'unit_of_measure'],
            'product_description' => ['type' => 'TEXT', 'null' => true, 'after' => 'unit_price'],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('transactions', ['product_name', 'quantity', 'unit_of_measure', 'unit_price', 'product_description']);

        $this->db->query('ALTER TABLE transactions DROP FOREIGN KEY transactions_app_id_foreign');
        $this->forge->dropColumn('transactions', 'app_id');
    }
}
