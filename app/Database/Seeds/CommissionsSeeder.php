<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CommissionsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'vendor_id'    => 1, // sesuaikan dengan vendor_profiles.id yang ada
                'period_start' => '2025-08-01',
                'period_end'   => '2025-08-15',
                'earning'      => 800000.00,   // kolom baru: penghasilan
                'amount'       => 1200000.00,
                'status'       => 'unpaid',
                'proof'        => null,
                'paid_at'      => null,
                'created_at'   => date('Y-m-d H:i:s'),
                'updated_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert batch
        $this->db->table('commissions')->insertBatch($data);
    }
}
