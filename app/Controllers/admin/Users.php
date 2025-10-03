<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\SeoProfilesModel;
use App\Models\IdentityModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;

class Users extends BaseController
{
    protected $users;
    protected $db;
    protected $vendorModel;
    protected $seoModel;
    protected $identityModel;

    public function __construct()
    {
        $this->users         = service('auth')->getProvider(); // Shield UserModel
        $this->db            = db_connect();
        $this->vendorModel   = new VendorProfilesModel();
        $this->seoModel      = new SeoProfilesModel();
        $this->identityModel = new IdentityModel();
    }

    // ========== LIST ==========
    public function index()
    {
        $currentTab = $this->request->getGet('tab') ?? 'seo';
        $users = [];

        if ($currentTab === 'seo') {
            // Query untuk user SEO
            $users = $this->db->table('users u')
                ->select('u.id, u.username, sp.name, sp.phone, sp.status as seo_status, ai.secret as email')
                ->join('auth_groups_users agu', 'agu.user_id = u.id')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
                ->where('agu.group', 'seoteam')
                ->orderBy('u.id', 'DESC')
                ->get()
                ->getResultArray();
            
            $users = array_map(function ($user) {
                return [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'name' => $user['name'] ?? '-',
                    'phone' => $user['phone'] ?? '-',
                    'email' => $user['email'] ?? '-',
                    'seo_status' => $user['seo_status'] ?? 'active',
                    'groups' => ['seoteam']
                ];
            }, $users);
            
        } else {
            // Query untuk VENDOR - dengan semua field komisi
            $users = $this->db->table('users u')
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
                    'is_verified' => $isVerified,
                    'groups' => ['vendor']
                ];
            }, $users);
        }

        // Filter users untuk tabs
        $usersSeo = array_filter($users, fn($user) => in_array('seoteam', $user['groups'] ?? [], true));
        $usersVendor = array_filter($users, fn($user) => in_array('vendor', $user['groups'] ?? [], true));

        return view('admin/users/index', [
            'page'        => 'Users',
            'users'       => $users,
            'usersSeo'    => $usersSeo,
            'usersVendor' => $usersVendor,
            'currentTab'  => $currentTab,
        ]);
    }

    // ========== CREATE ==========
    public function create()
    {
        $role = $this->request->getGet('role') ?? 'seoteam';

        // Handle AJAX request untuk modal - return HTML langsung
        if ($this->request->isAJAX()) {
            if ($role === 'vendor') {
                return view('admin/users/_form_vendor', ['role' => $role]);
            } else {
                return view('admin/users/_form_seo', ['role' => $role]);
            }
        }

        // fallback untuk non-AJAX
        if ($role === 'vendor') {
            return view('admin/users/create_vendor', [
                'page' => 'Users',
                'role' => $role,
            ]);
        } else {
            return view('admin/users/create_seo', [
                'page' => 'Users',
                'role' => $role,
            ]);
        }
    }

    // ========== STORE ==========
    public function store()
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                $role     = (string) $this->request->getPost('role'); // admin|seoteam|vendor
                $username = trim((string) $this->request->getPost('username'));
                $email    = trim((string) $this->request->getPost('email'));
                $password = (string) $this->request->getPost('password');
                
                // Validasi password
                if (strlen($password) < 8) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password minimal 8 karakter'
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
                $this->setSingleGroup((int) $userId, $role);

                // vendor profile
                if ($role === 'vendor') {
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
                }

                // seo profile
                if ($role === 'seoteam') {
                    $name  = trim((string) $this->request->getPost('fullname'));
                    $phone = trim((string) $this->request->getPost('phone'));

                    $this->seoModel->insert([
                        'user_id'     => $userId,
                        'name'        => $name,
                        'phone'       => $phone,
                        'status'      => 'active',
                        'created_at'  => date('Y-m-d H:i:s'),
                        'updated_at'  => date('Y-m-d H:i:s'),
                    ]);
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User berhasil dibuat'
                ]);

            } catch (\Exception $e) {
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

    // ========== EDIT ==========
    public function edit($id)
    {
        $role = $this->request->getGet('role') ?? 'seoteam';

        $user = $this->users->asArray()->find($id);
        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'User tidak ditemukan']);
            }
            return redirect()->to(site_url('admin/users'))->with('error', 'User tidak ditemukan');
        }

        $groups = $this->getUserGroups((int)$id);
        $profile = [];

        if ($role === 'vendor') {
            $profile = $this->vendorModel->where('user_id', $id)->first();
        } elseif ($role === 'seoteam') {
            $profile = $this->seoModel->where('user_id', $id)->first();
            if ($profile) {
                $user['name'] = $profile['name'] ?? $user['username'];
                $user['phone'] = $profile['phone'] ?? '';
            }
        }

        // Ambil email dari auth_identities
        $identity = $this->identityModel->where(['user_id' => $id, 'type' => 'email_password'])->first();
        $user['email'] = $identity['secret'] ?? '';

        $data = [
            'user' => $user,
            'groups' => $groups,
            'role' => $role,
            'profile' => $profile,
        ];

        // Return HTML untuk modal AJAX
        if ($this->request->isAJAX()) {
            if ($role === 'vendor') {
                return view('admin/users/_form_edit_vendor', $data);
            } else {
                return view('admin/users/_form_edit_seo', $data);
            }
        }

        // Fallback untuk non-AJAX
        if ($role === 'vendor') {
            return view('admin/users/edit_vendor', $data);
        } else {
            return view('admin/users/edit_seo', $data);
        }
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
                $role     = (string) $this->request->getPost('role');
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

                // Update username
                $this->users->update($id, ['username' => $username]);

                // Set group
                $this->setSingleGroup((int) $id, $role);

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

                if ($role === 'seoteam') {
                    // Handle SEO profile
                    $name  = trim((string) $this->request->getPost('fullname'));
                    $phone = trim((string) $this->request->getPost('phone'));
                    
                    $exists = $this->seoModel->where('user_id', $id)->first();
                    $data = [
                        'name'       => $name,
                        'phone'      => $phone,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ];

                    if ($exists) {
                        $this->seoModel->where('user_id', $id)->set($data)->update();
                    } else {
                        $data['user_id']    = $id;
                        $data['status']     = 'active';
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $this->seoModel->insert($data);
                    }
                } elseif ($role === 'vendor') {
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
                            'status' => 'error',
                            'message' => 'Persentase komisi harus antara 0-100%'
                        ]);
                    }

                    $exists = $this->vendorModel->where('user_id', $id)->first();
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
                    ];

                    if ($exists) {
                        $this->vendorModel->where('user_id', $id)->set($data)->update();
                    } else {
                        $data['user_id']    = $id;
                        $data['created_at'] = date('Y-m-d H:i:s');
                        $this->vendorModel->insert($data);
                    }
                }

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User berhasil diupdate'
                ]);

            } catch (\Exception $e) {
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

    // ========== DELETE ==========
    public function delete($id)
    {
        $groups   = $this->getUserGroups((int) $id);
        $isVendor = in_array('vendor', $groups, true);
        $isSeo    = in_array('seoteam', $groups, true);

        if ($this->db->tableExists('auth_groups_users')) {
            $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
        }

        // Hapus dari auth_identities menggunakan model
        $this->identityModel->where('user_id', $id)->delete();

        $this->users->delete($id);

        if ($isVendor) {
            $this->vendorModel->where('user_id', $id)->delete();
        }

        if ($isSeo) {
            $this->seoModel->where('user_id', $id)->delete();
        }

        $tab = $isVendor ? 'vendor' : 'seo';
        return redirect()->to(site_url('admin/users?tab=' . $tab))->with('success', 'User deleted.');
    }

// ========== TOGGLE SUSPEND SEO ==========
public function toggleSuspendSeo($id)
{
    // Handle AJAX request
    if ($this->request->isAJAX()) {
        try {
            log_message('debug', 'Toggle suspend SEO called for ID: ' . $id);
            
            $groups = $this->getUserGroups((int) $id);
            log_message('debug', 'User groups: ' . json_encode($groups));
            
            if (!in_array('seoteam', $groups, true)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Hanya Tim SEO yang bisa di-nonaktifkan.'
                ]);
            }

            $sp = $this->seoModel->where('user_id', $id)->first();
            if (!$sp) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Profil SEO tidak ditemukan.'
                ]);
            }

            log_message('debug', 'Current SEO status: ' . ($sp['status'] ?? 'active'));
            
            $currentStatus = $sp['status'] ?? 'active';
            
            // Tentukan status baru
            if ($currentStatus === 'inactive') {
                $newStatus = 'active';
                $message = 'Tim SEO diaktifkan kembali.';
            } else {
                $newStatus = 'inactive';
                $message = 'Tim SEO dinonaktifkan.';
            }
            
            log_message('debug', 'New SEO status: ' . $newStatus);
            
            // ⭐⭐ PERBAIKAN: Gunakan save() dengan primary key atau update() dengan kondisi yang tepat ⭐⭐
            $updateData = [
                'status' => $newStatus, 
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Cara 1: Gunakan save() dengan menyertakan primary key
            if (isset($sp['id'])) {
                $updateData['id'] = $sp['id'];
                $updateResult = $this->seoModel->save($updateData);
            } 
            // Cara 2: Gunakan where() dengan update()
            else {
                $updateResult = $this->seoModel->where('user_id', $id)->set($updateData)->update();
            }
            
            log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
            
            // Jika updateResult false, cek apakah karena tidak ada perubahan data
            if (!$updateResult) {
                // Cek apakah data sudah sama dengan yang ingin diupdate
                $currentData = $this->seoModel->where('user_id', $id)->first();
                if ($currentData && $currentData['status'] === $newStatus) {
                    log_message('debug', 'No data changes detected, considering as success');
                    $updateResult = true; // Anggap sukses jika tidak ada perubahan
                }
            }
            
            // Cek error database
            $error = $this->seoModel->errors();
            if ($error) {
                log_message('error', 'Model errors: ' . json_encode($error));
                throw new \Exception('Database error: ' . json_encode($error));
            }
            
            log_message('debug', 'Toggle suspend SEO success: ' . $message);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'new_status' => $newStatus,
                'new_label' => ucfirst($newStatus)
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Toggle suspend SEO error: ' . $e->getMessage());
            
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

            log_message('debug', 'Current vendor status: ' . ($vp['status'] ?? 'pending'));
            
            $currentStatus = $vp['status'] ?? 'pending';
            
            // ⭐⭐ LOGIC BARU: Pisahkan status verification dan active/inactive ⭐⭐
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
            
            // ⭐⭐ SIMPAN STATUS VERIFICATION SEBELUMNYA JIKA DINONAKTIFKAN ⭐⭐
            if (!$isCurrentlyActive) {
                // Sedang mengaktifkan - hapus inactive_reason
                $updateData['inactive_reason'] = null;
            } else {
                // Sedang menonaktifkan - simpan status verification saat ini
                $updateData['inactive_reason'] = $currentStatus;
            }
            
            $updateResult = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->update($updateData);
            
            log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
            
            // Cek error database
            $error = $this->db->error();
            if ($error['code'] != 0) {
                log_message('error', 'Database error: ' . json_encode($error));
                throw new \Exception('Database error: ' . $error['message']);
            }
            
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
            log_message('debug', 'Verify vendor called for ID: ' . $id);

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
            
            $updateResult = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->update($updateData);
            
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
            
            $updateResult = $this->db->table('vendor_profiles')
                ->where('user_id', $id)
                ->update($updateData);
            
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
}