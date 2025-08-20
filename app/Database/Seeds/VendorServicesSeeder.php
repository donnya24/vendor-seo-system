<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VendorServicesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'vendor_id'  => 1, // Sesuaikan dengan id di tabel vendor_profiles
                'service_id' => 1, // Sesuaikan dengan id di tabel services
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'service_id' => 2,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'service_id' => 3,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'service_id' => 4,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'service_id' => 5,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert batch data
        $this->db->table('vendor_services')->insertBatch($data);
    }
}
