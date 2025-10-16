<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\VendorProfilesModel;
use App\Models\IdentityModel;
use App\Models\ActivityLogsModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Database\Exceptions\DatabaseException;

class UserVendor extends BaseAdminController
{
    protected $users;
    protected $db;
    protected $vendorModel;
    protected $identityModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->users         = service('auth')->getProvider();
        $this->db            = db_connect();
        $this->vendorModel   = new VendorProfilesModel();
        $this->identityModel = new IdentityModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    // ========== LIST ==========
    public function index()
    {
        // Log activity akses halaman user vendor
        $this->logActivity(
            'view_user_vendor',
            'Mengakses halaman manajemen user vendor'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();
        
        // Query untuk VENDOR - dengan semua field termasuk action_by
        $users = $this->db->table('users u')
            ->select('u.id, u.username, 
                    vp.business_name, vp.owner_name, vp.phone, vp.whatsapp_number,
                    vp.status as vendor_status, vp.commission_type, 
                    vp.requested_commission, vp.requested_commission_nominal,
                    vp.action_by, vp.approved_at, vp.updated_at,
                    ai.secret as email')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->where('agu.group', 'vendor')
            ->orderBy('u.id', 'DESC')
            ->get()
            ->getResultArray();
        
        $users = array_map(function ($user) {
            // Tentukan is_verified berdasarkan status
            $isVerified = ($user['vendor_status'] ?? 'pending') === 'verified';
            
            // Format komisi dengan benar
            $commissionDisplay = '-';
            if ($user['commission_type'] === 'percent' && $user['requested_commission'] !== null) {
                $commissionDisplay = number_format($user['requested_commission'], 1) . '%';
            } elseif ($user['commission_type'] === 'nominal' && $user['requested_commission_nominal'] !== null) {
                $commissionDisplay = 'Rp ' . number_format($user['requested_commission_nominal'], 0, ',', '.');
            }
            
            // PERBAIKAN: Format action by - ambil nama user dari berbagai profile tables
            $actionByDisplay = '-';
            $actionDate = null;
            
            if (!empty($user['action_by'])) {
                // Ambil nama user yang melakukan aksi
                $actionByDisplay = $this->getUserNameById($user['action_by']);
                
                // Tentukan tanggal aksi berdasarkan status
                if ($user['vendor_status'] === 'verified' && !empty($user['approved_at'])) {
                    $actionDate = $user['approved_at'];
                } else {
                    $actionDate = $user['updated_at'];
                }
            }
            
            return [
                'id' => $user['id'],
                'username' => $user['username'],
                'business_name' => $user['business_name'] ?? '-',
                'owner_name' => $user['owner_name'] ?? '-',
                'phone' => $user['phone'] ?? '-',
                'whatsapp_number' => $user['whatsapp_number'] ?? '-',
                'email' => $user['email'] ?? '-',
                'vendor_status' => $user['vendor_status'] ?? 'pending',
                'commission_type' => $user['commission_type'] ?? 'nominal',
                'requested_commission' => $user['requested_commission'] ?? null,
                'requested_commission_nominal' => $user['requested_commission_nominal'] ?? null,
                'commission_display' => $commissionDisplay,
                'is_verified' => $isVerified,
                'action_by' => $user['action_by'],
                'action_by_display' => $actionByDisplay, // ✅ Sekarang menampilkan nama, bukan ID
                'action_date' => $actionDate,
                'groups' => ['vendor']
            ];
        }, $users);

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/uservendor/index', array_merge([
            'page'  => 'Users Vendor',
            'users' => $users,
        ], $commonData));
    }

    // ========== CREATE ==========
    public function create()
    {
        // Log activity akses form create user vendor
        $this->logActivity(
            'view_create_user_vendor',
            'Mengakses form create user vendor'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Handle AJAX request untuk modal - return HTML langsung
        if ($this->request->isAJAX()) {
            return view('admin/uservendor/modal_create');
        }

        // fallback untuk non-AJAX
        // Merge dengan common data (termasuk notifikasi)
        return view('admin/uservendor/create', array_merge([
            'page' => 'Users Vendor',
        ], $commonData));
    }

    // ========== STORE ==========
    public function store()
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                // Debug: Lihat semua data yang dikirim
                log_message('debug', '=== ALL POST DATA ===');
                log_message('debug', print_r($this->request->getPost(), true));
                log_message('debug', '=====================');
                
                $username = trim((string) $this->request->getPost('username'));
                $email    = trim((string) $this->request->getPost('email'));
                $password = (string) $this->request->getPost('password');
                
                // Validasi input required
                if (empty($username) || empty($email) || empty($password)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username, email, dan password wajib diisi'
                    ]);
                }

                // Validasi password
                if (strlen($password) < 8) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password minimal 8 karakter'
                    ]);
                }

                // Validasi konfirmasi password
                $password_confirm = $this->request->getPost('password_confirm');
                if ($password !== $password_confirm) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Konfirmasi password tidak sama'
                    ]);
                }

                // Cek duplikasi username
                $existingUser = $this->users->where('username', $username)->first();
                if ($existingUser) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username sudah digunakan',
                        'field' => 'username'
                    ]);
                }

                // Cek duplikasi email
                $existingEmail = $this->identityModel->where('secret', $email)->first();
                if ($existingEmail) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Email sudah digunakan',
                        'field' => 'email'
                    ]);
                }

                // buat user dasar
                $entity = new ShieldUser(['username' => $username]);
                $userId = $this->users->insert($entity, true);
                if (! $userId) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal membuat user'
                    ]);
                }

                // PERBAIKAN: Update status user menjadi active langsung setelah insert
                $updateData = [
                    'status' => 'active',
                    'active' => 1,
                    'last_active' => date('Y-m-d H:i:s')
                ];
                
                $this->users->update($userId, $updateData);
                log_message('debug', 'User status updated to active: ' . json_encode($updateData));

                // buat email-password identity
                $this->identityModel->insert([
                    'user_id'    => (int) $userId,
                    'type'       => 'email_password',
                    'secret'     => $email,
                    'secret2'    => password_hash($password, PASSWORD_DEFAULT),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // set grup tunggal
                $this->setSingleGroup((int) $userId, 'vendor');

                // vendor profile
                $businessName = trim((string) $this->request->getPost('business_name'));
                $ownerName    = trim((string) $this->request->getPost('owner_name'));
                $phone        = trim((string) $this->request->getPost('phone'));
                $whatsapp     = trim((string) $this->request->getPost('whatsapp_number'));
                $vendorStatus   = $this->request->getPost('vendor_status') ?? 'pending';
                $commissionType = $this->request->getPost('commission_type') ?? 'nominal';
                
                // Debug: Data komisi yang diterima
                log_message('debug', 'Commission Data Received:');
                log_message('debug', 'Type: ' . $commissionType);
                log_message('debug', 'Percent Value: ' . $this->request->getPost('requested_commission'));
                log_message('debug', 'Nominal Value: ' . $this->request->getPost('requested_commission_nominal'));
                
                // Handle komisi
                $requestedCommission = $this->request->getPost('requested_commission');
                $requestedCommissionNominal = $this->request->getPost('requested_commission_nominal');
                
                // Konversi ke float jika ada nilai
                $finalCommission = null;
                $finalCommissionNominal = null;
                
                if ($commissionType === 'percent' && $requestedCommission !== null && $requestedCommission !== '') {
                    $commissionValue = (float) $requestedCommission;
                    if ($commissionValue < 0 || $commissionValue > 100) {
                        // Rollback user creation
                        $this->users->delete($userId);
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Persentase komisi harus antara 0-100%'
                        ]);
                    }
                    $finalCommission = $commissionValue;
                } 
                elseif ($commissionType === 'nominal' && $requestedCommissionNominal !== null && $requestedCommissionNominal !== '') {
                    $finalCommissionNominal = (float) $requestedCommissionNominal;
                }

                $vendorData = [
                    'user_id'                   => $userId,
                    'business_name'             => $businessName,
                    'owner_name'                => $ownerName,
                    'phone'                     => $phone,
                    'whatsapp_number'           => $whatsapp,
                    'status'                    => $vendorStatus,
                    'commission_type'           => $commissionType,
                    'requested_commission'      => $finalCommission,
                    'requested_commission_nominal' => $finalCommissionNominal,
                    'created_at'                => date('Y-m-d H:i:s'),
                    'updated_at'                => date('Y-m-d H:i:s'),
                ];

                // Debug: Data yang akan diinsert
                log_message('debug', 'Vendor Data to Insert: ' . json_encode($vendorData));

                $insertResult = $this->vendorModel->insert($vendorData);
                
                if (!$insertResult) {
                    // Rollback user creation jika gagal insert vendor profile
                    $this->users->delete($userId);
                    $errors = $this->vendorModel->errors();
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal membuat profil vendor: ' . implode(', ', $errors)
                    ]);
                }
                
                // Debug: Cek data setelah insert
                $insertedData = $this->vendorModel->where('user_id', $userId)->first();
                log_message('debug', 'Data after insert: ' . json_encode($insertedData));
                
                // Debug: Verifikasi status user
                $userAfterUpdate = $this->users->find($userId);
                log_message('debug', 'User after update: ' . json_encode([
                    'id' => $userAfterUpdate->id,
                    'username' => $userAfterUpdate->username,
                    'status' => $userAfterUpdate->status,
                    'active' => $userAfterUpdate->active,
                    'last_active' => $userAfterUpdate->last_active
                ]));

                // Log activity create user vendor
                $this->logActivity(
                    'create_user_vendor',
                    'Membuat user vendor baru: ' . $username,
                    [
                        'user_id' => $userId,
                        'vendor_profile_id' => $insertResult,
                        'username' => $username,
                        'email' => $email,
                        'business_name' => $businessName
                    ]
                );

                log_message('info', "User vendor berhasil dibuat: {$username} ({$email})");

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User Vendor berhasil dibuat'
                ]);

            } catch (DatabaseException $e) {
                // Tangani error duplikasi dari database
                if ($e->getCode() === 1062) { // MySQL error code for duplicate entry
                    $message = $e->getMessage();
                    if (strpos($message, 'users.username') !== false) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Username sudah digunakan',
                            'field' => 'username'
                        ]);
                    } elseif (strpos($message, 'auth_identities.secret') !== false) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Email sudah digunakan',
                            'field' => 'email'
                        ]);
                    }
                }
                
                log_message('error', 'Store Database Error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan database. Silakan coba lagi.'
                ]);
            } catch (\Exception $e) {
                log_message('error', 'Store Error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Request harus AJAX'
        ]);
    }
    
    // ========== EDIT ==========
    public function edit($id)
    {
        // Log activity akses form edit user vendor
        $this->logActivity(
            'view_edit_user_vendor',
            'Mengakses form edit user vendor',
            ['user_id' => $id]
        );

        $user = $this->users->asArray()->find($id);
        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }
            return redirect()->to(site_url('admin/uservendor'))->with('error', 'User tidak ditemukan');
        }

        $groups = $this->getUserGroups((int)$id);
        $profile = $this->vendorModel->where('user_id', $id)->first();

        // Ambil email dari auth_identities
        $identity = $this->identityModel->where(['user_id' => $id, 'type' => 'email_password'])->first();
        $user['email'] = $identity['secret'] ?? '';

        $data = [
            'user' => $user,
            'groups' => $groups,
            'profile' => $profile,
        ];

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Return HTML untuk modal AJAX
        if ($this->request->isAJAX()) {
            return view('admin/uservendor/modal_edit', $data);
        }

        // Fallback untuk non-AJAX
        // Merge dengan common data (termasuk notifikasi)
        return view('admin/uservendor/edit', array_merge($data, $commonData));
    }

    // ========== UPDATE ==========
