<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Profile extends BaseController
{
    protected $activityLogsModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
    }

    private function user()
    {
        return service('auth')->user();
    }

    private function vp(): ?array
    {
        return (new VendorProfilesModel())
            ->where('user_id', (int) $this->user()->id)
            ->first();
    }

    public function edit()
    {
        $vp = $this->vp();

        $this->logActivity(
            $this->user()->id,
            $vp['id'] ?? null,
            'view_profile_edit',
            'success',
            'Mengakses form edit profil'
        );

        return view('vendoruser/profile/edit', ['vp' => $vp]);
    }

    public function update()
    {
        $user = $this->user();
        $vp   = $this->vp();

        if (!$vp) {
            $this->logActivity($user->id, null, 'update_profile', 'failed', 'Profil tidak ditemukan');
            return redirect()->back()->with('error', 'Profil tidak ditemukan');
        }

        // ==== VALIDASI ====
        $rules = [
            'business_name'        => 'required|min_length[3]',
            'owner_name'           => 'required|min_length[3]',
            'whatsapp_number'      => 'required',
            'phone'                => 'permit_empty',
            'profile_image'        => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image' => 'permit_empty|in_list[0,1]',
        ];

        $isVerified = ($vp['status'] ?? 'pending') === 'verified';
        if (!$isVerified) {
            $rules['requested_commission'] = 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]';
        }

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            $this->logActivity($user->id, $vp['id'], 'update_profile', 'failed', 'Validasi gagal', [
                'errors' => $validation->getErrors()
            ]);
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        // ==== PAYLOAD ====
        $data = [
            'business_name'   => (string) $this->request->getPost('business_name'),
            'owner_name'      => (string) $this->request->getPost('owner_name'),
            'whatsapp_number' => (string) $this->request->getPost('whatsapp_number'),
            'phone'           => (string) ($this->request->getPost('phone') ?? ''),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // ==== KOMISI (jika belum verified) ====
        $commissionChanged = false;
        if (!$isVerified) {
            $reqRaw        = str_replace(',', '.', (string) $this->request->getPost('requested_commission'));
            $newCommission = is_numeric($reqRaw) ? (float) $reqRaw : null;

            if ($newCommission != ($vp['requested_commission'] ?? null)) {
                $commissionChanged            = true;
                $data['requested_commission'] = $newCommission;
            }
            $data['status'] = 'pending';
        }

        // ==== FOTO PROFIL ====
        $pubDir = FCPATH . 'uploads/vendor_profiles';
        if (!is_dir($pubDir)) {
            @mkdir($pubDir, 0775, true);
        }

        $profileImageChanged = false;

        // Hapus jika diminta
        if ($this->request->getPost('remove_profile_image') === '1' && !empty($vp['profile_image'])) {
            $oldPath = $pubDir . '/' . $vp['profile_image'];
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
            $data['profile_image'] = null;
            $profileImageChanged   = true;
        }

        // Upload baru
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!empty($vp['profile_image'])) {
                $oldPath = $pubDir . '/' . $vp['profile_image'];
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            $newName = $file->getRandomName();
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
            $profileImageChanged   = true;
        }

        (new VendorProfilesModel())->update($vp['id'], $data);

        // ==== LOG ====
        $changes = [];
        if ($commissionChanged)   $changes['commission']     = $data['requested_commission'] ?? null;
        if ($profileImageChanged) $changes['profile_image']  = $data['profile_image'] ?? 'removed';

        $this->logActivity($user->id, $vp['id'], 'update_profile', 'success', 'Update profil berhasil', $changes);

        // Notifikasi admin
        if (!$isVerified && $commissionChanged) {
            try {
                $this->sendVerificationNotification($user, $data + [
                    'requested_commission' => $data['requested_commission'] ?? null
                ]);
                $this->logActivity($user->id, $vp['id'], 'request_commission', 'success', 'Mengajukan komisi baru', [
                    'commission' => $data['requested_commission'] ?? null
                ]);
            } catch (\Throwable $e) {
                $this->logActivity($user->id, $vp['id'], 'send_notification', 'error', 'Gagal kirim notifikasi: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    private function sendVerificationNotification($user, $vendorData)
    {
        $db = db_connect();
        if ($db->tableExists('notifications')) {
            $db->table('notifications')->insert([
                'user_id'    => 1, // ID admin
                'title'      => 'Pengajuan/Perubahan Komisi Vendor',
                'message'    => 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                                ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                                ') mengajukan/ubah komisi ' . ($vendorData['requested_commission'] ?? '-') . '%.',
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function password()
    {
        $vp = $this->vp();
        $this->logActivity($this->user()->id, $vp['id'] ?? null, 'view_password_form', 'success', 'Mengakses form ubah password');
        return view('vendoruser/profile/ubahpassword', ['page' => 'Ubah Password']);
    }

    // ========= Password Update =========
    public function passwordUpdate()
{
    $user = $this->user();
    $vp   = $this->vp();

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

        $this->logActivity($user->id, $vp['id'] ?? null, 'password_update', 'failed', 'Validasi gagal', ['errors' => $errors]);
        return redirect()->back()->with('errors_password', $errors);
    }

    $current = (string) $this->request->getPost('current_password');
    $new     = (string) $this->request->getPost('new_password');

    // Ambil hash password yang ada (dukungan 2 nama field umum)
    $existingHash = $user->password_hash ?? $user->password ?? null;

    if (! $existingHash || ! password_verify($current, $existingHash)) {
        if ($isAjax) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Password lama tidak sesuai.',
                'csrf'    => csrf_hash(),
            ])->setStatusCode(400);
        }

        $this->logActivity($user->id, $vp['id'] ?? null, 'password_update', 'failed', 'Password lama salah');
        return redirect()->back()->with('error_password', 'Password lama tidak sesuai.');
    }

    // Hash baru & simpan
    $newHash = password_hash($new, PASSWORD_DEFAULT);

    try {
        if (property_exists($user, 'password_hash')) {
            $user->password_hash = $newHash;
        } else {
            $user->password = $newHash;
        }

        // Ganti `UserModel` kalau nama model user Anda berbeda
        model('UserModel')->save($user);

        $this->logActivity($user->id, $vp['id'] ?? null, 'password_update', 'success', 'Password berhasil diperbarui');

        if ($isAjax) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Password berhasil diperbarui.',
                'csrf'    => csrf_hash(),
            ]);
        }

        return redirect()->back()->with('success_password', 'Password berhasil diperbarui.');
    } catch (\Throwable $e) {
        $this->logActivity($user->id, $vp['id'] ?? null, 'password_update', 'error', 'Gagal simpan password: '.$e->getMessage());

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

    private function logActivity($userId, $vendorId, $action, $status, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'vendor_id'   => $vendorId,
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
            log_message('error', 'Failed to log activity in Profile: ' . $e->getMessage());
        }
    }
}
