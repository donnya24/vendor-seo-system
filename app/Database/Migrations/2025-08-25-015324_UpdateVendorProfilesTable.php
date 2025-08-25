<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCommissionRateToVendorProfiles extends Migration
{
    public function up()
    {
        // Tambah kolom commission_rate (0–100, persen)
        $fields = [
            'commission_rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '5,2',   // contoh: 12.50
                'null'       => true,    // biarkan null jika belum diset
                'default'    => null,
            ],
        ];

        $this->forge->addColumn('vendor_profiles', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('vendor_profiles', 'commission_rate');
    }
}
