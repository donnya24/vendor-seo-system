<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VendorServicesProductsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'vendor_id'           => 1,
                'service_name'        => 'Custom Butik',
                'service_description' => 'Melayani custom pakaian sesuai permintaan',
                'product_name'        => 'Kain',
                'product_description' => 'Kain Bagus',
                'price'               => 8000,
                'attachment'          => null,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
            [
                'vendor_id'           => 1,
                'service_name'        => 'Sewa Butik',
                'service_description' => 'Melayani penyewaan butik untuk acara',
                'product_name'        => null,
                'product_description' => null,
                'price'               => null,
                'attachment'          => null,
                'created_at'          => date('Y-m-d H:i:s'),
                'updated_at'          => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('vendor_services_products')->insertBatch($data);
    }
}
