<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Splits subscription history out of `merchants` into its own auditable
 * entity: every simulated subscription payment gets its own row instead of
 * overwriting a single pair of columns, so a merchant's plan history (and
 * what they were charged each time) survives renewals and plan changes.
 */
class CreateSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'merchant_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'plan'        => ['type' => 'VARCHAR', 'constraint' => 50],
            'amount'      => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'status'      => ['type' => 'ENUM', 'constraint' => ['active', 'expired', 'canceled'], 'default' => 'active'],
            'started_at'  => ['type' => 'DATETIME'],
            'expires_at'  => ['type' => 'DATETIME'],
            'created_at'  => ['type' => 'DATETIME', 'null' => true],
            'updated_at'  => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('merchant_id');
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('subscriptions', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('subscriptions');
    }
}
