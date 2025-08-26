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

        if (!$vp) {
            return redirect()->back()->with('error', 'Profil tidak ditemukan');
        }

        // ========= VALIDATION =========
        $rules = [
            'business_name'   => 'required|min_length[3]',
            'owner_name'      => 'required|min_length[3]',
            'whatsapp_number' => 'required',
            'phone'           => 'permit_empty',
            'profile_image'   => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image' => 'permit_empty|in_list[0,1]'
        ];

        $isVerified = ($vp['status'] ?? 'pending') === 'verified';
        if (!$isVerified) {
            // vendor boleh & harus mengisi komisi saat belum verified/pending
            $rules['requested_commission'] = 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]';
        }

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        // ========= PAYLOAD DASAR =========
        $data = [
            'business_name'   => (string)$this->request->getPost('business_name'),
            'owner_name'      => (string)$this->request->getPost('owner_name'),
            'whatsapp_number' => (string)$this->request->getPost('whatsapp_number'),
            'phone'           => (string)($this->request->getPost('phone') ?? ''),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // ========= KOMISI: boleh diubah kalau BELUM verified =========
        if (!$isVerified) {
            $reqRaw = str_replace(',', '.', (string)$this->request->getPost('requested_commission'));
            $reqVal = is_numeric($reqRaw) ? (float)$reqRaw : null;
            $data['requested_commission'] = $reqVal;
            // bila vendor mengubah data saat belum verified → tetap pending agar admin review
            $data['status'] = 'pending';
        }

        // ========= FOTO PROFIL (DB pakai kolom profile_image) =========
        // folder publik: public/uploads/vendor_profiles/
        $pubDir = FCPATH . 'uploads/vendor_profiles';
        if (!is_dir($pubDir)) {
            @mkdir($pubDir, 0775, true);
        }

        // hapus foto jika diminta
        $removeFlag = (string)$this->request->getPost('remove_profile_image') === '1';
        if ($removeFlag && !empty($vp['profile_image'])) {
            $oldPath = $pubDir . '/' . $vp['profile_image'];
            if (is_file($oldPath)) @unlink($oldPath);
            $data['profile_image'] = null;
        }

        // upload baru (replace file lama jika ada)
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            // hapus lama
            if (!empty($vp['profile_image'])) {
                $oldPath = $pubDir . '/' . $vp['profile_image'];
                if (is_file($oldPath)) @unlink($oldPath);
            }
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
        }

        // simpan
        $vendorProfileModel->update($vp['id'], $data);

        // Kirim notifikasi admin kalau status masih pending (opsional)
        if (!$isVerified) {
            try { $this->sendVerificationNotification($user, $data + ['requested_commission' => $data['requested_commission'] ?? null]); } catch (\Throwable $e) {}
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    private function sendVerificationNotification($user, $vendorData)
    {
        $db = db_connect();
        if ($db->tableExists('notifications')) {
            $msg = 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                   ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                   ') mengajukan/ubah komisi ' . ($vendorData['requested_commission'] ?? '-') . '%.';
            $db->table('notifications')->insert([
                'user_id'    => 1, // ID admin (silakan sesuaikan)
                'title'      => 'Pengajuan/Perubahan Komisi Vendor',
                'message'    => $msg,
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ---------- Password ----------
    public function password()
    {
        return view('vendoruser/profile/ubahpassword', ['page' => 'Ubah Password']);
    }

    public function passwordUpdate()
    {
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error_password', 'Method not allowed');
        }

        $auth = service('auth');
        $user = $auth->user();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'current_password' => 'required',
            // jika "strong_password" tidak aktif di project kamu, ganti jadi min_length[8]
            'new_password'     => 'required|min_length[8]',
            'pass_confirm'     => 'required|matches[new_password]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()
                ->withInput()
                ->with('errors_password', $validation->getErrors())
                ->with('error_password', 'Terjadi kesalahan validasi.');
        }

        // ambil hash dari user model (pastikan $user punya field password_hash)
        if (empty($user->password_hash) || !password_verify($this->request->getPost('current_password'), $user->password_hash)) {
            return redirect()->back()->withInput()->with('error_password', 'Password saat ini salah.');
        }

        $userModel = new UserModel();
        $userModel->update($user->id, [
            'password_hash' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)
        ]);

        return redirect()->back()->with('success_password', 'Password berhasil diubah.');
    }
}