public function update($id = null)
{
    // Handle AJAX request
    if ($this->request->isAJAX()) {
        try {
            // Pastikan ID tersedia
            if (!$id) {
                $id = $this->request->getPost('id');
            }
            
            $username = trim((string) $this->request->getPost('username'));
            $newPass  = (string) $this->request->getPost('password');
            $email    = trim((string) $this->request->getPost('email'));

            // Cek user exists
            $user = $this->users->find($id);
            if (!$user) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }

            // Cek duplikasi username jika berubah
            if ($username !== $user->username) {
                $existingUser = $this->users->where('username', $username)->first();
                if ($existingUser) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username sudah digunakan',
                        'field' => 'username'
                    ]);
                }
            }

            // Ambil email lama dengan lebih aman
            $identity = $this->identityModel->where(['user_id' => $id, 'type' => 'email_password'])->first();
            $oldEmail = ($identity && isset($identity['secret'])) ? $identity['secret'] : '';

            // Cek duplikasi email jika berubah
            if (!empty($email) && $email !== $oldEmail) {
                $existingEmail = $this->identityModel->where('secret', $email)->first();
                if ($existingEmail) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Email sudah digunakan',
                        'field' => 'email'
                    ]);
                }
            }

            // Update username
            $this->users->update($id, ['username' => $username]);

            // Set group
            $this->setSingleGroup((int) $id, 'vendor');

            // Update email jika diisi
            if ($email !== '') {
                $this->updateEmailIdentity((int) $id, $email);
            }

            // Update password jika diisi
            if ($newPass !== '') {
                if (strlen($newPass) < 8) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password minimal 8 karakter'
                    ]);
                }
                $this->resetPasswordByEmailIdentity((int) $id, $newPass);
            }

            // Handle Vendor profile
            $businessName = trim((string) $this->request->getPost('business_name'));
            $ownerName    = trim((string) $this->request->getPost('owner_name'));
            $phone        = trim((string) $this->request->getPost('phone'));
            $whatsapp     = trim((string) $this->request->getPost('whatsapp_number'));
            $vendorStatus = (string) $this->request->getPost('vendor_status');
            $commissionType = $this->request->getPost('commission_type');
            
            // Ambil user yang melakukan aksi
            $currentUser = service('auth')->user();
            
            // Handle kedua tipe komisi dengan benar
            $requestedCommission = $this->request->getPost('requested_commission');
            $requestedCommissionNominal = $this->request->getPost('requested_commission_nominal');
            
            // Validasi persentase jika tipe persentase
            if ($commissionType === 'percent' && $requestedCommission !== '' && ($requestedCommission < 0 || $requestedCommission > 100)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Persentase komisi harus antara 0-100%'
                ]);
            }

            $exists = $this->vendorModel->where('user_id', $id)->first();
            $currentStatus = $exists['status'] ?? 'pending';
            
            $data = [
                'business_name'              => $businessName,
                'owner_name'                 => $ownerName,
                'phone'                      => $phone,
                'whatsapp_number'            => $whatsapp,
                'status'                     => $vendorStatus,
                'commission_type'            => $commissionType,
                'requested_commission'       => $commissionType === 'percent' && $requestedCommission !== '' ? (float) $requestedCommission : null,
                'requested_commission_nominal' => $commissionType === 'nominal' && $requestedCommissionNominal !== '' ? (float) $requestedCommissionNominal : null,
                'updated_at'                 => date('Y-m-d H:i:s'),
                'action_by'                  => $currentUser->id // ✅ Tambahkan action_by untuk status changes
            ];

            // ✅ PERBAIKAN: HAPUS REJECTION_REASON JIKA STATUS BERUBAH DARI REJECTED KE STATUS LAIN
            if ($currentStatus === 'rejected' && $vendorStatus !== 'rejected') {
                $data['rejection_reason'] = null;
                log_message('debug', 'Clearing rejection_reason because status changed from rejected to ' . $vendorStatus);
            }

            // ✅ PERBAIKAN: HAPUS REJECTED_AT JIKA STATUS BERUBAH DARI REJECTED KE STATUS LAIN
            if ($currentStatus === 'rejected' && $vendorStatus !== 'rejected') {
                $data['rejected_at'] = null;
                log_message('debug', 'Clearing rejected_at because status changed from rejected to ' . $vendorStatus);
            }

            // Jika status berubah menjadi verified, set approved_at
            if ($vendorStatus === 'verified') {
                $data['approved_at'] = date('Y-m-d H:i:s');
                
                // ✅ PERBAIKAN: HAPUS REJECTION_REASON DAN REJECTED_AT JIKA BERUBAH KE VERIFIED
                if ($currentStatus === 'rejected') {
                    $data['rejection_reason'] = null;
                    $data['rejected_at'] = null;
                    log_message('debug', 'Clearing rejection data because status changed from rejected to verified');
                }
            }
            // Jika status berubah menjadi rejected, set rejected_at
            elseif ($vendorStatus === 'rejected') {
                $data['rejected_at'] = date('Y-m-d H:i:s');
                
                // ✅ PERBAIKAN: HAPUS APPROVED_AT JIKA BERUBAH DARI VERIFIED KE REJECTED
                if ($currentStatus === 'verified') {
                    $data['approved_at'] = null;
                    log_message('debug', 'Clearing approved_at because status changed from verified to rejected');
                }
            }
            // Jika status berubah menjadi pending, hapus semua timestamp approval/rejection
            elseif ($vendorStatus === 'pending') {
                // ✅ PERBAIKAN: HAPUS SEMUA TIMESTAMP DAN REASON JIKA BERUBAH KE PENDING
                $data['approved_at'] = null;
                $data['rejected_at'] = null;
                $data['rejection_reason'] = null;
                log_message('debug', 'Clearing all approval/rejection data because status changed to pending');
            }
            // Jika status berubah menjadi inactive, simpan status sebelumnya di inactive_reason
            elseif ($vendorStatus === 'inactive') {
                $data['inactive_reason'] = $currentStatus;
                log_message('debug', 'Saving previous status to inactive_reason: ' . $currentStatus);
            }
            // Jika status berubah dari inactive ke status lain, hapus inactive_reason
            elseif ($currentStatus === 'inactive' && $vendorStatus !== 'inactive') {
                $data['inactive_reason'] = null;
                log_message('debug', 'Clearing inactive_reason because status changed from inactive to ' . $vendorStatus);
            }

            if ($exists) {
                $this->vendorModel->where('user_id', $id)->set($data)->update();
            } else {
                $data['user_id']    = $id;
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->vendorModel->insert($data);
            }

            // 🔔 KIRIM NOTIFIKASI JIKA STATUS BERUBAH
            if ($exists && $currentStatus !== $vendorStatus) {
                $this->sendVendorStatusNotification($exists, $vendorStatus);
            }

            // Log activity update user vendor
            $this->logActivity(
                'update_user_vendor',
                'Memperbarui user vendor: ' . $username . ' dengan status: ' . $vendorStatus . ' (dari: ' . $currentStatus . ')',
                [
                    'user_id' => $id,
                    'username' => $username,
                    'email' => $email,
                    'business_name' => $businessName,
                    'old_status' => $currentStatus,
                    'new_status' => $vendorStatus,
                    'action_by' => $currentUser->id
                ]
            );

            log_message('info', "User vendor berhasil diupdate: {$username} dengan status: {$vendorStatus} (dari: {$currentStatus})");

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'User Vendor berhasil diupdate',
                'new_status' => $vendorStatus,
                'old_status' => $currentStatus
            ]);

        } catch (\Exception $e) {
            // Tangani SEMUA jenis exception, termasuk DatabaseException
            log_message('error', 'Update Error: ' . $e->getMessage());
            log_message('error', $e->getTraceAsString()); // Tambahkan trace untuk debugging lebih lanjut
            
            // Cek apakah ini error duplikasi dari database
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                if (strpos($e->getMessage(), 'users.username') !== false) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username sudah digunakan',
                        'field' => 'username'
                    ]);
                } elseif (strpos($e->getMessage(), 'auth_identities.secret') !== false) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Email sudah digunakan',
                        'field' => 'email'
                    ]);
                }
            }
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan server. Silakan coba lagi.'
            ]);
        }
    }

    // Fallback untuk non-AJAX
    return $this->response->setStatusCode(400)->setJSON([
        'status' => 'error',
        'message' => 'Request harus AJAX'
    ]);
}

    // ========== DELETE ==========
    public function delete($id)
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                log_message('debug', '=== DELETE VENDOR START ===');
                log_message('debug', 'Vendor ID: ' . $id);
                
                // Cek apakah user ada dan merupakan vendor
                $groups = $this->getUserGroups((int) $id);
                $isVendor = in_array('vendor', $groups, true);
                
                if (!$isVendor) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'User bukan Vendor'
                    ]);
                }
                
                // Get user data for logging
                $user = $this->users->find($id);
                $username = $user ? $user->username : 'Unknown';
                $vendorProfile = $this->vendorModel->where('user_id', $id)->first();
                $businessName = $vendorProfile ? $vendorProfile['business_name'] : 'Unknown';
                
                // Mulai transaksi
                $this->db->transStart();
                
                // 1. Hapus dari auth_groups_users
                if ($this->db->tableExists('auth_groups_users')) {
                    $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
                    log_message('debug', 'Deleted from auth_groups_users');
                }
                
                // 2. Hapus dari auth_identities
                $this->identityModel->where('user_id', $id)->delete();
                log_message('debug', 'Deleted from auth_identities');
                
                // 3. Hapus dari vendor_profiles
                $this->vendorModel->where('user_id', $id)->delete();
                log_message('debug', 'Deleted from vendor_profiles');
                
                // 4. PERBAIKAN: Hard delete dari tabel users dengan query langsung
                $deleteResult = $this->db->table('users')->where('id', $id)->delete();
                log_message('debug', 'Hard delete from users table: ' . ($deleteResult ? 'true' : 'false'));
                
                // Selesaikan transaksi
                $this->db->transComplete();
                
                // Periksa apakah transaksi berhasil
                if ($this->db->transStatus() === FALSE) {
                    log_message('error', 'Transaction failed during vendor deletion');
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menghapus vendor. Terjadi kesalahan transaksi.'
                    ]);
                }
                
                // PERBAIKAN: Verifikasi dengan query langsung
                $checkUser = $this->db->table('users')->where('id', $id)->get()->getRow();
                if ($checkUser) {
                    log_message('error', 'User still exists after deletion - ID: ' . $id);
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Gagal menghapus user dari database'
                    ]);
                }
                
                log_message('debug', 'Delete vendor success - user completely removed');
                
                // Log activity delete user vendor
                $this->logActivity(
                    'delete_user_vendor',
                    'Menghapus user vendor: ' . $username . ' (' . $businessName . ')',
                    [
                        'user_id' => $id,
                        'username' => $username,
                        'business_name' => $businessName
                    ]
                );
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'User Vendor berhasil dihapus.',
                    'refresh' => true
                ]);
                
            } catch (\Exception $e) {
                log_message('error', 'Delete vendor error: ' . $e->getMessage());
                
                // Rollback transaksi jika terjadi error
                $this->db->transRollback();
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }
        
        // Fallback untuk non-AJAX
        return $this->response->setStatusCode(400)->setJSON([
            'success' => false,
            'message' => 'Request harus AJAX'
        ]);
    }
    
    // ========== GET VENDOR DATA ==========
    public function getVendorData($id)
    {
        if ($this->request->isAJAX()) {
            try {
                $vendor = $this->vendorModel->where('user_id', $id)->first();
                if (!$vendor) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Data vendor tidak ditemukan'
                    ]);
                }

                return $this->response->setJSON([
                    'success' => true,
                    'data' => $vendor
                ]);
            } catch (\Exception $e) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Request harus AJAX'
        ]);
    }

    // ========== TOGGLE SUSPEND VENDOR ==========
