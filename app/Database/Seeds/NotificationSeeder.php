<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        // Contoh data: sesuaikan id user/vendor yang sudah ada di DB kamu
        // users: 1=admin, 2=vendor, 11=seoteam
        // vendor_profiles: 1 (Butik Cantika)
        $data = [
            [
                'user_id'    => 2,                // vendor user
                'vendor_id'  => 1,                // vendor profile id
                'type'       => 'lead',
                'title'      => 'Lead baru masuk',
                'message'    => '1 lead baru untuk "Label Woven Premium".',
                'is_read'    => 0,
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day 10:30')),
            ],
            [
                'user_id'    => 2,
                'vendor_id'  => 1,
                'type'       => 'commission',
                'title'      => 'Komisi periode 01–15 Agustus',
                'message'    => 'Komisi siap diproses untuk vendor Anda.',
                'is_read'    => 1,
                'read_at'    => date('Y-m-d H:i:s', strtotime('-20 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-21 hours')),
            ],
            [
                'user_id'    => null,             // broadcast ke semua
                'vendor_id'  => null,
                'type'       => 'announcement',
                'title'      => 'Pemeliharaan Sistem',
                'message'    => 'Sistem akan maintenance Jumat 23:00–01:00.',
                'is_read'    => 0,
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 hours')),
            ],
            [
                'user_id'    => 11,               // seo team
                'vendor_id'  => 1,
                'type'       => 'system',
                'title'      => 'Tiket Optimasi Update',
                'message'    => 'Request optimasi #12 status berubah: Dalam Proses.',
                'is_read'    => 0,
                'read_at'    => null,
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 hours')),
            ],
        ];

        $this->db->table('notifications')->insertBatch($data);
    }
}
