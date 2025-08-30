<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LeadsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'vendor_id'             => 1,
                'tanggal'               => date('Y-m-d'),
                'jumlah_leads_masuk'    => 12,
                'jumlah_leads_diproses' => 8,
                'jumlah_leads_ditolak'  => 2,
                'jumlah_leads_closing'  => 2,
                'service_id'            => 1,
                'reported_by_vendor'    => 1,
                'assigned_at'           => date('Y-m-d H:i:s'),
                'updated_at'            => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('leads')->insertBatch($data);
    }
}
