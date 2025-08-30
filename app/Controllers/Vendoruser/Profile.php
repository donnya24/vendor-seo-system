<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\UserModel;

class Profile extends BaseController
{
    private function user()
    {
        return service('auth')->user();
    }

    private function vp(): ?array
    {
        return (new VendorProfilesModel())
            ->where('user_id', (int)$this->user()->id)
            ->first();
    }

    public function edit()
    {
        $vp = $this->vp();
        return view('vendoruser/profile/edit', [
            'vp' => $vp,
        ]);
    }

    public function update()
    {
        $user = $this->user();
        $vendorProfileModel = new VendorProfilesModel();
        $vp = $this->vp();

        if (!$vp) return redirect()->back()->with('error','Profil tidak ditemukan');

        // ===== VALIDATION =====
        $rules = [
            'business_name'         => 'required|min_length[3]',
            'owner_name'            => 'required|min_length[3]',
            'whatsapp_number'       => 'required',
            'phone'                 => 'permit_empty',
            'profile_image'         => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image'  => 'permit_empty|in_list[0,1]',
        ];

        $isVerified = ($vp['status'] ?? 'pending') === 'verified';
        if (! $isVerified) {
            // vendor boleh & HARUS isi komisi saat belum verified/pending
            $rules['requested_commission'] = 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]';
        }

        $validation = \Config\Services::validation();
        $validation->setRules($rules);
        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // ===== PAYLOAD =====
        $data = [
            'business_name'   => (string)$this->request->getPost('business_name'),
            'owner_name'      => (string)$this->request->getPost('owner_name'),
            'whatsapp_number' => (string)$this->request->getPost('whatsapp_number'),
            'phone'           => (string)($this->request->getPost('phone') ?? ''),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // ===== KOMISI (boleh edit jika BELUM verified) =====
        if (! $isVerified) {
            $reqRaw = str_replace(',', '.', (string)$this->request->getPost('requested_commission'));
            $data['requested_commission'] = is_numeric($reqRaw) ? (float)$reqRaw : null;
            // tetap pending agar direview admin lagi
            $data['status'] = 'pending';
        }

        // ===== FOTO PROFIL (samakan path dengan view/header) =====
        $pubDir = FCPATH . 'uploads/vendoruser/profiles';
        if (!is_dir($pubDir)) @mkdir($pubDir, 0775, true);

        // Hapus jika diminta
        if ((string)$this->request->getPost('remove_profile_image') === '1' && !empty($vp['profile_image'])) {
            $oldPath = $pubDir . '/' . $vp['profile_image'];
            if (is_file($oldPath)) @unlink($oldPath);
            $data['profile_image'] = null;
        }

        // Upload baru
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // hapus lama
            if (!empty($vp['profile_image'])) {
                $oldPath = $pubDir . '/' . $vp['profile_image'];
                if (is_file($oldPath)) @unlink($oldPath);
            }
            $newName = $file->getRandomName();
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
        }

        $vendorProfileModel->update($vp['id'], $data);

        // (opsional) kirim notifikasi admin kalau belum verified
        if (! $isVerified) {
            try { $this->sendVerificationNotification($user, $data + ['requested_commission' => $data['requested_commission'] ?? null]); } catch (\Throwable $e) {}
        }

        return redirect()->back()->with('success','Profil berhasil diperbarui');
    }

    private function sendVerificationNotification($user, $vendorData)
    {
        $db = db_connect();
        if ($db->tableExists('notifications')) {
            $db->table('notifications')->insert([
                'user_id'    => 1, // ID admin (sesuaikan)
                'title'      => 'Pengajuan/Perubahan Komisi Vendor',
                'message'    => 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                                ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                                ') mengajukan/ubah komisi ' . ($vendorData['requested_commission'] ?? '-') . '%.',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ========= Password =========
    public function password()
    {
        return view('vendoruser/profile/ubahpassword', ['page' => 'Ubah Password']);
    }

    public function passwordUpdate()
    {
        if (! $this->request->is('post')) {
            return redirect()->back()->with('error_password','Method not allowed');
        }

        $user = $this->user();

        // Validasi sederhana
        $val = \Config\Services::validation();
        $val->setRules([
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'pass_confirm'     => 'required|matches[new_password]',
        ], [
            'pass_confirm' => ['matches' => 'Konfirmasi password tidak cocok.']
        ]);
        if (! $val->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors_password', $val->getErrors())->with('error_password','Terjadi kesalahan validasi.');
        }

        $db  = db_connect();
        $row = $db->table('auth_identities')
                  ->select('id, secret, secret2')
                  ->where('user_id', (int)$user->id)
                  ->where('type', 'email_password')
                  ->get()->getRowArray();

        if (! $row) {
            return redirect()->back()->with('error_password','Identitas login tidak ditemukan.');
        }

        // Deteksi kolom mana yang menyimpan HASH (secret atau secret2)
        $hashCol = null;
        foreach (['secret','secret2'] as $col) {
            $val = (string)($row[$col] ?? '');
            $info = password_get_info($val);
            if (!empty($info['algo'])) { $hashCol = $col; break; }
        }
        // Fallback: jika `secret` berformat email, asumsi hash di `secret2`
        if (!$hashCol) {
            if (filter_var($row['secret'] ?? '', FILTER_VALIDATE_EMAIL)) $hashCol = 'secret2';
            elseif (filter_var($row['secret2'] ?? '', FILTER_VALIDATE_EMAIL)) $hashCol = 'secret';
        }
        if (!$hashCol) {
            return redirect()->back()->with('error_password','Kolom hash password tidak terdeteksi.');
        }

        $current = (string)$this->request->getPost('current_password');
        $new     = (string)$this->request->getPost('new_password');

        if (! password_verify($current, (string)$row[$hashCol])) {
            return redirect()->back()->withInput()->with('error_password','Password saat ini salah.');
        }

        $newHash = password_hash($new, PASSWORD_DEFAULT);
        try {
            $db->table('auth_identities')
               ->where('id', (int)$row['id'])
               ->update([$hashCol => $newHash, 'updated_at' => date('Y-m-d H:i:s')]);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error_password','Gagal menyimpan password baru.');
        }

        return redirect()->back()->with('success_password','Password berhasil diubah.');
    }
}
