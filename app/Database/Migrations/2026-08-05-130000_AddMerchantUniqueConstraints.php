<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMerchantUniqueConstraints extends Migration
{
    public function up()
    {
        $this->forge->addUniqueKey('business_name');
        $this->forge->addUniqueKey('contact_phone');
        $this->forge->processIndexes('merchants');
    }

    public function down()
    {
        $this->db->query('ALTER TABLE merchants DROP INDEX business_name');
        $this->db->query('ALTER TABLE merchants DROP INDEX contact_phone');
    }
}
