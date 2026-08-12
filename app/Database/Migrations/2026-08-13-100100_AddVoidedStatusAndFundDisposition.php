<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * 'voided' represents a transaction whose earned funds were forfeited by
 * an admin rejecting the withdrawal request that snapshotted it — it's
 * deliberately excluded from every balance/settlement/payout query
 * (they all whitelist specific statuses rather than exclude one), so no
 * other model code needs to change.
 */
class AddVoidedStatusAndFundDisposition extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('transactions', [
            'status' => [
                'name'       => 'status',
                'type'       => 'ENUM',
                'constraint' => ['pending', 'authorized', 'captured', 'settled', 'failed', 'voided'],
                'default'    => 'pending',
            ],
        ]);

        $this->forge->addColumn('withdrawal_requests', [
            'fund_disposition' => [
                'type'       => 'ENUM',
                'constraint' => ['returned', 'forfeited'],
                'null'       => true,
                'after'      => 'admin_note',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('withdrawal_requests', 'fund_disposition');

        $this->forge->modifyColumn('transactions', [
            'status' => [
                'name'       => 'status',
                'type'       => 'ENUM',
                'constraint' => ['pending', 'authorized', 'captured', 'settled', 'failed'],
                'default'    => 'pending',
            ],
        ]);
    }
}
