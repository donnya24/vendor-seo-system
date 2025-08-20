<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AreasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name' => 'Jakarta',
                'type' => 'city',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Jawa Barat',
                'type' => 'province',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Kalimantan Timur',
                'type' => 'province',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Sumatera Utara',
                'type' => 'province',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Sulawesi Selatan',
                'type' => 'province',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'Papua',
                'type' => 'region',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert batch
        $this->db->table('areas')->insertBatch($data);
    }
}
