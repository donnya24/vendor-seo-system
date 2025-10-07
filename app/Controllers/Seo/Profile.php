<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoProfilesModel;

class Profile extends BaseController
{
    protected $seoProfilesModel;

    public function __construct()
    {
        $this->seoProfilesModel = new SeoProfilesModel();
    }

    private function user()
    {
        return service('auth')->user();
    }

    /** Ambil email dari auth_identities.secret (type: email / email_password) */
    private function userEmail(int $userId): ?string
    {
        $db  = db_connect();
        $row = $db->table('auth_identities')
            ->where('user_id', $userId)
            ->whereIn('type', ['email', 'email_password'])
            ->orderBy('id', 'desc')
            ->get()
            ->getRowArray();

        return $row['secret'] ?? null;
    }

    /** Ambil seo profile untuk user ter-login */
    private function sp(): ?array
    {
        $user = $this->user();
        if (!$user) return null;

        return $this->seoProfilesModel
            ->where('user_id', (int)$user->id)
            ->first();
    }

    /* ------------------- PROFILE ------------------- */
    public function index()
    {
        $user = $this->user();
        if (!$user) return redirect()->to('/login');

        $sp = $this->sp() ?? [
            'id'            => null,
            'user_id'       => (int)$user->id,
            'name'          => '',
            'phone'         => '',
            'profile_image' => '',
            'status'        => 'active',
        ];

        // image path
        $profileImage     = $sp['profile_image'] ?? '';
        $profileImagePath = base_url('assets/img/default-avatar.png');
        if ($profileImage && is_file(FCPATH . 'uploads/seo_profiles/' . $profileImage)) {
            $profileImagePath = base_url('uploads/seo_profiles/' . $profileImage);
        }

        // Log aktivitas view profile
        log_activity_auto('view', "Melihat profil SEO", [
            'module' => 'seo_profile'
        ]);

        return view('Seo/profile/index', [
            'profile'          => $sp,
            'sp'               => $sp,
            'userEmail'        => $this->userEmail((int)$user->id) ?? '',
            'profileImagePath' => $profileImagePath,
            'title'            => 'Profil Saya',
            'activeMenu'       => 'profile',
        ]);
    }

    public function edit()
    {
        $user = $this->user();
        if (!$user) return redirect()->to('/login');

        $seoProfile = $this->sp() ?? [
            'id'            => null,
            'user_id'       => (int)$user->id,
            'name'          => '',
            'phone'         => '',
            'status'        => 'active',
            'profile_image' => ''
        ];

        $profileImage     = $seoProfile['profile_image'] ?? '';
        $profileImagePath = base_url('assets/img/default-avatar.png');
        if ($profileImage && is_file(FCPATH . 'uploads/seo_profiles/' . $profileImage)) {
            $profileImagePath = base_url('uploads/seo_profiles/' . $profileImage);
        }

        // Log aktivitas view edit profile
        log_activity_auto('view', "Membuka form edit profil SEO", [
            'module' => 'seo_profile'
        ]);

        return view('Seo/profile/edit', [
            'sp'               => $seoProfile,
            'userEmail'        => $this->userEmail((int)$user->id) ?? '',
            'profileImagePath' => $profileImagePath,
            'title'            => 'Edit Profil',
            'activeMenu'       => 'profile',
        ]);
    }

