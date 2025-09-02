<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class VendorServicesSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Ambil semua service
        $services = $db->table('services')
            ->select('id, vendor_id, name')
            ->get()
            ->getResult();

        if (empty($services)) {
            echo "Tidak ada data services ditemukan.\n";
            return;
        }

        $data = [];
        foreach ($services as $service) {
            $data[] = [
                'vendor_id'       => $service->vendor_id,
                'service_id'      => $service->id,
                'start_date'      => date('Y-m-d'),
                'end_date'        => null,
                'created_at'      => date('Y-m-d H:i:s'),
            ];
        }

        $db->table('vendor_services')->insertBatch($data);

        echo "Seeder VendorServicesSeeder selesai. Total vendor_services ditambahkan: " . count($data) . "\n";
    }
}
