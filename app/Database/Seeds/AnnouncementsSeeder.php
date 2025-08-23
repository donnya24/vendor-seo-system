<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementsSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $rows = [
            [
                'title'      => 'Pembaruan Sistem Dashboard',
                'body'       => 'Kami melakukan update performa & perbaikan bug kecil pada area vendor.',
                'audience'   => 'vendor',
                'publish_at' => $now,
                'expires_at' => null,
                'is_active'  => 1,
                'created_at' => $now,
            ],
            [
                'title'      => 'Maintenance Terjadwal',
                'body'       => 'Sistem akan maintenance hari Minggu pukul 00:00–02:00 WIB.',
                'audience'   => 'all',
                'publish_at' => date('Y-m-d H:i:s', strtotime('+1 day 00:00')),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+2 days 02:00')),
                'is_active'  => 1,
                'created_at' => $now,
            ],
            [
                'title'      => 'Panduan Optimasi Keyword Baru',
                'body'       => 'Tim SEO menambahkan SOP baru untuk on-page & internal link.',
                'audience'   => 'seo_team',
                'publish_at' => $now,
                'expires_at' => null,
                'is_active'  => 1,
                'created_at' => $now,
            ],
        ];

        $this->db->table('announcements')->insertBatch($rows);
    }
}
