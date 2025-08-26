<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\UserModel;

class Profile extends BaseController
{
       public function edit()
    {
        $user = service('auth')->user();
        $vendorProfileModel = new VendorProfilesModel();
        $vp = $vendorProfileModel->where('user_id', $user->id)->first();

        return view('vendoruser/profile/edit', [
            'vendor' => $vp,
        ]);
    }

    public function update()
    {
        $user = service('auth')->user();
        $vendorProfileModel = new VendorProfilesModel();
        $vp = $vendorProfileModel->where('user_id', $user->id)->first();

        if (!$vp) {
            return redirect()->back()->with('error', 'Profil tidak ditemukan');
        }

        // Rules dasar
        $rules = [
            'business_name'   => 'required|min_length[3]',
            'owner_name'      => 'required|min_length[3]',
            'whatsapp_number' => 'required',
            'phone'           => 'permit_empty',
            'profile_image'   => 'max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png]',
        ];

        // hanya validasi commission kalau belum verified
        if ($vp['status'] !== 'verified') {
            $rules['requested_commission'] = 'required|numeric';
        }

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // Data update
        $data = [
            'business_name'   => $this->request->getPost('business_name'),
            'owner_name'      => $this->request->getPost('owner_name'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'phone'           => $this->request->getPost('phone'),
        ];

        // Hanya update komisi kalau belum verified
        if ($vp['status'] !== 'verified') {
            $data['requested_commission'] = $this->request->getPost('requested_commission');
            $data['status'] = 'pending'; // supaya diverifikasi admin lagi
        }

        // Upload gambar profil
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/vendor_profiles', $newName);
            $data['profile_image'] = $newName;
        }

        $vendorProfileModel->update($vp['id'], $data);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    private function sendVerificationNotification($user, $vendorData)
    {
        // Simpan notifikasi ke database untuk admin
        $db = db_connect();
        if ($db->tableExists('notifications')) {
            $notificationData = [
                'user_id' => 1, // ID admin
                'title' => 'Pengajuan Verifikasi Vendor Baru',
                'message' => 'Vendor ' . $vendorData['business_name'] . ' (Pemilik: ' . $vendorData['owner_name'] . ') mengajukan komisi ' . $vendorData['requested_commission'] . '%. Silakan verifikasi profil mereka.',
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->table('notifications')->insert($notificationData);
        }
    }
    
    public function password()
    {
        return view('vendoruser/profile/ubahpassword', [
            'page' => 'Ubah Password'
        ]);
    }
    
    public function passwordUpdate()
    {
        // Pastikan method POST
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error_password', 'Method not allowed');
        }

        $auth = service('auth');
        $user = $auth->user();
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'current_password' => 'required',
            'new_password' => 'required|min_length[8]|strong_password',
            'pass_confirm' => 'required|matches[new_password]'
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors_password', $validation->getErrors())->with('error_password', 'Terjadi kesalahan validasi.');
        }
        
        // Verifikasi password saat ini
        if (!password_verify($this->request->getPost('current_password'), $user->password_hash)) {
            return redirect()->back()->withInput()->with('error_password', 'Password saat ini salah.');
        }
        
        // Update password
        $userModel = new UserModel();
        $userModel->update($user->id, [
            'password_hash' => password_hash($this->request->getPost('new_password'), PASSWORD_DEFAULT)
        ]);
        
        return redirect()->back()->with('success_password', 'Password berhasil diubah.');
    }
}