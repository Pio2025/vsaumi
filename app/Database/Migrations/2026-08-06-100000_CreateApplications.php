<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * An "application" is one app/website a merchant registers to call the
 * payment API — e.g. business "Metro Investment" registering an app called
 * "My Kava". Each application gets its own api_key/api_secret pair (mirrors
 * PayPal's Apps & Credentials model), while KYC/approval status stays on
 * the parent merchant.
 */
class CreateApplications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'merchant_id'           => ['type' => 'BIGINT', 'unsigned' => true],
            'name'                  => ['type' => 'VARCHAR', 'constraint' => 150],
            'website_url'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status'                => ['type' => 'ENUM', 'constraint' => ['active', 'suspended'], 'default' => 'active'],
            'api_key'               => ['type' => 'VARCHAR', 'constraint' => 64],
            'api_secret_encrypted'  => ['type' => 'TEXT'],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('merchant_id');
        $this->forge->addUniqueKey('api_key');
        $this->forge->addUniqueKey(['merchant_id', 'name']);
        $this->forge->addForeignKey('merchant_id', 'merchants', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('applications', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('applications');
    }
}
