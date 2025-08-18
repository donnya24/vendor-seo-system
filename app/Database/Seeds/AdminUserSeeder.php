<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserModel;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $users = model(UserModel::class);

        // Cek via email/username
        $existing = $users->findByCredentials(['email' => 'donnyk300@gmail.com'])
                 ?? $users->where('username', 'admin')->first();

        if (! $existing) {
            // Buat user baru
            $user = new User([
                'username' => 'admin',
                'email'    => 'donnyk300@gmail.com',
                'password' => 'magang25',
                'status'   => 'active',
            ]);

            $users->save($user);
            $user = $users->findById($users->getInsertID());

            // ✅ Update ke tabel users
            $this->db->table('users')
                ->where('id', $user->id)
                ->update([
                    'active'         => 1,
                    'status'         => 'active',
                    'status_message' => 'Default admin account',
                    'last_active'    => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);

            // ✅ Update name di auth_identities
            $this->db->table('auth_identities')
                ->where('user_id', $user->id)
                ->set('name', 'Administrator Utama')
                ->update();

            // Masukkan ke grup admin
            $user->addGroup('admin');

            echo "✅ Admin user created successfully!\n";
        } else {
            $user = is_array($existing)
                ? $users->findById($existing['id'])
                : $existing;

            // Pastikan ada di grup admin
            if (! $user->inGroup('admin')) {
                $user->addGroup('admin');
                echo "Added existing admin to 'admin' group.\n";
            } else {
                echo "Admin user already exists and is in 'admin' group.\n";
            }

            // Update name kalau masih null
            $this->db->table('auth_identities')
                ->where('user_id', $user->id)
                ->where('name IS NULL')
                ->set('name', 'Administrator Utama')
                ->update();

            // Update users table kalau active=0 atau status_message NULL
            $this->db->table('users')
                ->where('id', $user->id)
                ->update([
                    'active'         => 1,
                    'status'         => 'active',
                    'status_message' => 'Default admin account',
                    'last_active'    => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);
        }

        echo "==============================\n";
        echo "Admin Account Information:\n";
        echo "Username: admin\n";
        echo "Email: donnyk300@gmail.com\n";
        echo "Password: magang25\n";
        echo "==============================\n";
    }
}
