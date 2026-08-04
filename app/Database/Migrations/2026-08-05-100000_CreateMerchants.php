<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMerchants extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'                    => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'business_name'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'contact_email'         => ['type' => 'VARCHAR', 'constraint' => 150],
            'contact_phone'         => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'status'                => ['type' => 'ENUM', 'constraint' => ['pending', 'active', 'suspended'], 'default' => 'pending'],
            'api_key'               => ['type' => 'VARCHAR', 'constraint' => 64],
            'api_secret_encrypted'  => ['type' => 'TEXT'],
            'subscription_plan'     => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'subscription_expiry'   => ['type' => 'DATETIME', 'null' => true],
            'bank_account_details'  => ['type' => 'TEXT', 'null' => true],
            'mpaisa_wallet_id'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'mycash_wallet_id'      => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'created_at'            => ['type' => 'DATETIME', 'null' => true],
            'updated_at'            => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('api_key');
        $this->forge->addUniqueKey('contact_email');
        $this->forge->createTable('merchants', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('merchants');
    }
}