public function toggleSuspend($id)
{
    // Handle AJAX request
    if ($this->request->isAJAX()) {
        try {
            log_message('debug', '=== TOGGLE SUSPEND VENDOR START ===');
            log_message('debug', 'Vendor ID: ' . $id);
            
            $groups = $this->getUserGroups((int) $id);
            log_message('debug', 'User groups: ' . json_encode($groups));
            
            if (!in_array('vendor', $groups, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Hanya vendor yang bisa di-nonaktifkan.'
                ]);
            }

            $vp = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->get()
                ->getRowArray();
                
            if (!$vp) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Profil vendor tidak ditemukan.'
                ]);
            }

            $currentStatus = $vp['status'] ?? 'pending';
            $vendorName = $vp['business_name'] ?? 'Unknown';
            log_message('debug', 'Current vendor status: ' . $currentStatus);
            
            // LOGIC: Pisahkan status verification dan active/inactive
            $isCurrentlyActive = !in_array($currentStatus, ['inactive']);
            
            if (!$isCurrentlyActive) {
                // Aktifkan vendor - kembalikan ke status verification sebelumnya
                $previousVerificationStatus = $vp['inactive_reason'] ?? 'pending';
                $newStatus = $previousVerificationStatus;
                $message = 'Vendor ' . $vendorName . ' diaktifkan kembali.';
                $isActive = true;
                $notificationStatus = 'active';
            } else {
                // Nonaktifkan vendor - simpan status verification saat ini
                $newStatus = 'inactive';
                $message = 'Vendor ' . $vendorName . ' dinonaktifkan.';
                $isActive = false;
                $notificationStatus = 'inactive';
            }
            
            log_message('debug', 'New vendor status: ' . $newStatus);
            
            // Ambil user yang melakukan aksi
            $currentUser = service('auth')->user();
            
            // Update database
            $updateData = [
                'status' => $newStatus, 
                'updated_at' => date('Y-m-d H:i:s'),
                'action_by' => $currentUser->id
            ];
            
            // ✅ HAPUS REJECTION_REASON JIKA STATUS SEBELUMNYA REJECTED DAN BERUBAH KE STATUS LAIN
            if ($currentStatus === 'rejected' && $newStatus !== 'rejected') {
                $updateData['rejection_reason'] = null;
                log_message('debug', 'Clearing rejection_reason because status changed from rejected to ' . $newStatus);
            }
            
            // SIMPAN STATUS VERIFICATION SEBELUMNYA JIKA DINONAKTIFKAN
            if (!$isCurrentlyActive) {
                // Jika diaktifkan kembali, hapus inactive_reason
                $updateData['inactive_reason'] = null;
            } else {
                // Jika dinonaktifkan, simpan status verification sebelumnya
                $updateData['inactive_reason'] = $currentStatus;
            }
            
            $updateResult = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->set($updateData)
                ->update();
            
            log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
            
            // Cek affected rows
            $affectedRows = $this->db->affectedRows();
            log_message('debug', 'Affected rows: ' . $affectedRows);
            
            // Jika updateResult false, cek apakah karena tidak ada perubahan data
            if (!$updateResult && $affectedRows === 0) {
                // Cek apakah data sudah sama dengan yang ingin diupdate
                $currentData = $this->db->table('vendor_profiles')
                    ->where('user_id', $id)
                    ->get()
                    ->getRowArray();
                if ($currentData && $currentData['status'] === $newStatus) {
                    log_message('debug', 'No data changes detected, considering as success');
                    $updateResult = true; // Anggap sukses jika tidak ada perubahan
                }
            }
            
            // 🔔 KIRIM NOTIFIKASI JIKA UPDATE BERHASIL
            if ($updateResult) {
                $this->sendVendorStatusNotification($vp, $notificationStatus);
            }
            
            // Log activity toggle suspend vendor
            $this->logActivity(
                'toggle_suspend_vendor',
                $message,
                [
                    'user_id' => $id,
                    'vendor_profile_id' => $vp['id'] ?? null,
                    'old_status' => $currentStatus,
                    'new_status' => $newStatus,
                    'vendor_name' => $vendorName,
                    'action_by' => $currentUser->id
                ]
            );
            
            log_message('debug', 'Toggle suspend vendor success: ' . $message);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'new_status' => $newStatus,
                'new_label' => ucfirst($newStatus),
                'is_active' => $isActive
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Toggle suspend vendor error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => 'Request harus AJAX'
    ]);
}
    // ========== VERIFY VENDOR ==========
