<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LeadDistributionSeeder extends Seeder
{
    public function run()
    {
        // Pastikan ID ini ada di DB kamu:
        // - lead_id: contoh 1,2,3 (atau sesuaikan dengan leads yang sudah ada)
        // - vendor_id: contoh 1 (id di vendor_profiles)
        // - assigned_by: contoh 1 (admin) / 11 (seo team) / null
        $rows = [
            [
                'lead_id'     => 15,
                'vendor_id'   => 1,
                'assigned_by' => 11, // seo_team
                'status'      => 'new',
                'note'        => 'Lead baru dialokasikan ke vendor #1.',
                'assigned_at' => date('Y-m-d H:i:s', strtotime('-2 days 09:15')),
                'updated_at'  => null,
            ],
        ];

        $this->db->table('lead_distribution')->insertBatch($rows);
    }
}
