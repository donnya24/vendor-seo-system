<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use App\Models\SeoProfilesModel;

class Profile extends BaseController
{
    protected $vendorProfilesModel;
    protected $notificationsModel;
    protected $userModel;
    protected $seoProfilesModel;

    private $vendorProfile;
    private $vendorId;
    private $isVerified;

    public function __construct()
    {
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
        $this->seoProfilesModel = new SeoProfilesModel();
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
        
        // Debug informasi awal
        log_message('info', "Memulai update profil vendor ID: {$this->vendorId}, User ID: {$user->id}");

        // Ambil semua data POST
        $postData = $this->request->getPost();
        
        // Bersihkan format nominal komisi SEBELUM validasi
        if (isset($postData['requested_commission_nominal'])) {
            $postData['requested_commission_nominal'] = preg_replace('/[^\d]/', '', $postData['requested_commission_nominal']);
        }

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
            
            $commissionType = $postData['commission_type'] ?? '';
            if ($commissionType === 'percent') {
                $rules['requested_commission'] = 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[100]';
                $rules['requested_commission_nominal'] = 'permit_empty';
            } else if ($commissionType === 'nominal') {
                $rules['requested_commission_nominal'] = 'required|numeric|greater_than[0]';
                $rules['requested_commission'] = 'permit_empty';
            }
        } else {
            // Untuk vendor yang sudah verified, commission_type tidak required
            $rules['commission_type'] = 'permit_empty|in_list[percent,nominal]';
        }

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        // Gunakan data yang sudah dibersihkan untuk validasi
        if (!$validation->run($postData)) {
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
            'business_name'   => (string) $postData['business_name'],
            'owner_name'      => (string) $postData['owner_name'],
            'whatsapp_number' => (string) $postData['whatsapp_number'],
            'phone'           => (string) ($postData['phone'] ?? ''),
        ];

        // ==== HANDLE COMMISSION DATA ====
        $commissionChanged = false;
        $oldCommissionType = $this->vendorProfile['commission_type'] ?? '';
        $oldCommissionValue = '';

