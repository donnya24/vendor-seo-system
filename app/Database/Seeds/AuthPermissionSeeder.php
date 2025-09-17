<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class AuthPermissionSeeder extends Seeder
{
    public function run()
    {
        $now = Time::now();

        // Daftar permission sesuai config AuthGroups
        $permissions = [
            'admin.access',
            'admin.settings',
            'users.manage',
            'seo.access',
            'seo.manage',
            'vendor.access',
        ];

        // Isi user → permission manual
        $data = [
            [
                'user_id'    => 1, // ID Admin
                'permission' => 'admin.access',
                'created_at' => $now,
            ],
            [
                'user_id'    => 1,
                'permission' => 'users.manage',
                'created_at' => $now,
            ],
            [
                'user_id'    => 2, // ID Vendor
                'permission' => 'vendor.access',
                'created_at' => $now,
            ],
            [
                'user_id'    => 11, // ID SEO
                'permission' => 'seo.access',
                'created_at' => $now,
            ],
        ];

        // Insert langsung
        $this->db->table('auth_permissions_users')->insertBatch($data);
    }
}
