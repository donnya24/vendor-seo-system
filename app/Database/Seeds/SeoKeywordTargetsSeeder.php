<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SeoKeywordTargetsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // ambil beberapa vendor id yang ada
        $vendorIds = array_map(
            fn($r) => (int) $r->id,
            $db->table('vendor_profiles')->select('id')->orderBy('id', 'ASC')->get(5)->getResult()
        );

        if (empty($vendorIds)) {
            // fallback biar seed tetap jalan di mesin dev kosong
            $vendorIds = [1];
        }

        $rows = [
            [
                'vendor_id'        => $vendorIds[0],
                'project_name'     => 'Label Baju Stratlaya',
                'keyword'          => 'label baju murah',
                'current_position' => 12,
                'target_position'  => 5,
                'deadline'         => date('Y-m-d', strtotime('+30 days')),
                'status'           => 'in_progress',
                'priority'         => 'high',
                'notes'            => 'Perkuat backlink niche + update halaman kategori.',
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'        => $vendorIds[0],
                'project_name'     => 'Cetak Yasin Bangkalan',
                'keyword'          => 'cetak yasin hardcover',
                'current_position' => 18,
                'target_position'  => 9,
                'deadline'         => date('Y-m-d', strtotime('+45 days')),
                'status'           => 'pending',
                'priority'         => 'medium',
                'notes'            => 'Optimasi internal link + schema Product/Service.',
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'        => $vendorIds[count($vendorIds) > 1 ? 1 : 0],
                'project_name'     => 'Kursus Bahasa Inggris',
                'keyword'          => 'kursus bahasa inggris online',
                'current_position' => 8,
                'target_position'  => 3,
                'deadline'         => date('Y-m-d', strtotime('+21 days')),
                'status'           => 'in_progress',
                'priority'         => 'high',
                'notes'            => 'Tambahkan landing untuk long-tail + E-E-A-T.',
                'created_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        $db->table('seo_keyword_targets')->insertBatch($rows);
    }
}