        if (!$this->isVerified) {
            $commissionType = $postData['commission_type'];
            $data['commission_type'] = $commissionType;
            
            // Simpan nilai komisi lama untuk notifikasi
            if ($oldCommissionType === 'percent') {
                $oldCommissionValue = $this->vendorProfile['requested_commission'] ?? 0;
            } else {
                $oldCommissionValue = $this->vendorProfile['requested_commission_nominal'] ?? 0;
            }
            
            if ($commissionType === 'percent') {
                $reqRaw = str_replace(',', '.', (string) ($postData['requested_commission'] ?? ''));
                $newCommission = is_numeric($reqRaw) ? (float) $reqRaw : null;
                $oldCommission = isset($this->vendorProfile['requested_commission']) ? (float) $this->vendorProfile['requested_commission'] : null;

                if ($newCommission !== $oldCommission) {
                    $commissionChanged = true;
                    $data['requested_commission'] = $newCommission;
                    $data['requested_commission_nominal'] = null;
                    log_message('info', "Komisi percent berubah: {$oldCommission} -> {$newCommission}");
                }
            } else {
                // Data nominal sudah dibersihkan sebelumnya
                $newCommissionNominal = is_numeric($postData['requested_commission_nominal'] ?? '') ? (float) $postData['requested_commission_nominal'] : null;
                $oldCommissionNominal = isset($this->vendorProfile['requested_commission_nominal']) ? (float) $this->vendorProfile['requested_commission_nominal'] : null;

                if ($newCommissionNominal !== $oldCommissionNominal) {
                    $commissionChanged = true;
                    $data['requested_commission_nominal'] = $newCommissionNominal;
                    $data['requested_commission'] = null;
                    log_message('info', "Komisi nominal berubah: {$oldCommissionNominal} -> {$newCommissionNominal}");
                }
            }
            
            // Reset status ke pending jika ada perubahan komisi
            if ($commissionChanged) {
                $data['status'] = 'pending';
                $data['approved_at'] = null;
                $data['action_by'] = null;
                log_message('info', "Status direset ke pending karena perubahan komisi");
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
        if (($postData['remove_profile_image'] ?? '0') === '1' && !empty($this->vendorProfile['profile_image'])) {
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

        // ==== UPDATE DATABASE MENGGUNAKAN QUERY BUILDER ====
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('vendor_profiles');
            
            // Tambahkan updated_at
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Update menggunakan query builder langsung
            $builder->where('id', $this->vendorId);
            $success = $builder->update($data);

            if (!$success) {
                throw new \Exception('Gagal update data vendor');
            }

            log_message('info', "Update profil vendor berhasil, commissionChanged: " . ($commissionChanged ? 'YES' : 'NO'));

            // ==== CREATE NOTIFICATION JIKA ADA PERUBAHAN KOMISI ====
            if ($commissionChanged && !$this->isVerified) {
                log_message('info', "Memanggil createCommissionChangeNotification...");
                $this->createCommissionChangeNotification($oldCommissionType, $oldCommissionValue, $data);
            }

            // ==== LOG ACTIVITY ====
            $changes = [];
            if ($commissionChanged) {
                if ($data['commission_type'] === 'percent') {
                    $changes['commission'] = $data['requested_commission'] . '%';
                } else {
                    $changes['commission'] = 'Rp ' . number_format($data['requested_commission_nominal'], 0, ',', '.');
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

            return redirect()->back()->with('success', 'Profil berhasil diperbarui' . ($commissionChanged ? ' dan pengajuan komisi dikirim untuk verifikasi' : ''));

        } catch (\Throwable $e) {
            // Log error
            log_message('error', 'Error update profil: ' . $e->getMessage());
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

    /**
     * Membuat notifikasi untuk semua Admin dan SEO ketika komisi berubah
     */
    private function createCommissionChangeNotification(string $oldCommissionType, $oldCommissionValue, array $newData): void
    {
        try {
            $db = \Config\Database::connect();
            
            // Format nilai komisi lama dan baru
            $oldCommissionFormatted = $this->formatCommissionValue($oldCommissionType, $oldCommissionValue);
            $newCommissionFormatted = $this->formatCommissionValue($newData['commission_type'], 
                $newData['commission_type'] === 'percent' ? ($newData['requested_commission'] ?? 0) : ($newData['requested_commission_nominal'] ?? 0));

            $vendorName = $this->vendorProfile['business_name'] ?? 'Vendor';
            $vendorId = $this->vendorId;

            // Debug informasi
            log_message('info', "Membuat notifikasi perubahan komisi untuk vendor: {$vendorName}");
            log_message('info', "Komisi: {$oldCommissionFormatted} -> {$newCommissionFormatted}");

            // 1. Dapatkan semua admin users yang aktif
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1) // Pastikan user aktif
                ->get()
                ->getResultArray();
            
            log_message('info', "Found " . count($adminUsers) . " active admin users for commission notification");

            // 2. Dapatkan semua SEO users yang aktif
            // Metode 1: Melalui auth_groups_users (jika SEO users memiliki grup 'seo')
            $seoUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'seo')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();
            
            // Jika tidak ada SEO users melalui auth_groups, coba metode 2
            if (empty($seoUsers)) {
                log_message('info', "No SEO users found through auth_groups, trying seo_profiles table");
                
                // Metode 2: Melalui seo_profiles table
                $seoProfiles = $this->seoProfilesModel
                    ->select('user_id')
                    ->where('status', 'active') // Asumsi status aktif
                    ->findAll();
                
                // Konversi ke format yang sama dengan metode 1
                $seoUsers = array_map(function($profile) {
                    return ['user_id' => $profile['user_id']];
                }, $seoProfiles);
                
                // Filter hanya user yang aktif di tabel users
                $activeSeoUserIds = $db->table('users')
                    ->select('id')
                    ->whereIn('id', array_column($seoUsers, 'user_id'))
                    ->where('active', 1)
                    ->get()
                    ->getResultArray();
                
                // Filter seoUsers untuk hanya menyertakan user yang aktif
                $activeSeoUserIds = array_column($activeSeoUserIds, 'id');
                $seoUsers = array_filter($seoUsers, function($user) use ($activeSeoUserIds) {
                    return in_array($user['user_id'], $activeSeoUserIds);
                });
                
                // Re-index array
                $seoUsers = array_values($seoUsers);
            }
            
            log_message('info', "Found " . count($seoUsers) . " active SEO users for commission notification");
            if (empty($seoUsers)) {
                log_message('warning', "No active SEO users found. Check if group name 'seo' is correct and users are active.");
            }

            // Siapkan data notifikasi
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            // Buat pesan notifikasi
            $title = 'Pengajuan Perubahan Komisi';
            $message = "📝 Vendor {$vendorName} mengajukan perubahan komisi dari {$oldCommissionFormatted} menjadi {$newCommissionFormatted}.";
            $message .= "\n\nDetail Pengajuan:";
            $message .= "\n• Vendor: {$vendorName}";
            $message .= "\n• Komisi Lama: {$oldCommissionFormatted}";
            $message .= "\n• Komisi Baru: {$newCommissionFormatted}";
            $message .= "\n• Status: Menunggu Verifikasi";

            // Notifikasi untuk semua ADMIN
            foreach ($adminUsers as $admin) {
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'vendor_id' => $this->vendorId,
                    'type' => 'commission_change',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Notifikasi untuk semua SEO
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => $seo['user_id'],
                    'vendor_id' => $this->vendorId,
                    'type' => 'commission_change',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Insert semua notifikasi
            if (!empty($notifications)) {
                $this->notificationsModel->insertBatch($notifications);
                
                // Log untuk debugging
                log_message('info', "Created commission change notifications: " . count($notifications) . " notifications sent");
                
                // Log aktivitas notifikasi
                if (function_exists('log_activity_auto')) {
                    log_activity_auto('notification', 'Notifikasi pengajuan perubahan komisi dibuat untuk admin dan SEO', [
                        'module' => 'vendor_profile',
                        'vendor_id' => $this->vendorId,
                        'target_users' => count($notifications),
                        'old_commission' => $oldCommissionFormatted,
                        'new_commission' => $newCommissionFormatted
                    ]);
                }
                
                return;
            }

            log_message('warning', "No notifications were created for commission change");

        } catch (\Throwable $e) {
            // Log error tanpa mengganggu flow utama
            log_message('error', 'Gagal membuat notifikasi perubahan komisi: ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Gagal membuat notifikasi perubahan komisi: ' . $e->getMessage(), [
                    'module' => 'vendor_profile',
                    'vendor_id' => $this->vendorId
                ]);
            }
        }
    }

    /**
     * Format nilai komisi untuk ditampilkan
     */
    private function formatCommissionValue(string $type, $value): string
    {
        if (empty($value) || $value == 0) {
            return 'Belum diatur';
        }
        
        if ($type === 'percent') {
            return number_format((float)$value, 1) . '%';
        } else {
            return 'Rp ' . number_format((float)$value, 0, ',', '.');
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