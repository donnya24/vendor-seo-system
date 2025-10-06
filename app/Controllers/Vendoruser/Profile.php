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
    protected $vendorProfilesModel;

    private $vendorProfile;
    private $vendorId;
    private $isVerified;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->initVendor();
    }

    private function initVendor(): void
    {
        $user = service('auth')->user();
        $this->vendorProfile = $this->vendorProfilesModel
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? 0;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';
    }

    private function withVendorData(array $data = []): array
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function user()
    {
        return service('auth')->user();
    }

    public function edit()
    {
        if (! $this->vendorId) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dahulu.');
        }

        // Log aktivitas menggunakan helper
        if (function_exists('log_activity_auto')) {
            log_activity_auto('view_form', 'Membuka form edit profil vendor', [
                'module' => 'vendor_profile',
                'vendor_id' => $this->vendorId
            ]);
        }

        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Edit Profil',
            'content_view' => 'vendoruser/profile/edit',
            'content_data' => [
                'page' => 'Profile',
                'vp'   => $this->vendorProfile,
            ],
        ]));
    }

    public function update()
    {
        if (! $this->vendorId) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada.');
        }

        $user = $this->user();

        // ==== VALIDASI DASAR ====
        $rules = [
            'business_name'        => 'required|min_length[3]|max_length[150]',
            'owner_name'           => 'required|min_length[3]|max_length[100]',
            'whatsapp_number'      => 'required|max_length[30]',
            'phone'                => 'permit_empty|max_length[30]',
            'profile_image'        => 'permit_empty|max_size[profile_image,2048]|is_image[profile_image]|mime_in[profile_image,image/jpg,image/jpeg,image/png,image/webp,image/gif]',
            'remove_profile_image' => 'permit_empty|in_list[0,1]',
        ];

        // ==== VALIDASI KOMISI HANYA UNTUK VENDOR BELUM VERIFIED ====
        if (!$this->isVerified) {
            $rules['commission_type'] = 'required|in_list[percent,nominal]';
            
            $commissionType = $this->request->getPost('commission_type');
            if ($commissionType === 'percent') {
                $rules['requested_commission'] = 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]';
                $rules['requested_commission_nominal'] = 'permit_empty';
            } else if ($commissionType === 'nominal') {
                $rules['requested_commission_nominal'] = 'required';
                $rules['requested_commission'] = 'permit_empty';
            }
        } else {
            // Untuk vendor yang sudah verified, commission_type tidak required
            $rules['commission_type'] = 'permit_empty|in_list[percent,nominal]';
        }

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->withRequest($this->request)->run()) {
            // Log error validasi
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Validasi update profil gagal: ' . json_encode($validation->getErrors()), [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId
                ]);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('errors', $validation->getErrors())
                ->with('error', 'Terjadi kesalahan validasi. Silakan periksa kembali data Anda.');
        }

        // ==== PREPARE DATA ====
        $data = [
            'business_name'   => (string) $this->request->getPost('business_name'),
            'owner_name'      => (string) $this->request->getPost('owner_name'),
            'whatsapp_number' => (string) $this->request->getPost('whatsapp_number'),
            'phone'           => (string) ($this->request->getPost('phone') ?? ''),
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // ==== HANDLE COMMISSION DATA ====
        $commissionChanged = false;
        $commissionAction = '';
        $commissionData = [];

        if (!$this->isVerified) {
            $commissionType = $this->request->getPost('commission_type');
            $data['commission_type'] = $commissionType;
            
            if ($commissionType === 'percent') {
                $reqRaw = str_replace(',', '.', (string) $this->request->getPost('requested_commission'));
                $newCommission = is_numeric($reqRaw) ? (float) $reqRaw : null;
                $oldCommission = isset($this->vendorProfile['requested_commission']) ? (float) $this->vendorProfile['requested_commission'] : null;

                if ($newCommission !== $oldCommission) {
                    $commissionChanged = true;
                    $data['requested_commission'] = $newCommission;
                    $data['requested_commission_nominal'] = null;
                    $commissionAction = empty($oldCommission) ? 'insert' : 'edit';
                    $commissionData = [
                        'type' => 'percent',
                        'value' => $newCommission,
                        'old_value' => $oldCommission
                    ];
                }
            } else {
                $nominalRaw = $this->request->getPost('requested_commission_nominal');
                $nominalClean = preg_replace('/[^\d]/', '', $nominalRaw);
                $newCommissionNominal = is_numeric($nominalClean) ? (float) $nominalClean : null;
                $oldCommissionNominal = isset($this->vendorProfile['requested_commission_nominal']) ? (float) $this->vendorProfile['requested_commission_nominal'] : null;

                if ($newCommissionNominal !== $oldCommissionNominal) {
                    $commissionChanged = true;
                    $data['requested_commission_nominal'] = $newCommissionNominal;
                    $data['requested_commission'] = null;
                    $commissionAction = empty($oldCommissionNominal) ? 'insert' : 'edit';
                    $commissionData = [
                        'type' => 'nominal',
                        'value' => $newCommissionNominal,
                        'old_value' => $oldCommissionNominal
                    ];
                }
            }
            
            // Reset status ke pending jika ada perubahan komisi
            if ($commissionChanged) {
                $data['status'] = 'pending';
            }
        }

        // ==== HANDLE PROFILE IMAGE ====
        $profileImageChanged = false;
        $pubDir = FCPATH . 'uploads/vendor_profiles';
        
        // Create directory if not exists
        if (!is_dir($pubDir)) {
            @mkdir($pubDir, 0775, true);
        }

        // Handle remove profile image
        if ($this->request->getPost('remove_profile_image') === '1' && !empty($this->vendorProfile['profile_image'])) {
            $oldPath = $pubDir . '/' . $this->vendorProfile['profile_image'];
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
            $data['profile_image'] = null;
            $profileImageChanged = true;
        }

        // Handle new profile image upload
        $file = $this->request->getFile('profile_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Remove old image if exists
            if (!empty($this->vendorProfile['profile_image'])) {
                $oldPath = $pubDir . '/' . $this->vendorProfile['profile_image'];
                if (is_file($oldPath)) {
                    @unlink($oldPath);
                }
            }
            
            $newName = $file->getRandomName();
            $file->move($pubDir, $newName);
            $data['profile_image'] = $newName;
            $profileImageChanged = true;
        }

        // ==== UPDATE DATABASE ====
        try {
            $this->vendorProfilesModel->update($this->vendorId, $data);

            // ==== LOG ACTIVITY ====
            $changes = [];
            if ($commissionChanged) {
                if ($commissionData['type'] === 'percent') {
                    $changes['commission'] = $commissionData['value'] . '%';
                    if (!empty($commissionData['old_value'])) {
                        $changes['commission_old'] = $commissionData['old_value'] . '%';
                    }
                } else {
                    $changes['commission'] = 'Rp ' . number_format($commissionData['value'], 0, ',', '.');
                    if (!empty($commissionData['old_value'])) {
                        $changes['commission_old'] = 'Rp ' . number_format($commissionData['old_value'], 0, ',', '.');
                    }
                }
            }
            if ($profileImageChanged) {
                $changes['profile_image'] = isset($data['profile_image']) ? 'updated' : 'removed';
            }

            // Log success menggunakan helper
            if (function_exists('log_activity_auto')) {
                $description = 'Update profil vendor berhasil';
                if ($commissionChanged) {
                    $description .= ' dengan perubahan komisi';
                }
                if ($profileImageChanged) {
                    $description .= ' dengan perubahan foto profil';
                }
                
                log_activity_auto('update', $description, [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId,
                    'changes' => $changes
                ]);
            }

            // ==== SEND NOTIFICATION FOR COMMISSION CHANGE ====
            if (!$this->isVerified && $commissionChanged) {
                $this->sendCommissionNotification($user, $data + [
                    'requested_commission' => $data['requested_commission'] ?? null,
                    'requested_commission_nominal' => $data['requested_commission_nominal'] ?? null,
                    'commission_type' => $data['commission_type'],
                    'action' => $commissionAction
                ]);

                // Log commission request
                if (function_exists('log_activity_auto')) {
                    $logMessage = 'Mengajukan komisi: ';
                    if ($data['commission_type'] === 'percent') {
                        $logMessage .= $data['requested_commission'] . '%';
                    } else {
                        $logMessage .= 'Rp ' . number_format($data['requested_commission_nominal'], 0, ',', '.');
                    }
                    
                    log_activity_auto('create', $logMessage, [
                        'module' => 'vendor_profile',
                        'vendor_id' => $this->vendorId,
                        'commission_type' => $data['commission_type'],
                        'commission_value' => $data['commission_type'] === 'percent' ? $data['requested_commission'] : $data['requested_commission_nominal']
                    ]);
                }
            }

            return redirect()->back()->with('success', 'Profil berhasil diperbarui' . ($commissionChanged ? ' dan pengajuan komisi dikirim untuk verifikasi' : ''));

        } catch (\Throwable $e) {
            // Log error
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Gagal update profil: ' . $e->getMessage(), [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId
                ]);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    private function sendCommissionNotification($user, $vendorData)
    {
        $db = db_connect();
        
        try {
            // Get admin and seoteam users
            $targetUsers = $db->table('auth_groups_users')
                ->select('user_id, group')
                ->whereIn('group', ['admin', 'seoteam'])
                ->get()
                ->getResultArray();
            
            if (empty($targetUsers)) {
                log_message('warning', 'Tidak ada user admin atau seoteam yang ditemukan untuk notifikasi komisi.');
                return;
            }
            
            // Prepare notification data
            $commissionText = '';
            if ($vendorData['commission_type'] === 'percent') {
                $commissionText = $vendorData['requested_commission'] . '%';
            } else {
                $commissionText = 'Rp ' . number_format($vendorData['requested_commission_nominal'], 0, ',', '.');
            }
            
            $actionText = $vendorData['action'] === 'insert' ? 'mengajukan komisi' : 'mengubah pengajuan komisi';
            
            $title = 'Pengajuan Komisi Vendor';
            $message = 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                        ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                        ') ' . $actionText . ' ' . $commissionText . '. Silakan verifikasi pengajuan ini.';

            $now = date('Y-m-d H:i:s');

            // Prepare batch insert
            $notificationsToInsert = [];
            foreach ($targetUsers as $targetUser) {
                $notification = [
                    'user_id'    => $targetUser['user_id'],
                    'vendor_id'  => $this->vendorId,
                    'title'      => $title,
                    'message'    => $message,
                    'type'       => 'system',
                    'is_read'    => 0,
                    'created_at' => $now,
                ];
                
                // Set seo_id for seoteam users
                if ($targetUser['group'] === 'seoteam') {
                    $notification['seo_id'] = $targetUser['user_id'];
                }
                
                $notificationsToInsert[] = $notification;
            }

            // Insert notifications
            $db->table('notifications')->insertBatch($notificationsToInsert);
            
            log_message('info', 'Berhasil mengirim ' . count($notificationsToInsert) . ' notifikasi komisi.');

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi komisi: ' . $e->getMessage());
            throw $e;
        }
    }

    public function password()
    {
        if (! $this->vendorId) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dahulu.');
        }

        // Log activity
        if (function_exists('log_activity_auto')) {
            log_activity_auto('view_form', 'Membuka form ubah password', [
                'module' => 'vendor_profile',
                'vendor_id' => $this->vendorId
            ]);
        }

        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Ubah Password',
            'content_view' => 'vendoruser/profile/ubahpassword',
            'content_data' => [
                'page' => 'Ubah Password',
            ],
        ]));
    }

    public function passwordUpdate()
    {
        if (! $this->vendorId) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized'])
                : redirect()->back()->with('error', 'Unauthorized.');
        }

        $user = $this->user();
        $isAjax = $this->request->isAJAX();

        $rules = [
            'current_password' => 'required',
            'new_password'     => 'required|min_length[8]',
            'pass_confirm'     => 'required|matches[new_password]',
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();

            // Log failed validation
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Validasi ubah password gagal', [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId,
                    'errors' => $errors
                ]);
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Validasi gagal.',
                    'errors' => $errors,
                    'csrf'   => csrf_hash(),
                ])->setStatusCode(422);
            }

            return redirect()->back()->with('errors_password', $errors);
        }

        $current = (string) $this->request->getPost('current_password');
        $new     = (string) $this->request->getPost('new_password');

        $existingHash = $user->password_hash ?? $user->password ?? null;

        if (! $existingHash || ! password_verify($current, $existingHash)) {
            // Log wrong current password
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Password lama tidak sesuai saat mencoba ubah password', [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId
                ]);
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Password lama tidak sesuai.',
                    'csrf'    => csrf_hash(),
                ])->setStatusCode(400);
            }

            return redirect()->back()->with('error_password', 'Password lama tidak sesuai.');
        }

        try {
            $newHash = password_hash($new, PASSWORD_DEFAULT);

            if (property_exists($user, 'password_hash')) {
                $user->password_hash = $newHash;
            } else {
                $user->password = $newHash;
            }

            model('UserModel')->save($user);

            // Log success
            if (function_exists('log_activity_auto')) {
                log_activity_auto('update', 'Password berhasil diubah', [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId
                ]);
            }

            if ($isAjax) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Password berhasil diperbarui.',
                    'csrf'    => csrf_hash(),
                ]);
            }

            return redirect()->back()->with('success_password', 'Password berhasil diperbarui.');

        } catch (\Throwable $e) {
            // Log error
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Gagal mengubah password: ' . $e->getMessage(), [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId
                ]);
            }

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
}