public function verifyVendor($id)
{
    // Handle AJAX request
    if ($this->request->isAJAX()) {
        try {
            log_message('debug', '=== VERIFY VENDOR START ===');
            log_message('debug', 'Vendor ID: ' . $id);
            
            $groups = $this->getUserGroups((int) $id);
            log_message('debug', 'User groups: ' . json_encode($groups));
            
            if (!in_array('vendor', $groups, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Hanya vendor yang bisa diverifikasi.'
                ]);
            }

            $vp = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->get()
                ->getRowArray();
                
            if (!$vp) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Profil vendor tidak ditemukan.'
                ]);
            }

            $vendorName = $vp['business_name'] ?? 'Unknown';
            $currentStatus = $vp['status'] ?? 'pending';
            log_message('debug', 'Current vendor status: ' . $currentStatus);
            
            // Ambil user yang melakukan aksi
            $currentUser = service('auth')->user();
            
            // Update status menjadi verified
            $updateData = [
                'status' => 'verified',
                'updated_at' => date('Y-m-d H:i:s'),
                'approved_at' => date('Y-m-d H:i:s'),
                'action_by' => $currentUser->id
            ];
            
            // ✅ HAPUS REJECTION_REASON JIKA STATUS SEBELUMNYA REJECTED
            if ($currentStatus === 'rejected') {
                $updateData['rejection_reason'] = null;
                log_message('debug', 'Clearing rejection_reason because status changed from rejected to verified');
            }
            
            $updateResult = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->set($updateData)
                ->update();
            
            log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
            
            // Cek affected rows
            $affectedRows = $this->db->affectedRows();
            log_message('debug', 'Affected rows: ' . $affectedRows);
            
            // Jika updateResult false, cek apakah karena tidak ada perubahan data
            if (!$updateResult && $affectedRows === 0) {
                // Cek apakah data sudah sama dengan yang ingin diupdate
                $currentData = $this->db->table('vendor_profiles')
                    ->where('user_id', $id)
                    ->get()
                    ->getRowArray();
                if ($currentData && $currentData['status'] === 'verified') {
                    log_message('debug', 'No data changes detected, considering as success');
                    $updateResult = true; // Anggap sukses jika tidak ada perubahan
                }
            }
            
            // 🔔 KIRIM NOTIFIKASI JIKA UPDATE BERHASIL
            if ($updateResult) {
                $this->sendVendorStatusNotification($vp, 'verified');
            }
            
            // Log activity verify vendor
            $this->logActivity(
                'verify_vendor',
                'Memverifikasi vendor: ' . $vendorName,
                [
                    'user_id' => $id,
                    'vendor_profile_id' => $vp['id'] ?? null,
                    'old_status' => $currentStatus,
                    'new_status' => 'verified',
                    'vendor_name' => $vendorName,
                    'action_by' => $currentUser->id
                ]
            );
            
            log_message('debug', 'Verify vendor success');
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Vendor ' . $vendorName . ' berhasil diverifikasi.',
                'new_status' => 'verified',
                'new_label' => 'Verified'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Verify vendor error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => 'Request harus AJAX'
    ]);
}
    // ========== PENDING VENDOR ==========
