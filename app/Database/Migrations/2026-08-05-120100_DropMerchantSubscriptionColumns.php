<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropMerchantSubscriptionColumns extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('merchants', ['subscription_plan', 'subscription_expiry']);
    }

    public function down()
    {
        $this->forge->addColumn('merchants', [
            'subscription_plan'   => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true, 'after' => 'status'],
            'subscription_expiry' => ['type' => 'DATETIME', 'null' => true, 'after' => 'subscription_plan'],
        ]);
    }
}
