<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        $existing = $this->db->table('admins')->where('email', 'admin@vsaumi.com')->get()->getFirstRow();

        if ($existing) {
            return;
        }

        $this->db->table('admins')->insert([
            'name'          => 'VSaumi Admin',
            'email'         => 'admin@vsaumi.com',
            'password_hash' => password_hash('Admin123', PASSWORD_DEFAULT),
            'created_at'    => date('Y-m-d H:i:s'),
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }
}
