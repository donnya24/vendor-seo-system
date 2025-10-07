<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\IdentityModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;

class UserVendor extends BaseController
{
    protected $users;
    protected $db;
    protected $vendorModel;
    protected $identityModel;

    public function __construct()
    {
        $this->users         = service('auth')->getProvider();
        $this->db            = db_connect();
        $this->vendorModel   = new VendorProfilesModel();
        $this->identityModel = new IdentityModel();
    }

    // ========== LIST VENDOR ==========
    public function index()
    {
        // Query untuk VENDOR - dengan semua field komisi
        $vendorUsers = $this->db->table('users u')
            ->select('u.id, u.username, 
                     vp.business_name, vp.owner_name, vp.phone, vp.whatsapp_number,
                     vp.status as vendor_status, vp.commission_type, 
                     vp.requested_commission, vp.requested_commission_nominal,
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
            
            return [
                'id' => (int)$user['id'],
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
                'is_verified' => $isVerified,
                'groups' => ['vendor']
            ];
        }, $vendorUsers);

        return view('admin/uservendor/index', [
            'page'        => 'Vendor Management',
            'users'       => $users,
            'usersVendor' => $users,
        ]);
    }

    // ========== CREATE VENDOR ==========
    public function create()
    {
        // Handle AJAX request untuk modal - return HTML langsung
        if ($this->request->isAJAX()) {
            return view('admin/uservendor/_form_vendor', ['role' => 'vendor']);
        }

        // fallback untuk non-AJAX
        return view('admin/uservendor/create_vendor', [
            'page' => 'Vendor Management',
            'role' => 'vendor',
        ]);
    }

    // ========== STORE VENDOR ==========
    public function store()
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                $role     = 'vendor';
                $username = trim((string) $this->request->getPost('username'));
                $email    = trim((string) $this->request->getPost('email'));
                $password = (string) $this->request->getPost('password');
                
                // Validasi input
                if (empty($username) || empty($email) || empty($password)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username, email dan password harus diisi'
                    ]);
                }

                // Validasi password
                if (strlen($password) < 8) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password minimal 8 karakter'
                    ]);
                }

                // Cek apakah username sudah ada
                $existingUser = $this->users->where('username', $username)->first();
                if ($existingUser) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Username sudah digunakan'
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

                log_message('debug', 'Vendor created with ID: ' . $userId);

                // buat email-password identity
                $this->identityModel->saveEmailIdentity($userId, $email, $password);

                // set grup vendor
                $this->setSingleGroup((int) $userId, $role);

                // vendor profile
                $businessName = trim((string) $this->request->getPost('business_name'));
                $ownerName    = trim((string) $this->request->getPost('owner_name'));
                $phone        = trim((string) $this->request->getPost('phone'));
                $whatsapp     = trim((string) $this->request->getPost('whatsapp_number'));
                $vendorStatus   = $this->request->getPost('vendor_status') ?? 'pending';
                $commissionType = $this->request->getPost('commission_type') ?? 'nominal';
                
                // Handle kedua tipe komisi dengan benar
                $requestedCommission = null;
                $requestedCommissionNominal = null;
                
                if ($commissionType === 'percent') {
                    $requestedCommission = $this->request->getPost('requested_commission');
                    // Validasi persentase
                    if ($requestedCommission !== '' && ($requestedCommission < 0 || $requestedCommission > 100)) {
                        // Rollback user creation
                        $this->users->delete($userId);
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Persentase komisi harus antara 0-100%'
                        ]);
                    }
                } else {
                    $requestedCommissionNominal = $this->request->getPost('requested_commission_nominal');
                }

                $vendorData = [
                    'user_id'                   => $userId,
                    'business_name'             => $businessName,
                    'owner_name'                => $ownerName,
                    'phone'                     => $phone,
                    'whatsapp_number'           => $whatsapp,
                    'status'                    => $vendorStatus,
                    'commission_type'           => $commissionType,
                    'requested_commission'      => $requestedCommission !== '' ? (float) $requestedCommission : null,
                    'requested_commission_nominal' => $requestedCommissionNominal !== '' ? (float) $requestedCommissionNominal : null,
                    'created_at'                => date('Y-m-d H:i:s'),
                    'updated_at'                => date('Y-m-d H:i:s'),
                ];

                $this->vendorModel->insert($vendorData);
                log_message('debug', 'Vendor profile created for user ID: ' . $userId);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Vendor berhasil dibuat'
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Store vendor error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }

        // Fallback untuk non-AJAX
        return $this->response->setStatusCode(400)->setJSON([
            'status' => 'error',
            'message' => 'Request harus AJAX'
        ]);
    }

    // ========== EDIT VENDOR ==========
    public function edit($id)
    {
        log_message('debug', '=== EDIT VENDOR METHOD CALLED ===');
        log_message('debug', 'Edit Vendor ID from URL: ' . $id);

        // Cari user
        $user = $this->users->find((int)$id);
        
        log_message('debug', 'Vendor found: ' . ($user ? 'YES - ID: ' . $user->id : 'NO'));
        
        if (!$user) {
            log_message('error', 'Vendor not found for editing. ID: ' . $id);
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Vendor tidak ditemukan. ID: ' . $id
                ]);
            }
            return redirect()->to(site_url('admin/uservendor'))->with('error', 'Vendor tidak ditemukan');
        }

        // Convert user object to array dengan data yang benar
        $userArray = [
            'id' => $user->id,
            'username' => $user->username,
        ];
        
        $profile = $this->vendorModel->where('user_id', $id)->first();
        if ($profile) {
            $userArray = array_merge($userArray, $profile);
            $userArray['vendor_status'] = $profile['status'] ?? 'pending';
        }

        // Ambil email dari auth_identities
        $identity = $this->identityModel->getEmailIdentity($id);
        $userArray['email'] = $identity['secret'] ?? '';

        $data = [
            'user' => $userArray,
            'role' => 'vendor',
            'profile' => $profile,
        ];

        log_message('debug', 'Final vendor data for edit: ' . json_encode($userArray));

        // Return HTML untuk modal AJAX
        if ($this->request->isAJAX()) {
            return view('admin/uservendor/_form_edit_vendor', $data);
        }

        // Fallback untuk non-AJAX
        return view('admin/uservendor/edit_vendor', $data);
    }

    // ========== UPDATE VENDOR ==========
    public function update($id = null)
    {
        // Log request data
        log_message('debug', '=== UPDATE VENDOR METHOD CALLED ===');
        log_message('debug', 'URL ID: ' . $id);
        log_message('debug', 'POST Data: ' . json_encode($this->request->getPost()));
        
        // Handle AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
        }

        try {
            // Pastikan ID tersedia - ambil dari parameter atau POST
            if (!$id) {
                $id = $this->request->getPost('id');
            }
            
            log_message('debug', 'Final Vendor ID: ' . $id);
            
            if (!$id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID vendor tidak valid'
                ]);
            }

            // Cek user exists
            $user = $this->users->find($id);
            
            if (!$user) {
                log_message('debug', 'Vendor not found with ID: ' . $id);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Vendor tidak ditemukan. ID: ' . $id
                ]);
            }

            log_message('debug', 'Vendor found: ' . $user->username);
            
            // Get data dari POST
            $username = trim((string) $this->request->getPost('username'));
            $newPass  = (string) $this->request->getPost('password');
            $email    = trim((string) $this->request->getPost('email'));

            // Validasi required fields
            if (empty($username) || empty($email)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Username dan email harus diisi'
                ]);
            }

            // Update username jika berubah
            if ($user->username !== $username) {
                $this->users->update($id, ['username' => $username]);
                log_message('debug', 'Vendor username updated');
            }

            // Update email jika diisi dan berubah
            $currentIdentity = $this->identityModel->getEmailIdentity($id);
            if ($email !== '' && $currentIdentity && $currentIdentity['secret'] !== $email) {
                $this->identityModel->saveEmailIdentity($id, $email, $newPass);
                log_message('debug', 'Vendor email updated');
            }

            // Update password jika diisi
            if ($newPass !== '') {
                if (strlen($newPass) < 8) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Password minimal 8 karakter'
                    ]);
                }
                $this->identityModel->saveEmailIdentity($id, $currentIdentity['secret'], $newPass);
                log_message('debug', 'Vendor password updated');
            }

            // Handle Vendor profile
            $businessName = trim((string) $this->request->getPost('business_name'));
            $ownerName    = trim((string) $this->request->getPost('owner_name'));
            $phone        = trim((string) $this->request->getPost('phone'));
            $whatsapp     = trim((string) $this->request->getPost('whatsapp_number'));
            $vendorStatus = (string) $this->request->getPost('vendor_status');
            $commissionType = $this->request->getPost('commission_type');
            
            // Handle kedua tipe komisi dengan benar
            $requestedCommission = $this->request->getPost('requested_commission');
            $requestedCommissionNominal = $this->request->getPost('requested_commission_nominal');
            
            // Validasi persentase jika tipe persentase
            if ($commissionType === 'percent' && $requestedCommission !== '' && ($requestedCommission < 0 || $requestedCommission > 100)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Persentase komisi harus antara 0-100%'
                ]);
            }

            $profileData = [
                'business_name'              => $businessName,
                'owner_name'                 => $ownerName,
                'phone'                      => $phone,
                'whatsapp_number'            => $whatsapp,
                'status'                     => $vendorStatus,
                'commission_type'            => $commissionType,
                'requested_commission'       => $commissionType === 'percent' && $requestedCommission !== '' ? (float) $requestedCommission : null,
                'requested_commission_nominal' => $commissionType === 'nominal' && $requestedCommissionNominal !== '' ? (float) $requestedCommissionNominal : null,
                'updated_at'                 => date('Y-m-d H:i:s'),
            ];

            $this->vendorModel->getOrCreateByUserId($id, $profileData);
            log_message('debug', 'Vendor profile updated');

            log_message('debug', 'Vendor updated successfully: ' . $id);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Vendor berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Update vendor error: '.  $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // ========== DELETE VENDOR ==========
    public function delete($id)
    {
        try {
            log_message('debug', 'Deleting vendor ID: ' . $id);

            if ($this->db->tableExists('auth_groups_users')) {
                $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
            }

            // Hapus dari auth_identities menggunakan model
            $this->identityModel->where('user_id', $id)->delete();

            // Hapus user
            $this->users->delete($id);

            // Hapus profile vendor
            $this->vendorModel->where('user_id', $id)->delete();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Vendor berhasil dihapus',
                    'redirect' => site_url('admin/uservendor')
                ]);
            }

            return redirect()->to(site_url('admin/uservendor'))->with('success', 'Vendor deleted.');

        } catch (\Exception $e) {
            log_message('error', 'Delete vendor error: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menghapus vendor: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Gagal menghapus vendor: ' . $e->getMessage());
        }
    }

    // ========== GET VENDOR DATA ==========
    public function getVendorData($id)
    {
        $response = [
            'status' => 'error',
            'data' => null,
            'message' => 'Vendor tidak ditemukan'
        ];

        try {
            // Get user data
            $user = $this->users->asArray()->find($id);
            if (!$user) {
                throw new \Exception('User tidak ditemukan');
            }

            // Get vendor profile
            $profile = $this->vendorModel->where('user_id', $id)->first();
            if (!$profile) {
                throw new \Exception('Profil vendor tidak ditemukan');
            }

            // Get email
            $identity = $this->identityModel->getEmailIdentity($id);
            
            // Combine all data
            $vendorData = array_merge($user, $profile, [
                'email' => $identity['secret'] ?? '',
                'vendor_status' => $profile['status'] ?? 'pending',
            ]);

            $response['status'] = 'success';
            $response['data'] = $vendorData;

        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
            log_message('error', 'Get vendor data error: ' . $e->getMessage());
        }

        return $this->response->setJSON($response);
    }

    // ========== TOGGLE SUSPEND VENDOR ==========
    public function toggleSuspend($id)
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                log_message('debug', '=== TOGGLE SUSPEND VENDOR START ===');
                log_message('debug', 'Vendor ID: ' . $id);
                
                $vp = $this->vendorModel->getByUserId($id);
                    
                if (!$vp) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Profil vendor tidak ditemukan.'
                    ]);
                }

                log_message('debug', 'Current vendor status: ' . ($vp['status'] ?? 'pending'));
                
                $currentStatus = $vp['status'] ?? 'pending';
                
                // LOGIC BARU: Pisahkan status verification dan active/inactive
                $isCurrentlyActive = !in_array($currentStatus, ['inactive', 'suspended']);
                
                if (!$isCurrentlyActive) {
                    // Aktifkan vendor - kembalikan ke status verification sebelumnya
                    $previousVerificationStatus = $vp['inactive_reason'] ?? 'pending';
                    $newStatus = $previousVerificationStatus;
                    $message = 'Vendor diaktifkan kembali.';
                    $isActive = true;
                } else {
                    // Nonaktifkan vendor - simpan status verification saat ini
                    $newStatus = 'inactive';
                    $message = 'Vendor dinonaktifkan.';
                    $isActive = false;
                }
                
                log_message('debug', 'New vendor status: ' . $newStatus);
                
                // Update database
                $updateData = [
                    'status' => $newStatus, 
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // SIMPAN STATUS VERIFICATION SEBELUMNYA JIKA DINONAKTIFKAN
                if (!$isCurrentlyActive) {
                    // Sedang mengaktifkan - hapus inactive_reason
                    $updateData['inactive_reason'] = null;
                } else {
                    // Sedang menonaktifkan - simpan status verification saat ini
                    $updateData['inactive_reason'] = $currentStatus;
                }
                
                $updateResult = $this->vendorModel->update($id, $updateData);
                
                log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
                
                log_message('debug', 'Toggle suspend completed: ' . $message);
                log_message('debug', '=== TOGGLE SUSPEND VENDOR END ===');
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $message,
                    'new_status' => $newStatus,
                    'is_active' => !$isCurrentlyActive, // Status aktif setelah update
                    'should_refresh' => true
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Toggle suspend vendor error: ' . $e->getMessage());
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
            }
        } else {
            // Non-AJAX request
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request harus AJAX'
            ]);
        }
    }

    // ========== VERIFY VENDOR ==========
    public function verifyVendor($id)
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                log_message('debug', 'Verify vendor called for ID: ' . $id);

                $vp = $this->vendorModel->getByUserId($id);
                    
                if (!$vp) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Profil vendor tidak ditemukan.'
                    ]);
                }

                $currentStatus = $vp['status'] ?? 'pending';
                
                if ($currentStatus !== 'pending') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Hanya vendor dengan status pending yang bisa diverifikasi.'
                    ]);
                }

                // Update status menjadi verified
                $updateData = [
                    'status' => 'verified',
                    'approved_at' => date('Y-m-d H:i:s'),
                    'action_by' => service('auth')->id(), // ID admin yang melakukan approve
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $updateResult = $this->vendorModel->update($id, $updateData);
                
                if (!$updateResult) {
                    throw new \Exception('Gagal mengupdate status vendor');
                }

                log_message('debug', 'Vendor verified successfully: ' . $id);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Vendor berhasil diverifikasi.',
                    'new_status' => 'verified'
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

    // ========== REJECT VENDOR ==========
    public function rejectVendor($id)
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                log_message('debug', 'Reject vendor called for ID: ' . $id);

                $rejectReason = $this->request->getPost('reject_reason');
                
                if (empty($rejectReason)) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Alasan penolakan harus diisi.'
                    ]);
                }

                $vp = $this->vendorModel->getByUserId($id);
                    
                if (!$vp) {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Profil vendor tidak ditemukan.'
                    ]);
                }

                $currentStatus = $vp['status'] ?? 'pending';
                
                if ($currentStatus !== 'pending') {
                    return $this->response->setJSON([
                        'success' => false,
                        'message' => 'Hanya vendor dengan status pending yang bisa ditolak.'
                    ]);
                }

                // Update status menjadi rejected dan simpan alasan
                $updateData = [
                    'status' => 'rejected',
                    'rejection_reason' => $rejectReason,
                    'action_by' => service('auth')->id(), // ID admin yang melakukan reject
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                $updateResult = $this->vendorModel->update($id, $updateData);
                
                if (!$updateResult) {
                    throw new \Exception('Gagal mengupdate status vendor');
                }

                log_message('debug', 'Vendor rejected successfully: ' . $id);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Vendor berhasil ditolak.',
                    'new_status' => 'rejected'
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

    // ========== HELPER METHODS ==========
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
}