    public function update()
    {
        $user = $this->user();
        if (!$user) return redirect()->to('/login');

        $sp = $this->sp();
        if (!$sp) {
            // Log aktivitas gagal update profile
            log_activity_auto('update', "Gagal update profil SEO - profil tidak ditemukan", [
                'module' => 'seo_profile',
                'status' => 'failed'
            ]);
            return redirect()->back()->with('error', 'Profil tidak ditemukan');
        }

        $rules = [
            'name'                 => 'required|min_length[3]',
            'phone'                => 'permit_empty',
            'profile_image'        => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image' => 'permit_empty|in_list[0,1]',
        ];

        if (!$this->validate($rules)) {
            // Log aktivitas validasi gagal
            log_activity_auto('update', "Validasi update profil SEO gagal", [
                'module' => 'seo_profile',
                'status' => 'failed',
                'errors' => $this->validator->getErrors()
            ]);
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name'       => (string)$this->request->getPost('name'),
            'phone'      => (string)($this->request->getPost('phone') ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Upload / Hapus foto
        $pubDir = FCPATH . 'uploads/seo_profiles';
        if (!is_dir($pubDir)) {
            @mkdir($pubDir, 0775, true);
        }

        $profileImageChanged = false;
        $changes = [];

        // Hapus foto profil
        if ($this->request->getPost('remove_profile_image') === '1' && !empty($sp['profile_image'])) {
            $oldPath = $pubDir . '/' . $sp['profile_image'];
            if (is_file($oldPath)) @unlink($oldPath);
            $data['profile_image'] = null;
            $profileImageChanged   = true;
            $changes['profile_image'] = 'removed';
        }

        // Upload foto profil baru
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!empty($sp['profile_image'])) {
                $oldPath = $pubDir . '/' . $sp['profile_image'];
                if (is_file($oldPath)) @unlink($oldPath);
            }
            $newName = $file->getRandomName();
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
            $profileImageChanged   = true;
            $changes['profile_image'] = 'updated';
        }

        // Track perubahan data
        if ($sp['name'] !== $data['name']) {
            $changes['name'] = $data['name'];
        }
        if ($sp['phone'] !== $data['phone']) {
            $changes['phone'] = $data['phone'];
        }

        $this->seoProfilesModel->update($sp['id'], $data);

        // Log aktivitas berhasil update profile
        log_activity_auto('update', "Berhasil update profil SEO", [
            'module'  => 'seo_profile',
            'status'  => 'success',
            'changes' => $changes
        ]);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    /* ------------------- PASSWORD ------------------- */
    public function password()
    {
        $user = $this->user();
        if (!$user) return redirect()->to('/login');

        // Log aktivitas view password form
        log_activity_auto('view', "Membuka form ubah password", [
            'module' => 'seo_profile'
        ]);

        return view('Seo/profile/ubahpassword', [
            'title'      => 'Ubah Password',
            'activeMenu' => 'profile',
        ]);
    }

    public function passwordUpdate()
    {
        $user = $this->user();
        if (!$user) {
            // Log aktivitas gagal - user tidak ditemukan
            log_activity_auto('update_password', "Gagal ubah password - user tidak ditemukan", [
                'module' => 'seo_profile',
                'status' => 'failed'
            ]);
            return redirect()->back()->with('error_password', 'User tidak ditemukan / sesi habis.');
        }

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (!$this->validate($rules)) {
            // Log aktivitas validasi password gagal
            log_activity_auto('update_password', "Validasi ubah password gagal", [
                'module' => 'seo_profile',
                'status' => 'failed',
                'errors' => $this->validator->getErrors()
            ]);
            return redirect()->back()->withInput()->with('errors_password', $this->validator->getErrors());
        }

        // Ambil record auth_identities (email_password)
        $db = db_connect();
        $identity = $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->orderBy('id', 'desc')
            ->get()
            ->getRow();

        if (!$identity || !password_verify((string)$this->request->getPost('current_password'), (string)$identity->secret2)) {
            // Log aktivitas password lama salah
            log_activity_auto('update_password', "Gagal ubah password - password lama salah", [
                'module' => 'seo_profile',
                'status' => 'failed'
            ]);
            return redirect()->back()->with('error_password', 'Password lama salah.');
        }

        // Update password
        $newPasswordHash = password_hash((string)$this->request->getPost('new_password'), PASSWORD_BCRYPT);
        $db->table('auth_identities')
            ->where('id', $identity->id)
            ->update([
                'secret2'    => $newPasswordHash,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        // Log aktivitas berhasil ubah password
        log_activity_auto('update_password', "Berhasil mengubah password", [
            'module' => 'seo_profile',
            'status' => 'success'
        ]);

        service('auth')->logout();
        
        // Log aktivitas logout setelah ubah password
        log_activity_auto('logout', "Logout otomatis setelah ubah password", [
            'module' => 'auth'
        ]);

        return redirect()->to('/login')->with('success_password', 'Password berhasil diubah. Silakan login ulang.');
    }
}