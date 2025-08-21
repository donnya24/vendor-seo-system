<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ServicesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Ambil langsung vendor_profiles
        $vendors = $db->table('vendor_profiles')
            ->select('id as vendor_id, business_name, owner_name')
            ->get()
            ->getResult();

        if (empty($vendors)) {
            echo "Tidak ada vendor_profiles ditemukan.\n";
            return;
        }

        $data = [];
        foreach ($vendors as $vendor) {
            $data[] = [
                'vendor_id'    => $vendor->vendor_id,
                'name'         => 'Layanan ' . $vendor->business_name,
                'service_type' => 'vendor_service',
                'description'  => 'Layanan utama yang ditawarkan oleh ' . $vendor->business_name .
                                   ' milik ' . $vendor->owner_name,
                'status'       => 'pending', // default menunggu approval Admin/SEO
                'created_at'   => date('Y-m-d H:i:s'),
            ];
        }

        $db->table('services')->insertBatch($data);

        echo "Seeder ServicesSeeder selesai. Total service ditambahkan: " . count($data) . "\n";
    }
}
