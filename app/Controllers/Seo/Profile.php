<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoProfilesModel;
use App\Models\ActivityLogsModel;

class Profile extends BaseController
{
    protected $seoProfilesModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->seoProfilesModel  = new SeoProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
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
            ->select('secret')
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
        if (! $user) return redirect()->to('/login');

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

        $this->logActivity($user->id, null, 'view_profile', 'success', 'Mengakses profil SEO user');

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
        if (! $user) return redirect()->to('/login');

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

        $this->logActivity($user->id, null, 'view_profile_edit', 'success', 'Mengakses form edit profil');

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
        if (! $user) return redirect()->to('/login');

        $sp = $this->sp();
        if (! $sp) {
            $this->logActivity($user->id, null, 'update_profile', 'failed', 'Profil tidak ditemukan');
            return redirect()->back()->with('error', 'Profil tidak ditemukan');
        }

        $rules = [
            'name'                 => 'required|min_length[3]',
            'phone'                => 'permit_empty',
            'profile_image'        => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image' => 'permit_empty|in_list[0,1]',
        ];

        if (! $this->validate($rules)) {
            $this->logActivity($user->id, null, 'update_profile', 'failed', 'Validasi gagal', $this->validator->getErrors());
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

        // Hapus
        if ($this->request->getPost('remove_profile_image') === '1' && !empty($sp['profile_image'])) {
            $oldPath = $pubDir . '/' . $sp['profile_image'];
            if (is_file($oldPath)) @unlink($oldPath);
            $data['profile_image'] = null;
            $profileImageChanged   = true;
        }

        // Upload baru
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            if (!empty($sp['profile_image'])) {
                $oldPath = $pubDir . '/' . $sp['profile_image'];
                if (is_file($oldPath)) @unlink($oldPath);
            }
            $newName = $file->getRandomName();
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
            $profileImageChanged   = true;
        }

        $this->seoProfilesModel->update($sp['id'], $data);

        $changes = [];
        if ($profileImageChanged) $changes['profile_image'] = $data['profile_image'] ?? 'removed';

        $this->logActivity($user->id, null, 'update_profile', 'success', 'Update profil berhasil', $changes);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    /* ------------------- PASSWORD ------------------- */
    public function password()
    {
        $user = $this->user();
        if (! $user) return redirect()->to('/login');

        $this->logActivity($user->id, null, 'view_password_form', 'success', 'Mengakses form ubah password');

        return view('Seo/profile/ubahpassword', [
            'title'      => 'Ubah Password',
            'activeMenu' => 'profile',
        ]);
    }

    public function passwordUpdate()
    {
        $user = $this->user();
        if (! $user) return redirect()->back()->with('error_password', 'User tidak ditemukan / sesi habis.');

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'confirm_password' => 'required|matches[new_password]'
        ];

        if (! $this->validate($rules)) {
            $this->logActivity($user->id, null, 'update_password', 'failed', 'Validasi password gagal', $this->validator->getErrors());
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

        if (! $identity || ! password_verify((string)$this->request->getPost('current_password'), (string)$identity->secret2)) {
            $this->logActivity($user->id, null, 'update_password', 'failed', 'Password lama salah');
            return redirect()->back()->with('error_password', 'Password lama salah.');
        }

        // Update password
        $db->table('auth_identities')
            ->where('id', $identity->id)
            ->update([
                'secret2'    => password_hash((string)$this->request->getPost('new_password'), PASSWORD_BCRYPT),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

        $this->logActivity($user->id, null, 'update_password', 'success', 'Password berhasil diubah');

        service('auth')->logout();
        return redirect()->to('/login')->with('success_password', 'Password berhasil diubah. Silakan login ulang.');
    }

    /* ------------------- LOGGING ------------------- */
    private function logActivity($userId, $ignoredSeoId, $action, $status, $description = null, $additionalData = [])
    {
        // NOTE: activity_logs tidak punya kolom seo_id → vendor_id NULL.
        try {
            $data = [
                'user_id'     => $userId,
                'vendor_id'   => null,
                'module'      => 'seo_profile',
                'action'      => $action,
                'status'      => $status,
                'description' => $description,
                'ip_address'  => (string)$this->request->getIPAddress(),
                'user_agent'  => (string)$this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData, JSON_UNESCAPED_UNICODE);
            }
            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', '[SEO Profile] Log gagal: ' . $e->getMessage());
        }
    }
}