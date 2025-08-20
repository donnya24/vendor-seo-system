<?php
// app/Database/Seeds/VendorAreasSeeder.php
namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VendorAreasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'vendor_id'  => 1,  // contoh vendor ID
                'area_id'    => 1,  // contoh area ID (sesuaikan dengan tabel areas)
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'area_id'    => 2,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'area_id'    => 3,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'area_id'    => 4,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'area_id'    => 5,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'  => 1,
                'area_id'    => 6,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert batch
        $this->db->table('vendor_areas')->insertBatch($data);
    }
}
