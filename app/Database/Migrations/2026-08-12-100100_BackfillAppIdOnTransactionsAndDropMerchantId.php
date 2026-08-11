<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/**
 * Data migration: points every existing transaction at its merchant's
 * first (lowest-id) application — same "first application" convention as
 * BackfillApplicationsFromMerchants — then drops the now-redundant
 * merchant_id column. Ownership of a transaction becomes transitive via
 * app_id -> applications.merchant_id from here on.
 *
 * Idempotent: the WHERE app_id IS NULL guard means a retry after a partial
 * failure doesn't re-run the backfill on already-migrated rows.
 */
class BackfillAppIdOnTransactionsAndDropMerchantId extends Migration
{
    public function up()
    {
        $this->db->query('
            UPDATE transactions t
            JOIN applications a ON a.merchant_id = t.merchant_id
            SET t.app_id = a.id
            WHERE t.app_id IS NULL
            AND a.id = (SELECT MIN(a2.id) FROM applications a2 WHERE a2.merchant_id = t.merchant_id)
        ');

        $stillNull = $this->db->table('transactions')->where('app_id', null)->countAllResults();

        if ($stillNull > 0) {
            throw new RuntimeException("{$stillNull} transaction(s) have no application to backfill app_id from — every merchant needs at least one application before this migration can run.");
        }

        $this->forge->dropForeignKey('transactions', 'transactions_app_id_foreign');
        $this->forge->modifyColumn('transactions', [
            'app_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addForeignKey('app_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('transactions');

        $this->db->query('ALTER TABLE transactions DROP FOREIGN KEY transactions_merchant_id_foreign');
        $this->forge->dropColumn('transactions', 'merchant_id');
    }

    public function down()
    {
        $this->forge->addColumn('transactions', [
            'merchant_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'id'],
        ]);

        $this->db->query('
            UPDATE transactions t
            JOIN applications a ON a.id = t.app_id
            SET t.merchant_id = a.merchant_id
        ');

        $this->forge->modifyColumn('transactions', [
            'merchant_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addKey('merchant_id');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', '', 'CASCADE');
        $this->forge->processIndexes('transactions');

        $this->forge->dropForeignKey('transactions', 'transactions_app_id_foreign');
        $this->forge->modifyColumn('transactions', [
            'app_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addForeignKey('app_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('transactions');
    }
}
