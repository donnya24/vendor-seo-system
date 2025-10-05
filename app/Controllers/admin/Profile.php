<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AdminProfileModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;

class Profile extends BaseController
{
    protected $activityLogsModel;
    protected $notificationsModel;
    protected $adminProfileModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->adminProfileModel = new AdminProfileModel();
    }

    private function user()
    {
        return service('auth')->user();
    }

    private function ap(): ?array
    {
        return $this->adminProfileModel
            ->where('user_id', (int) $this->user()->id)
            ->first();
    }

    // Method untuk mendapatkan data email dari auth_identities
    private function getEmailFromAuth()
    {
        $db = db_connect();
        $identity = $db->table('auth_identities')
            ->where('user_id', $this->user()->id)
            ->where('type', 'email_password')
            ->get()
            ->getRow();
        
        return $identity ? $identity->secret : '';
    }

    // Method untuk menampilkan modal edit profile (AJAX)
    public function editModal()
    {
        // Hanya response AJAX
        if (!$this->request->isAJAX()) {
            return redirect()->to('admin/profile');
        }

        $ap = $this->ap();
        
        // Jika profile belum ada, buat data default
        if (!$ap) {
            $ap = [
                'user_id' => $this->user()->id,
                'name' => $this->user()->username ?? 'Admin',
                'phone' => '',
                'profile_image' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $apId = $this->adminProfileModel->insert($ap);
            $ap['id'] = $apId;
        }

        // Ambil email dari auth_identities
        $ap['email'] = $this->getEmailFromAuth();

        $this->logActivity(
            $this->user()->id,
            $ap['id'] ?? null,
            'view_profile_edit_modal',
            'success',
            'Membuka modal edit profil'
        );

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'html' => view('admin/profile/modal_edit', ['ap' => $ap]),
                'ap' => $ap
            ],
            'csrf' => csrf_hash()
        ]);
    }

    // Method untuk menampilkan modal ubah password (AJAX)
    public function passwordModal()
    {
        // Hanya response AJAX
        if (!$this->request->isAJAX()) {
            return redirect()->to('admin/profile');
        }

        $ap = $this->ap();

        $this->logActivity(
            $this->user()->id,
            $ap['id'] ?? null,
            'view_password_modal',
            'success',
            'Membuka modal ubah password'
        );

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'html' => view('admin/profile/modal_password')
            ],
            'csrf' => csrf_hash()
        ]);
    }

    public function update()
    {
        $user = $this->user();
        $ap   = $this->ap();

        // Jika profile belum ada, buat dulu
        if (!$ap) {
            $ap = [
                'user_id' => $user->id,
                'name' => $user->username ?? 'Admin',
                'phone' => '',
                'profile_image' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $apId = $this->adminProfileModel->insert($ap);
            $ap['id'] = $apId;
        }

        // ==== VALIDASI ====
        $rules = [
            'name'                 => 'required|min_length[3]',
            'email'                => 'required|valid_email',
            'phone'                => 'permit_empty',
            'profile_image'        => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image' => 'permit_empty|in_list[0,1]',
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            $this->logActivity($user->id, $ap['id'], 'update_profile', 'failed', 'Validasi gagal', [
                'errors' => $validation->getErrors()
            ]);
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'errors' => $validation->getErrors(),
                    'csrf' => csrf_hash()
                ]);
            }
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // ==== PAYLOAD UNTUK ADMIN_PROFILES ====
        $data = [
            'name'       => (string) $this->request->getPost('name'),
            'phone'      => (string) ($this->request->getPost('phone') ?? ''),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // ==== FOTO PROFIL ====
        $pubDir = FCPATH . 'uploads/admin_profiles';
        if (!is_dir($pubDir)) {
            @mkdir($pubDir, 0775, true);
        }

        $profileImageChanged = false;
        if ($this->request->getPost('remove_profile_image') === '1' && !empty($ap['profile_image'])) {
            $oldPath = $pubDir . '/' . $ap['profile_image'];
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
            $data['profile_image'] = null;
            $profileImageChanged   = true;
        }

        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!empty($ap['profile_image'])) {
                $oldPath = $pubDir . '/' . $ap['profile_image'];
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $newName = $file->getRandomName();
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
            $profileImageChanged   = true;
        }

        // Update admin_profiles table
        $this->adminProfileModel->update($ap['id'], $data);

        // Update auth_identities table untuk email
        $email = (string) $this->request->getPost('email');
        $db = db_connect();
        $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->update([
                'name' => $data['name'],
                'secret' => $email,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // ==== LOG ====
        $changes = [];
        if ($profileImageChanged) $changes['profile_image'] = $data['profile_image'] ?? 'removed';
        $changes['name'] = $data['name'];
        $changes['phone'] = $data['phone'];
        $changes['email'] = $email;

        $this->logActivity($user->id, $ap['id'], 'update_profile', 'success', 'Update profil berhasil', $changes);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Profil berhasil diperbarui',
                'csrf' => csrf_hash()
            ]);
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

