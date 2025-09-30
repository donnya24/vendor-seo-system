<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;

class Profile extends BaseController
{
    protected $activityLogsModel;
    protected $notificationsModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
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
            'commission_type'      => 'required|in_list[percent,nominal]',
        ];

        $isVerified = ($vp['status'] ?? 'pending') === 'verified';
        if (!$isVerified) {
            $commissionType = $this->request->getPost('commission_type');
            if ($commissionType === 'percent') {
                $rules['requested_commission'] = 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]';
            } else {
                $rules['requested_commission_nominal'] = 'required';
            }
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
            'commission_type' => $this->request->getPost('commission_type'),
        ];

        // ==== KOMISI (jika belum verified) ====
        $commissionChanged = false;
        $commissionAction = '';
        if (!$isVerified) {
            $commissionType = $this->request->getPost('commission_type');
            
            if ($commissionType === 'percent') {
                $reqRaw = str_replace(',', '.', (string) $this->request->getPost('requested_commission'));
                $newCommission = is_numeric($reqRaw) ? (float) $reqRaw : null;
                $oldCommission = isset($vp['requested_commission']) ? (float) $vp['requested_commission'] : null;

                // DEBUG: Log nilai lama dan baru
                log_message('info', "KOMISI PERSEN - Lama: {$oldCommission}, Baru: {$newCommission}");

                if ($newCommission !== $oldCommission) {
                    $commissionChanged = true;
                    $data['requested_commission'] = $newCommission;
                    $data['requested_commission_nominal'] = null; // Reset nominal
                    $commissionAction = is_null($oldCommission) ? 'insert' : 'edit';
                }
            } else {
                $nominalRaw = $this->request->getPost('requested_commission_nominal');
                $nominalClean = preg_replace('/[^\d]/', '', $nominalRaw);
                $newCommissionNominal = is_numeric($nominalClean) ? (float) $nominalClean : null;
                $oldCommissionNominal = isset($vp['requested_commission_nominal']) ? (float) $vp['requested_commission_nominal'] : null;

                // DEBUG: Log nilai lama dan baru
                log_message('info', "KOMISI NOMINAL - Lama: {$oldCommissionNominal}, Baru: {$newCommissionNominal}");

                if ($newCommissionNominal !== $oldCommissionNominal) {
                    $commissionChanged = true;
                    $data['requested_commission_nominal'] = $newCommissionNominal;
                    $data['requested_commission'] = null; // Reset persen
                    $commissionAction = is_null($oldCommissionNominal) ? 'insert' : 'edit';
                }
            }
            
            $data['status'] = 'pending';
        }

        // ==== FOTO PROFIL ====
        $pubDir = FCPATH . 'uploads/vendor_profiles';
        if (!is_dir($pubDir)) {
            @mkdir($pubDir, 0775, true);
        }

        $profileImageChanged = false;
        if ($this->request->getPost('remove_profile_image') === '1' && !empty($vp['profile_image'])) {
            $oldPath = $pubDir . '/' . $vp['profile_image'];
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
            $data['profile_image'] = null;
            $profileImageChanged   = true;
        }

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
        if ($commissionChanged) {
            if ($data['commission_type'] === 'percent') {
                $changes['commission'] = $data['requested_commission'] . '%';
            } else {
                $changes['commission_nominal'] = 'Rp ' . number_format($data['requested_commission_nominal'], 0, ',', '.');
            }
        }
        if ($profileImageChanged) $changes['profile_image']  = $data['profile_image'] ?? 'removed';

        $this->logActivity($user->id, $vp['id'], 'update_profile', 'success', 'Update profil berhasil', $changes);

        // DEBUG: Log kondisi utama sebelum notifikasi
        log_message('info', "KONDISI NOTIFIKASI - isVerified: " . ($isVerified ? 'true' : 'false') . ", commissionChanged: " . ($commissionChanged ? 'true' : 'false'));

        // Notifikasi admin dan seoteam
        if (!$isVerified && $commissionChanged) {
            try {
                log_message('info', 'MEMANGGIL FUNGSI KIRIM NOTIFIKASI...');
                $this->sendCommissionNotification($user, $data + [
                    'requested_commission' => $data['requested_commission'] ?? null,
                    'requested_commission_nominal' => $data['requested_commission_nominal'] ?? null,
                    'commission_type' => $data['commission_type'],
                    'action' => $commissionAction
                ]);
                
                $logMessage = 'Mengajukan komisi baru: ';
                if ($data['commission_type'] === 'percent') {
                    $logMessage .= $data['requested_commission'] . '%';
                } else {
                    $logMessage .= 'Rp ' . number_format($data['requested_commission_nominal'], 0, ',', '.');
                }
                
                $this->logActivity($user->id, $vp['id'], 'request_commission', 'success', $logMessage);
            } catch (\Throwable $e) {
                $this->logActivity($user->id, $vp['id'], 'send_notification', 'error', 'Gagal kirim notifikasi: ' . $e->getMessage());
                log_message('error', 'Gagal mengirim notifikasi komisi: ' . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    // PERBAIKI: Fungsi notifikasi yang lebih robust dan detail loggingnya
    private function sendCommissionNotification($user, $vendorData)
    {
        $db = db_connect();
        
        // 1. Ambil SEMUA user dengan group 'admin' dan 'seoteam'
        $targetUsers = $db->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('group', ['admin', 'seoteam'])
            ->get()
            ->getResultArray();
        
        log_message('info', 'DITEMUKAN ' . count($targetUsers) . ' USER TARGET (ADMIN/SEOTEAM).');
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUsers)) {
            log_message('warning', 'Tidak ada user admin atau seoteam yang ditemukan untuk notifikasi komisi.');
            return;
        }
        
        // 2. Siapkan data notifikasi
        $commissionText = '';
        if ($vendorData['commission_type'] === 'percent') {
            $commissionText = $vendorData['requested_commission'] . '%';
        } else {
            $commissionText = 'Rp ' . number_format($vendorData['requested_commission_nominal'], 0, ',', '.');
        }
        
        $actionText = '';
        if ($vendorData['action'] === 'insert') {
            $actionText = 'mengajukan komisi';
        } elseif ($vendorData['action'] === 'edit') {
            $actionText = 'mengubah pengajuan komisi';
        }
        
        $title = 'Pengajuan/Perubahan Komisi Vendor';
        $message = 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                    ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                    ') ' . $actionText . ' ' . $commissionText . '.';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan array data untuk di-insert secara batch
        $notificationsToInsert = [];
        foreach ($targetUsers as $targetUser) {
            $notification = [
                'user_id'    => $targetUser['user_id'],
                'title'      => $title,
                'message'    => $message,
                'type'       => 'system',
                'is_read'    => 0,
                'created_at' => $now,
            ];
            
            // Set vendor_id untuk semua notifikasi
            $notification['vendor_id'] = $user->id;
            
            // Set seo_id jika user adalah seoteam
            if ($targetUser['group'] === 'seoteam') {
                $notification['seo_id'] = $targetUser['user_id'];
            }
            
            $notificationsToInsert[] = $notification;
        }

        // DEBUG: Log data yang akan diinsert
        log_message('debug', 'DATA NOTIF YANG AKAN DI-INSERT: ' . json_encode($notificationsToInsert));

        // 4. Insert semua notifikasi dalam satu kali query
        try {
            $db->table('notifications')->insertBatch($notificationsToInsert);
            log_message('info', 'SUKSES: Berhasil mengirim ' . count($notificationsToInsert) . ' notifikasi komisi.');
        } catch (\Throwable $e) {
            // Log jika terjadi error
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            // Lempar error agar bisa ditangkap di try-catch luar
            throw $e;
        }
    }

    public function password()
    {
        $vp = $this->vp();
        $this->logActivity($this->user()->id, $vp['id'] ?? null, 'view_password_form', 'success', 'Mengakses form ubah password');
        return view('vendoruser/profile/ubahpassword', ['page' => 'Ubah Password']);
    }

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

        $newHash = password_hash($new, PASSWORD_DEFAULT);

        try {
            if (property_exists($user, 'password_hash')) {
                $user->password_hash = $newHash;
            } else {
                $user->password = $newHash;
            }

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