public function pendingVendor($id)
{
    // Handle AJAX request
    if ($this->request->isAJAX()) {
        try {
            log_message('debug', '=== PENDING VENDOR START ===');
            log_message('debug', 'Vendor ID: ' . $id);
            
            $groups = $this->getUserGroups((int) $id);
            log_message('debug', 'User groups: ' . json_encode($groups));
            
            if (!in_array('vendor', $groups, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Hanya vendor yang bisa di-set pending.'
                ]);
            }

            $vp = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->get()
                ->getRowArray();
                
            if (!$vp) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Profil vendor tidak ditemukan.'
                ]);
            }

            $vendorName = $vp['business_name'] ?? 'Unknown';
            $currentStatus = $vp['status'] ?? 'pending';
            log_message('debug', 'Current vendor status: ' . $currentStatus);
            
            // Ambil user yang melakukan aksi
            $currentUser = service('auth')->user();
            
            // Update status menjadi pending
            $updateData = [
                'status' => 'pending',
                'updated_at' => date('Y-m-d H:i:s'),
                'action_by' => $currentUser->id
            ];
            
            // ✅ HAPUS REJECTION_REASON JIKA STATUS SEBELUMNYA REJECTED
            if ($currentStatus === 'rejected') {
                $updateData['rejection_reason'] = null;
                log_message('debug', 'Clearing rejection_reason because status changed from rejected to pending');
            }
            
            $updateResult = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->set($updateData)
                ->update();
            
            log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
            
            // Cek affected rows
            $affectedRows = $this->db->affectedRows();
            log_message('debug', 'Affected rows: ' . $affectedRows);
            
            // Jika updateResult false, cek apakah karena tidak ada perubahan data
            if (!$updateResult && $affectedRows === 0) {
                // Cek apakah data sudah sama dengan yang ingin diupdate
                $currentData = $this->db->table('vendor_profiles')
                    ->where('user_id', $id)
                    ->get()
                    ->getRowArray();
                if ($currentData && $currentData['status'] === 'pending') {
                    log_message('debug', 'No data changes detected, considering as success');
                    $updateResult = true; // Anggap sukses jika tidak ada perubahan
                }
            }
            
            // 🔔 KIRIM NOTIFIKASI JIKA UPDATE BERHASIL
            if ($updateResult) {
                $this->sendVendorStatusNotification($vp, 'pending');
            }
            
            // Log activity pending vendor
            $this->logActivity(
                'pending_vendor',
                'Mengembalikan vendor ke status pending: ' . $vendorName,
                [
                    'user_id' => $id,
                    'vendor_profile_id' => $vp['id'] ?? null,
                    'old_status' => $currentStatus,
                    'new_status' => 'pending',
                    'vendor_name' => $vendorName,
                    'action_by' => $currentUser->id
                ]
            );
            
            log_message('debug', 'Pending vendor success');
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Vendor ' . $vendorName . ' berhasil dikembalikan ke status pending.',
                'new_status' => 'pending',
                'new_label' => 'Pending'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Pending vendor error: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => 'Request harus AJAX'
    ]);
}

    // ========== REJECT VENDOR ==========
    public function rejectVendor($id)
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                log_message('debug', '=== REJECT VENDOR START ===');
                log_message('debug', 'Vendor ID: ' . $id);
                
                $rejectReason = $this->request->getPost('reject_reason');
                
                if (empty($rejectReason)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Alasan penolakan harus diisi.'
                    ]);
                }

                $groups = $this->getUserGroups((int) $id);
                log_message('debug', 'User groups: ' . json_encode($groups));
                
                if (!in_array('vendor', $groups, true)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Hanya vendor yang bisa ditolak.'
                    ]);
                }

                $vp = $this->db->table('vendor_profiles')
                    ->where('user_id', $id)
                    ->get()
                    ->getRowArray();
                    
                if (!$vp) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Profil vendor tidak ditemukan.'
                    ]);
                }

                $vendorName = $vp['business_name'] ?? 'Unknown';
                log_message('debug', 'Current vendor status: ' . ($vp['status'] ?? 'pending'));
                
                // Ambil user yang melakukan aksi
                $currentUser = service('auth')->user();
                
                // Update status menjadi rejected dan simpan alasan
                $updateData = [
                    'status' => 'rejected',
                    'rejection_reason' => $rejectReason,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'action_by' => $currentUser->id
                ];
                
                $updateResult = $this->db->table('vendor_profiles')
                    ->where('user_id', $id)
                    ->set($updateData)
                    ->update();
                
                log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
                
                // Cek affected rows
                $affectedRows = $this->db->affectedRows();
                log_message('debug', 'Affected rows: ' . $affectedRows);
                
                // Jika updateResult false, cek apakah karena tidak ada perubahan data
                if (!$updateResult && $affectedRows === 0) {
                    // Cek apakah data sudah sama dengan yang ingin diupdate
                    $currentData = $this->db->table('vendor_profiles')
                        ->where('user_id', $id)
                        ->get()
                        ->getRowArray();
                    if ($currentData && $currentData['status'] === 'rejected') {
                        log_message('debug', 'No data changes detected, considering as success');
                        $updateResult = true; // Anggap sukses jika tidak ada perubahan
                    }
                }
                
                // 🔔 KIRIM NOTIFIKASI JIKA UPDATE BERHASIL
                if ($updateResult) {
                    $this->sendVendorStatusNotification($vp, 'rejected', $rejectReason);
                }
                
                // Log activity reject vendor
                $this->logActivity(
                    'reject_vendor',
                    'Menolak vendor: ' . $vendorName . ' dengan alasan: ' . $rejectReason,
                    [
                        'user_id' => $id,
                        'vendor_profile_id' => $vp['id'] ?? null,
                        'old_status' => $vp['status'] ?? 'pending',
                        'new_status' => 'rejected',
                        'rejection_reason' => $rejectReason,
                        'vendor_name' => $vendorName,
                        'action_by' => $currentUser->id
                    ]
                );
                
                log_message('debug', 'Reject vendor success');
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Vendor ' . $vendorName . ' berhasil ditolak.',
                    'new_status' => 'rejected',
                    'new_label' => 'Rejected'
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Reject vendor error: ' . $e->getMessage());
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Request harus AJAX'
        ]);
    }

    /**
     * Helper method untuk mendapatkan nama user berdasarkan ID dari berbagai profile tables
     */
    private function getUserNameById($userId)
    {
        try {
            $db = \Config\Database::connect();
            
            // 1. Cek di admin_profiles terlebih dahulu (untuk admin)
            $adminProfile = $db->table('admin_profiles')
                             ->select('name')
                             ->where('user_id', $userId)
                             ->get()
                             ->getRowArray();
            if ($adminProfile && !empty($adminProfile['name'])) {
                return $adminProfile['name'] . ' (Admin)';
            }
            
            // 2. Cek di seo_profiles (untuk SEO team)
            $seoProfile = $db->table('seo_profiles')
                           ->select('name')
                           ->where('user_id', $userId)
                           ->get()
                           ->getRowArray();
            if ($seoProfile && !empty($seoProfile['name'])) {
                return $seoProfile['name'] . ' (SEO)';
            }
            
            // 3. Cek di vendor_profiles (untuk vendor)
            $vendorProfile = $db->table('vendor_profiles')
                              ->select('business_name, owner_name')
                              ->where('user_id', $userId)
                              ->get()
                              ->getRowArray();
            if ($vendorProfile) {
                if (!empty($vendorProfile['business_name'])) {
                    return $vendorProfile['business_name'] . ' (Vendor)';
                } elseif (!empty($vendorProfile['owner_name'])) {
                    return $vendorProfile['owner_name'] . ' (Vendor)';
                }
            }
            
            // 4. Fallback: ambil username dari users table
            $user = $db->table('users')
                      ->select('username')
                      ->where('id', $userId)
                      ->get()
                      ->getRowArray();
            if ($user && !empty($user['username'])) {
                return $user['username'] . ' (User)';
            }
            
            // 5. Jika semua gagal, return ID dengan label
            return 'User ID: ' . $userId;
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting user name by ID: ' . $e->getMessage());
            return 'User ID: ' . $userId;
        }
    }

    // ========== HELPER METHODS ==========
    private function getUserGroups(int $userId): array
    {
        if (! $this->db->tableExists('auth_groups_users')) {
            return [];
        }

        $rows = $this->db->table('auth_groups_users')
            ->select('group')
            ->where('user_id', $userId)
            ->get()->getResultArray();

        return array_values(array_unique(array_column($rows, 'group')));
    }

    private function setSingleGroup(int $userId, string $group): void
    {
        if (! in_array($group, ['admin', 'seoteam', 'vendor'], true)) {
            $group = 'vendor';
        }

        if ($this->db->tableExists('auth_groups_users')) {
            $this->db->table('auth_groups_users')->where('user_id', $userId)->delete();
            $this->db->table('auth_groups_users')->insert([
                'user_id' => $userId,
                'group'   => $group,
            ]);
        }
    }

    private function resetPasswordByEmailIdentity(int $userId, string $newPass): void
    {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $this->identityModel
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->set('secret2', $hash)
            ->update();
    }

    private function updateEmailIdentity(int $userId, string $email): void
    {
        $this->identityModel
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->set('secret', $email)
            ->update();
    }

    /**
     * Kirim notifikasi status vendor ke vendor
     */
    private function sendVendorStatusNotification($vendorData, $status, $reason = null)
    {
        try {
            $db = \Config\Database::connect();
            
            $vendorName = $vendorData['business_name'] ?? 'Vendor Tidak Dikenal';
            $vendorUserId = $vendorData['user_id'] ?? null;
            
            if (!$vendorUserId) {
                log_message('error', 'Vendor user_id tidak ditemukan untuk notifikasi');
                return;
            }

            // Tentukan pesan berdasarkan status
            $title = '';
            $message = '';
            
            switch ($status) {
                case 'verified':
                    $title = '🎉 Akun Vendor Diverifikasi';
                    $message = "Selamat! Vendor {$vendorName} telah diverifikasi dan aktif.";
                    break;
                    
                case 'rejected':
                    $title = '❌ Verifikasi Vendor Ditolak';
                    $message = "Maaf, vendor {$vendorName} ditolak.";
                    if ($reason) {
                        $message .= " Alasan: {$reason}";
                    }
                    break;
                    
                case 'pending':
                    $title = '⏳ Status Vendor Pending';
                    $message = "Status vendor {$vendorName} telah dikembalikan ke pending oleh Admin.";
                    break;
                    
                case 'inactive':
                    $title = 'Vendor Dinonaktifkan';
                    $message = "Vendor {$vendorName} telah dinonaktifkan oleh Admin.";
                    break;
                    
                case 'active':
                    $title = 'Vendor Diaktifkan';
                    $message = "Vendor {$vendorName} telah diaktifkan kembali oleh Admin.";
                    break;
                    
                default:
                    return; // Tidak kirim notifikasi untuk status lain
            }

            // Kirim notifikasi ke vendor
            $db->table('notifications')->insert([
                'user_id' => $vendorUserId,
                'vendor_id' => $vendorData['id'] ?? null, // vendor_profiles.id
                'seo_id' => null,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            log_message('info', "Notifikasi status vendor berhasil dikirim: {$vendorName} - {$status}");

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi status vendor: ' . $e->getMessage());
        }
    }

    /**
     * Log activity untuk admin
     */
    private function logActivity($action, $description, $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            
            $data = [
                'user_id'     => $user ? $user->id : null,
                'module'      => 'admin_user_vendor',
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => (string) $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogsModel->insert($data);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity in UserVendor: ' . $e->getMessage());
        }
    }
}