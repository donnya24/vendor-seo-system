<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'name'         => 'SEO Optimization',
                'service_type' => 'digital_marketing',
                'description'  => 'Optimasi mesin pencari untuk meningkatkan visibilitas website.',
                'status'       => 'active',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'name'         => 'Content Writing',
                'service_type' => 'digital_marketing',
                'description'  => 'Pembuatan artikel dan konten berkualitas untuk mendukung SEO.',
                'status'       => 'active',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'name'         => 'Backlink Building',
                'service_type' => 'digital_marketing',
                'description'  => 'Membangun tautan berkualitas dari website lain untuk meningkatkan otoritas.',
                'status'       => 'active',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'name'         => 'Local SEO',
                'service_type' => 'digital_marketing',
                'description'  => 'Optimasi SEO lokal agar bisnis mudah ditemukan di daerah tertentu.',
                'status'       => 'active',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
            [
                'name'         => 'Social Media Management',
                'service_type' => 'digital_marketing',
                'description'  => 'Manajemen akun sosial media untuk meningkatkan brand awareness.',
                'status'       => 'active',
                'created_at'   => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert data ke tabel services
        $this->db->table('services')->insertBatch($data);
    }
}