public function passwordUpdate()
{
    $user = $this->user();
    $ap   = $this->ap();

    $rules = [
        'current_password' => 'required',
        'new_password'     => 'required|min_length[8]',
        'pass_confirm'     => 'required|matches[new_password]',
    ];

    $isAjax = $this->request->isAJAX();

    if (! $this->validate($rules)) {
        $errors = $this->validator->getErrors();

        if ($isAjax) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal.',
                'errors' => $errors,
                'csrf'   => csrf_hash(),
            ])->setStatusCode(422);
        }

        $this->logActivity($user->id, $ap['id'] ?? null, 'password_update', 'failed', 'Validasi gagal', ['errors' => $errors]);
        return redirect()->back()->with('errors_password', $errors);
    }

    $current = (string) $this->request->getPost('current_password');
    $new     = (string) $this->request->getPost('new_password');

    $existingHash = $user->password_hash ?? $user->password ?? null;

    if (! $existingHash || ! password_verify($current, $existingHash)) {
        if ($isAjax) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Password lama tidak sesuai.',
                'csrf'    => csrf_hash(),
            ])->setStatusCode(400);
        }

        $this->logActivity($user->id, $ap['id'] ?? null, 'password_update', 'failed', 'Password lama salah');
        return redirect()->back()->with('error_password', 'Password lama tidak sesuai.');
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);

    try {
        // Update password di auth_identities (secret2)
        $db = db_connect();
        $db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->update([
                'secret2' => $newHash,
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        // Juga update di users table jika diperlukan
        if (property_exists($user, 'password_hash')) {
            $user->password_hash = $newHash;
        } else {
            $user->password = $newHash;
        }
        model('UserModel')->save($user);

        // Log activity untuk password update
        $this->logActivity($user->id, $ap['id'] ?? null, 'password_update', 'success', 'Password berhasil diperbarui - logout otomatis');

        if ($isAjax) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Password berhasil diperbarui. Anda akan logout otomatis dalam 3 detik.',
                'logout_redirect' => true, // FLAG UNTUK LOGOUT OTOMATIS
                'redirect_url' => site_url('logout'), // URL LOGOUT
                'csrf'    => csrf_hash(),
            ]);
        }

        // Untuk non-AJAX, langsung logout
        return redirect()->to('logout')->with('success_password', 'Password berhasil diperbarui. Silakan login kembali.');

    } catch (\Throwable $e) {
        $this->logActivity($user->id, $ap['id'] ?? null, 'password_update', 'error', 'Gagal simpan password: '.$e->getMessage());

        if ($isAjax) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan, gagal menyimpan password.',
                'csrf'    => csrf_hash(),
            ])->setStatusCode(500);
        }

        return redirect()->back()->with('error_password', 'Terjadi kesalahan, gagal menyimpan password.');
    }
}
    private function logActivity($userId = null, $adminId = null, $action = null, $status = null, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'admin_id'    => $adminId,
                'module'      => 'profile',
                'action'      => $action,
                'status'      => $status,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }
            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Admin Profile: ' . $e->getMessage());
        }
    }
}