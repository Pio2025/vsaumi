<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Subscriptions move from merchant-level billing history to per-application
 * billing history — each app a merchant registers can carry its own plan.
 * merchant_id is kept (not dropped) for cheap merchant-wide rollups.
 */
class AddApplicationIdToSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addColumn('subscriptions', [
            'application_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true, 'after' => 'merchant_id'],
        ]);

        $this->forge->addKey('application_id');
        $this->forge->addForeignKey('application_id', 'applications', 'id', 'CASCADE', 'CASCADE');
        $this->forge->processIndexes('subscriptions');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE subscriptions DROP FOREIGN KEY subscriptions_application_id_foreign');
        $this->forge->dropColumn('subscriptions', 'application_id');
    }
}
