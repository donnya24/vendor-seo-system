<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run()
    {
        // Sesuaikan ID berikut dengan data nyata di DB kamu:
        // user_id: 1=admin, 2=vendor, 11=seo_team (contoh)
        // vendor_id: 1 (contoh vendor profile)
        $rows = [
            [
                'user_id'     => 2,
                'vendor_id'   => 1,
                'module'      => 'auth',
                'action'      => 'login',
                'description' => 'Vendor melakukan login dari halaman /login',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Mozilla/5.0',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-2 days 08:15')),
            ],
            [
                'user_id'     => 2,
                'vendor_id'   => 1,
                'module'      => 'products',
                'action'      => 'create',
                'description' => 'Menambahkan produk "Label Woven Premium"',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Mozilla/5.0',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-2 days 08:25')),
            ],
            [
                'user_id'     => 2,
                'vendor_id'   => 1,
                'module'      => 'leads',
                'action'      => 'view',
                'description' => 'Melihat detail lead #1009',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Mozilla/5.0',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day 10:02')),
            ],
            [
                'user_id'     => 2,
                'vendor_id'   => 1,
                'module'      => 'auth',
                'action'      => 'logout',
                'description' => 'Vendor keluar dari sistem',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Mozilla/5.0',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day 10:35')),
            ],
            [
                'user_id'     => 11,
                'vendor_id'   => 1,
                'module'      => 'seo',
                'action'      => 'update',
                'description' => 'SEO team memperbarui status request optimasi #12 menjadi "Dalam Proses".',
                'ip_address'  => '127.0.0.1',
                'user_agent'  => 'Mozilla/5.0',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-3 hours')),
            ],
        ];

        $this->db->table('activity_logs')->insertBatch($rows);
    }
}
