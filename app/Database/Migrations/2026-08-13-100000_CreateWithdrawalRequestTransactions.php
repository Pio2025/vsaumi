<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Snapshots which transactions back a withdrawal request at the moment
 * it's created, so the admin "View" detail and the approve/reject actions
 * later operate on exactly what was requested rather than whatever a
 * live re-query of the merchant's balance happens to return by then.
 */
class CreateWithdrawalRequestTransactions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'withdrawal_request_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'transaction_id'        => ['type' => 'BIGINT', 'unsigned' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['withdrawal_request_id', 'transaction_id']);
        $this->forge->addForeignKey('withdrawal_request_id', 'withdrawal_requests', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('transaction_id', 'transactions', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('withdrawal_request_transactions', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('withdrawal_request_transactions');
    }
}
