<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LeadsSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();

        // Ambil semua services supaya bisa assign leads
        $services = $db->table('services')
            ->select('id, vendor_id, name')
            ->get()
            ->getResult();

        if (empty($services)) {
            echo "Tidak ada data services ditemukan.\n";
            return;
        }

        $faker = \Faker\Factory::create('id_ID');

        $data = [];
        foreach ($services as $service) {
            // Generate 3 leads untuk setiap service
            for ($i = 0; $i < 3; $i++) {
                $statusOptions = ['new','in_progress','closed','rejected'];
                $sourceOptions = ['wa_inbox','wa_outbox','vendor_manual'];

                $data[] = [
                    'vendor_id'          => $service->vendor_id,
                    'customer_name'      => $faker->name,
                    'customer_phone'     => $faker->phoneNumber,
                    'service_id'         => $service->id,
                    'status'             => $faker->randomElement($statusOptions),
                    'source'             => $faker->randomElement($sourceOptions),
                    'reported_by_vendor' => $service->vendor_id, // default dianggap vendor sendiri
                    'assigned_at'        => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                ];
            }
        }

        $db->table('leads')->insertBatch($data);

        echo "Seeder LeadsSeeder selesai. Total leads ditambahkan: " . count($data) . "\n";
    }
}
