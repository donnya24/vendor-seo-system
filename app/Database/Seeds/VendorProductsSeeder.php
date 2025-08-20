<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class VendorProductsSeeder extends Seeder
{
    public function run()
    {
        // Pastikan vendor_profiles minimal punya 1 baris (di dump kamu ada id=1)
        // Sesuaikan $vendorId kalau perlu.
        $vendorId = 1;

        $now = Time::now('UTC'); // simpan UTC biar konsisten

        $data = [
            [
                'vendor_id'    => $vendorId,
                'product_name' => 'Label Woven Premium',
                'description'  => 'Label woven premium kualitas tinggi, cocok untuk pakaian butik. MOQ 100 pcs.',
                'price'        => '3500.00',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'vendor_id'    => $vendorId,
                'product_name' => 'Hangtag Custom',
                'description'  => 'Hangtag custom dengan berbagai ukuran dan finishing (doff/glossy).',
                'price'        => '1500.00',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'vendor_id'    => $vendorId,
                'product_name' => 'Stiker Label Produk',
                'description'  => 'Stiker label tahan air untuk kemasan produk UMKM.',
                'price'        => '500.00',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'vendor_id'    => $vendorId,
                'product_name' => 'Jasa Desain Label',
                'description'  => 'Paket desain label (3 opsi konsep, 2x revisi).',
                'price'        => '150000.00',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'vendor_id'    => $vendorId,
                'product_name' => 'Label Satin',
                'description'  => 'Label satin lembut untuk inner/size label.',
                'price'        => '1200.00',
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ];

        $this->db->table('vendor_products')->insertBatch($data);
    }
}
