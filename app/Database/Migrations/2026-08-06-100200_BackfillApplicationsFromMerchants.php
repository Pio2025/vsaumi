<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Data migration: gives every existing merchant a first "application" row
 * carrying their current api_key/api_secret_encrypted verbatim (so existing
 * checkout links and X-Api-Key values keep working unchanged), named after
 * their business, then repoints their subscription history at it.
 *
 * Idempotent: skips merchants that already have an application row, so a
 * retry after a partial failure (e.g. the NOT NULL step below) doesn't
 * insert duplicates.
 */
class BackfillApplicationsFromMerchants extends Migration
{
    public function up()
    {
        $now = date('Y-m-d H:i:s');

        $merchants = $this->db->table('merchants')->get()->getResultArray();

        foreach ($merchants as $merchant) {
            $alreadyBackfilled = $this->db->table('applications')
                ->where('merchant_id', $merchant['id'])
                ->countAllResults();

            if ($alreadyBackfilled > 0) {
                continue;
            }

            $this->db->table('applications')->insert([
                'merchant_id'           => $merchant['id'],
                'name'                  => $merchant['business_name'],
                'website_url'           => null,
                'status'                => 'active',
                'api_key'               => $merchant['api_key'],
                'api_secret_encrypted'  => $merchant['api_secret_encrypted'],
                'created_at'            => $merchant['created_at'] ?? $now,
                'updated_at'            => $now,
            ]);
        }

        $this->db->query('
            UPDATE subscriptions s
            JOIN applications a ON a.merchant_id = s.merchant_id
            SET s.application_id = a.id
            WHERE s.application_id IS NULL
        ');

        $this->forge->dropForeignKey('subscriptions', 'subscriptions_application_id_foreign');
        $this->forge->modifyColumn('subscriptions', [
            'application_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
        ]);
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('subscriptions');
    }

    public function down()
    {
        $this->forge->dropForeignKey('subscriptions', 'subscriptions_application_id_foreign');
        $this->forge->modifyColumn('subscriptions', [
            'application_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
        ]);
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('subscriptions');

        $this->db->table('applications')->emptyTable();
    }
}
