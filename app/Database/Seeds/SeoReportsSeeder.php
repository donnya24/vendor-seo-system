<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SeoReportsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'vendor_id'  => 1,
                'keyword'    => 'label baju murah',
                'project'    => 'Label Baju Stratlaya',
                'position'   => 5,
                'change'     => 2,
                'trend'      => 'up',
                'volume'     => 1200,
                'status'     => 'active',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('seo_reports')->insertBatch($data);
    }
}
