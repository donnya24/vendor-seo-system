<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class VendorProfilesSeeder extends Seeder
{
    public function run()
    {
        // Contoh isi profil vendor untuk user_id=2 (vendor: toko.butik)
        $this->db->table('vendor_profiles')->insert([
            'user_id'         => 2, // FK ke users.id
            'business_name'   => 'Butik Cantika',
            'owner_name'      => 'Siti Aisyah',
            'phone'           => '081234567890',
            'whatsapp_number' => '6281234567890',
            'status'          => 'active',
            'is_verified'     => 1,
            'created_at'      => Time::now(),
            'updated_at'      => Time::now(),
        ]);

        // Bisa tambahkan vendor lain (misalnya user_id lain yang punya role vendor)
        /*
        $this->db->table('vendor_profiles')->insert([
            'user_id'         => 5,
            'business_name'   => 'Toko Elektronik Jaya',
            'owner_name'      => 'Budi Santoso',
            'phone'           => '081298765432',
            'whatsapp_number' => '6281298765432',
            'status'          => 'pending',
            'is_verified'     => 0,
            'created_at'      => Time::now(),
        ]);
        */
    }
}