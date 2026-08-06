<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Removes the old free-text payout columns on merchants now that payout
 * details are captured structurally via payout_providers/merchant_payout_accounts.
 * These columns were never read or written anywhere in the codebase.
 */
class DropMerchantLegacyPayoutColumns extends Migration
{
    public function up()
    {
        $this->forge->dropColumn('merchants', ['bank_account_details', 'mpaisa_wallet_id', 'mycash_wallet_id']);
    }

    public function down()
    {
        $this->forge->addColumn('merchants', [
            'bank_account_details' => [
                'type'  => 'TEXT',
                'null'  => true,
                'after' => 'status',
            ],
            'mpaisa_wallet_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'bank_account_details',
            ],
            'mycash_wallet_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'mpaisa_wallet_id',
            ],
        ]);
    }
}
