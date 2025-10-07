<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SeoProfilesModel;
use App\Models\IdentityModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;

class UserSeo extends BaseController
{
    protected $users;
    protected $db;
    protected $seoModel;
    protected $identityModel;

    public function __construct()
    {
        $this->users         = service('auth')->getProvider();
        $this->db            = db_connect();
        $this->seoModel      = new SeoProfilesModel();
        $this->identityModel = new IdentityModel();
    }

    // ========== LIST SEO ==========
    public function index()
    {
        // Query untuk user SEO
        $seoUsers = $this->db->table('users u')
            ->select('u.id, u.username, sp.name, sp.phone, sp.status as seo_status, ai.secret as email')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->where('agu.group', 'seoteam')
            ->orderBy('u.id', 'DESC')
            ->get()
            ->getResultArray();

        log_message('debug', 'Raw SEO users from DB: ' . json_encode($seoUsers));

        // Transform data dengan benar
        $users = array_map(function ($user) {
            return [
                'id' => (int)$user['id'],
                'username' => $user['username'],
                'name' => $user['name'] ?? '-',
                'phone' => $user['phone'] ?? '-',
                'email' => $user['email'] ?? '-',
                'seo_status' => $user['seo_status'] ?? 'active',
                'groups' => ['seoteam']
            ];
        }, $seoUsers);

        log_message('debug', 'Processed SEO users: ' . json_encode($users));

        return view('admin/userseo/index', [
            'page'        => 'SEO Team Management',
            'users'       => $users,
            'usersSeo'    => $users,
        ]);
    }

    // ========== CREATE SEO ==========
    public function create()
    {
        // Handle AJAX request untuk modal - return HTML langsung
        if ($this->request->isAJAX()) {
            return view('admin/userseo/_form_seo', ['role' => 'seoteam']);
        }

        // fallback untuk non-AJAX
        return view('admin/userseo/create_seo', [
            'page' => 'SEO Team Management',
            'role' => 'seoteam',
        ]);
    }

    // ========== STORE SEO ==========
    public function store()
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                $role     = 'seoteam';
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

                log_message('debug', 'SEO created with ID: ' . $userId);

                // buat email-password identity
                $this->identityModel->saveEmailIdentity($userId, $email, $password);

                // set grup seoteam
                $this->setSingleGroup((int) $userId, $role);

                // seo profile
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
                log_message('debug', 'SEO profile created for user ID: ' . $userId);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Tim SEO berhasil dibuat'
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Store SEO error: ' . $e->getMessage());
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

    // ========== EDIT SEO ==========
    public function edit($id)
    {
        log_message('debug', '=== EDIT SEO METHOD CALLED ===');
        log_message('debug', 'Edit SEO ID from URL: ' . $id);
        
        // Cari user
        $user = $this->users->find((int)$id);
        
        log_message('debug', 'SEO found: ' . ($user ? 'YES - ID: ' . $user->id : 'NO'));
        
        if (!$user) {
            log_message('error', 'SEO not found for editing. ID: ' . $id);
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Tim SEO tidak ditemukan. ID: ' . $id
                ]);
            }
            return redirect()->to(site_url('admin/userseo'))->with('error', 'Tim SEO tidak ditemukan');
        }

        // Convert user object to array dengan data yang benar
        $userArray = [
            'id' => $user->id,
            'username' => $user->username,
        ];
        
        $profile = $this->seoModel->getByUserId($id);
        if ($profile) {
            $userArray = array_merge($userArray, $profile);
            $userArray['name'] = $profile['name'] ?? $userArray['username'];
            $userArray['phone'] = $profile['phone'] ?? '';
            $userArray['seo_status'] = $profile['status'] ?? 'active';
        } else {
            // Jika tidak ada profile, set nilai default
            $userArray['name'] = $userArray['username'];
            $userArray['phone'] = '';
            $userArray['seo_status'] = 'active';
        }

        // Ambil email dari auth_identities
        $identity = $this->identityModel->getEmailIdentity($id);
        $userArray['email'] = $identity['secret'] ?? '';

        $data = [
            'user' => $userArray,
            'role' => 'seoteam',
            'profile' => $profile,
        ];

        log_message('debug', 'Final SEO data for edit: ' . json_encode($userArray));

        // Return HTML untuk modal AJAX
        if ($this->request->isAJAX()) {
            return view('admin/userseo/_form_edit_seo', $data);
        }

        // Fallback untuk non-AJAX
        return view('admin/userseo/edit_seo', $data);
    }

    // ========== UPDATE SEO ==========
    public function update($id = null)
    {
        // Log request data
        log_message('debug', '=== UPDATE SEO METHOD CALLED ===');
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
            
            log_message('debug', 'Final SEO ID: ' . $id);
            
            if (!$id) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'ID Tim SEO tidak valid'
                ]);
            }

            // Cek user exists
            $user = $this->users->find($id);
            
            if (!$user) {
                log_message('debug', 'SEO not found with ID: ' . $id);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tim SEO tidak ditemukan. ID: ' . $id
                ]);
            }

            log_message('debug', 'SEO found: ' . $user->username);
            
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
                log_message('debug', 'SEO username updated');
            }

            // Update email jika diisi dan berubah
            $currentIdentity = $this->identityModel->getEmailIdentity($id);
            if ($email !== '' && $currentIdentity && $currentIdentity['secret'] !== $email) {
                $this->identityModel->saveEmailIdentity($id, $email, $newPass);
                log_message('debug', 'SEO email updated');
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
                log_message('debug', 'SEO password updated');
            }

            // Handle SEO profile
            $name  = trim((string) $this->request->getPost('fullname'));
            $phone = trim((string) $this->request->getPost('phone'));
            
            $profileData = [
                'name'       => $name,
                'phone'      => $phone,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $this->seoModel->getOrCreateByUserId($id, $profileData);
            log_message('debug', 'SEO profile updated');

            log_message('debug', 'SEO updated successfully: ' . $id);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Tim SEO berhasil diupdate'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Update SEO error: '.  $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // ========== DELETE SEO ==========
    public function delete($id)
    {
        try {
            log_message('debug', 'Deleting SEO ID: ' . $id);

            if ($this->db->tableExists('auth_groups_users')) {
                $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
            }

            // Hapus dari auth_identities menggunakan model
            $this->identityModel->where('user_id', $id)->delete();

            // Hapus user
            $this->users->delete($id);

            // Hapus profile SEO
            $this->seoModel->where('user_id', $id)->delete();

            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Tim SEO berhasil dihapus',
                    'redirect' => site_url('admin/userseo')
                ]);
            }

            return redirect()->to(site_url('admin/userseo'))->with('success', 'Tim SEO deleted.');

        } catch (\Exception $e) {
            log_message('error', 'Delete SEO error: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menghapus Tim SEO: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Gagal menghapus Tim SEO: ' . $e->getMessage());
        }
    }

    // ========== TOGGLE SUSPEND SEO ==========
    public function toggleSuspendSeo($id)
    {
        // Handle AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Request harus AJAX'
            ]);
        }

        try {
            log_message('debug', 'Toggle suspend SEO called for ID: ' . $id);
            
            $sp = $this->seoModel->getByUserId($id);
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
            
            // Update status
            $updateResult = $this->seoModel->update($id, [
                'status' => $newStatus, 
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            log_message('debug', 'Update result: ' . ($updateResult ? 'true' : 'false'));
            
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

    // ========== HELPER METHODS ==========
    private function setSingleGroup(int $userId, string $group): void
    {
        if (! in_array($group, ['admin', 'seoteam', 'vendor'], true)) {
            $group = 'seoteam';
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