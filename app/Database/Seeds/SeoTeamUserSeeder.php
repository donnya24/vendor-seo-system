<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\Shield\Entities\User;
use App\Models\UserModel;

class SeoTeamUserSeeder extends Seeder
{
    public function run()
    {
        $users = model(UserModel::class);

        // Cek user existing via email/username
        $existing = $users->findByCredentials(['email' => 'Timseo@gmail.com'])
                 ?? $users->where('username', 'seo')->first();

        if (! $existing) {
            // Buat user baru
            $user = new User([
                'username' => 'seo',
                'email'    => 'Timseo@gmail.com',
                'password' => 'magang25',
                'status'   => 'active',
            ]);

            $users->save($user);
            $user = $users->findById($users->getInsertID());

            // ✅ Set langsung user aktif (tanpa activator)
            $users->update($user->id, [
                'active' => 1,
                'status' => 'active',
            ]);

            // ✅ Update name di auth_identities
            $this->db->table('auth_identities')
                ->where('user_id', $user->id)
                ->set('name', 'Tim SEO')
                ->update();

            // Masukkan ke grup seoteam
            $user->addGroup('seoteam');

            echo "✅ Admin user (Tim SEO) created & activated successfully!\n";
        } else {
            $user = is_array($existing)
                ? $users->findById($existing['id'])
                : $existing;

            // Pastikan user sudah active
            if (! $user->active) {
                $users->update($user->id, [
                    'active' => 1,
                    'status' => 'active',
                ]);
                echo "User Tim SEO diaktifkan ulang.\n";
            }

            // Pastikan masuk ke grup seoteam
            if (! $user->inGroup('seoteam')) {
                $user->addGroup('seoteam');
                echo "Added existing Tim SEO to 'seoteam' group.\n";
            }

            // Update name kalau masih null
            $this->db->table('auth_identities')
                ->where('user_id', $user->id)
                ->where('name IS NULL')
                ->set('name', 'Tim SEO')
                ->update();
        }

        echo "==============================\n";
        echo "Admin Account Information:\n";
        echo "Username: seo\n";
        echo "Email: Timseo@gmail.com\n";
        echo "Password: magang25\n";
        echo "==============================\n";
    }
}
