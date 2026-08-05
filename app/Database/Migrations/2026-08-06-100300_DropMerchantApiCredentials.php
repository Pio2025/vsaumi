<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes the merchant-level api_key/api_secret_encrypted columns now that
 * every merchant has at least one application row carrying its own
 * credentials (see BackfillApplicationsFromMerchants). Ships last, after
 * every code path that used to read merchants.api_key has been cut over to
 * ApplicationModel — see the multi-application implementation plan.
 */
class DropMerchantApiCredentials extends Migration
{
    public function up()
    {
        $this->db->query('ALTER TABLE merchants DROP INDEX api_key');
        $this->forge->dropColumn('merchants', ['api_key', 'api_secret_encrypted']);
    }

    public function down()
    {
        $this->forge->addColumn('merchants', [
            'api_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
                'after'      => 'status',
            ],
            'api_secret_encrypted' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'api_key',
            ],
        ]);

        $this->db->query('
            UPDATE merchants m
            JOIN (
                SELECT a1.merchant_id, a1.api_key, a1.api_secret_encrypted
                FROM applications a1
                WHERE a1.id = (SELECT MIN(a2.id) FROM applications a2 WHERE a2.merchant_id = a1.merchant_id)
            ) first_app ON first_app.merchant_id = m.id
            SET m.api_key = first_app.api_key,
                m.api_secret_encrypted = first_app.api_secret_encrypted
        ');

        $this->forge->addUniqueKey('api_key');
        $this->forge->processIndexes('merchants');
    }
}
