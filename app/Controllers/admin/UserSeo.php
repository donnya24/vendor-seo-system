<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\SeoProfilesModel;
use App\Models\IdentityModel;
use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;
use CodeIgniter\Database\Exceptions\DatabaseException;

class UserSeo extends BaseController
{
    protected $users;
    protected $db;
    protected $seoModel;
    protected $identityModel;
    protected $userModel;

    public function __construct()
    {
        $this->users         = service('auth')->getProvider();
        $this->db            = db_connect();
        $this->seoModel      = new SeoProfilesModel();
        $this->identityModel = new IdentityModel();
        $this->userModel     = new UserModel();
    }

    // ========== LIST ==========
    public function index()
    {
        // Query untuk user SEO
        // Query ini sudah benar untuk hard delete, karena jika user dihapus,
        // data di auth_groups_users juga hilang, sehingga user tidak akan muncul.
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

        return view('admin/userseo/index', [
            'page'  => 'Users SEO',
            'users' => $users,
        ]);
    }

    // ========== CREATE ==========
    public function create()
    {
        // Handle AJAX request untuk modal - return HTML langsung
        if ($this->request->isAJAX()) {
            return view('admin/userseo/modal_create');
        }

        // fallback untuk non-AJAX
        return view('admin/userseo/create', [
            'page' => 'Users SEO',
        ]);
    }

    // ========== STORE ==========
    public function store()
    {
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                $username = trim((string) $this->request->getPost('username'));
                $email    = trim((string) $this->request->getPost('email'));
                $password = (string) $this->request->getPost('password');
                $fullname = trim((string) $this->request->getPost('fullname'));
                $phone    = trim((string) $this->request->getPost('phone'));
                
                // Validasi input required
                if (empty($username) || empty($email) || empty($password) || empty($fullname)) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Semua field wajib diisi'
                    ]);
                }

                // Validasi password
                if (strlen($password) < 8) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Password minimal 8 karakter',
                        'field' => 'password'
                    ]);
                }

                // Validasi konfirmasi password
                $password_confirm = $this->request->getPost('password_confirm');
                if ($password !== $password_confirm) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Konfirmasi password tidak sama',
                        'field' => 'password_confirm'
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
                
                if (!$userId) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal membuat user'
                    ]);
                }

                // buat email-password identity dengan nama
                $this->identityModel->insert([
                    'user_id'    => (int) $userId,
                    'type'       => 'email_password',
                    'secret'     => $email,
                    'secret2'    => password_hash($password, PASSWORD_DEFAULT),
                    'name'       => $fullname, // Tambahkan nama lengkap
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                // Aktifkan user
                $this->userModel->activateUser($userId);

                // set grup tunggal
                $this->setSingleGroup((int) $userId, 'seoteam');

                // seo profile
                $this->seoModel->insert([
                    'user_id'     => $userId,
                    'name'        => $fullname,
                    'phone'       => $phone,
                    'status'      => 'active',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

                log_message('info', "User SEO berhasil dibuat: {$username} ({$email})");

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User SEO berhasil dibuat'
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
        $user = $this->users->asArray()->find($id);
        if (!$user) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan'
                ]);
            }
            return redirect()->to(site_url('admin/userseo'))->with('error', 'User tidak ditemukan');
        }

        $groups = $this->getUserGroups((int)$id);
        $profile = $this->seoModel->where('user_id', $id)->first();
        
        // Ambil nama dari auth_identities
        $identity = $this->identityModel->where(['user_id' => $id, 'type' => 'email_password'])->first();
        $user['name'] = $identity['name'] ?? ($profile['name'] ?? $user['username']);
        $user['phone'] = $profile['phone'] ?? '';
        $user['email'] = $identity['secret'] ?? '';

        $data = [
            'user' => $user,
            'groups' => $groups,
            'profile' => $profile,
        ];

        // Return HTML untuk modal AJAX
        if ($this->request->isAJAX()) {
            return view('admin/userseo/modal_edit', $data);
        }

        // Fallback untuk non-AJAX
        return view('admin/userseo/edit', $data);
    }

    // ========== UPDATE ==========//
    public function update($id = null)
    {
        log_message('info', 'UPDATE REQUEST: ID=' . $id . ', POST DATA=' . json_encode($this->request->getPost()));
        
        // Handle AJAX request
        if ($this->request->isAJAX()) {
            try {
                // Pastikan ID tersedia
                if (!$id) {
                    $id = $this->request->getPost('id');
                }

                if (!$id) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'ID User tidak valid.'
                    ]);
                }
                
                $username = trim((string) $this->request->getPost('username'));
                $newPass  = (string) $this->request->getPost('password');
                $email    = trim((string) $this->request->getPost('email'));
                $fullname = trim((string) $this->request->getPost('fullname'));
                $phone    = trim((string) $this->request->getPost('phone'));

                // Cek user exists
                $user = $this->users->find($id);
                if (!$user) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'User tidak ditemukan'
                    ]);
                }

                // --- PERBAIKAN: Gunakan notasi objek (->) bukan array (['']) ---
                // Cek duplikasi username jika berubah
                if ($username !== $user->username) { // PERBAIKAN DI SINI
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
                $this->setSingleGroup((int) $id, 'seoteam');

                // --- PERBAIKAN LOGIKA UPDATE ---
                // Selalu update nama di auth_identities dan seo_profiles
                if (!empty($email)) {
                    // Update email dan nama jika email diisi
                    $this->updateEmailIdentity((int) $id, $email, $fullname);
                } else {
                    // Jika email tidak diubah, tetap update nama
                    $this->updateIdentityName((int) $id, $fullname);
                }

                // Update password jika diisi
                if (!empty($newPass)) {
                    if (strlen($newPass) < 8) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Password minimal 8 karakter',
                            'field' => 'password'
                        ]);
                    }
                    
                    // Validasi konfirmasi password
                    $password_confirm = $this->request->getPost('password_confirm');
                    if ($newPass !== $password_confirm) {
                        return $this->response->setJSON([
                            'status' => 'error',
                            'message' => 'Konfirmasi password tidak sama',
                            'field' => 'password_confirm'
                        ]);
                    }
                    
                    $this->resetPasswordByEmailIdentity((int) $id, $newPass);
                }

                // Handle SEO profile
                $exists = $this->seoModel->where('user_id', $id)->first();
                $data = [
                    'name'       => $fullname,
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

                log_message('info', "User SEO berhasil diupdate: {$username}");

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User SEO berhasil diupdate'
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
                $groups = $this->getUserGroups((int) $id);
                $isSeo  = in_array('seoteam', $groups, true);

                if (!$isSeo) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'User bukan Tim SEO'
                    ]);
                }

                // Hapus dari tabel terkait terlebih dahulu untuk menjaga integritas
                if ($this->db->tableExists('auth_groups_users')) {
                    $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
                }

                // Hapus dari auth_identities menggunakan model
                $this->identityModel->where('user_id', $id)->delete();

                // Hapus dari seo_profiles
                $this->seoModel->where('user_id', $id)->delete();

                // =================================================================
                // PERUBAHAN UTAMA: Hard delete user dari tabel 'users'
                // Parameter 'false' memaksa penghapusan permanen
                // =================================================================
                $this->users->delete($id, false);

                log_message('warning', "User SEO berhasil dihapus PERMANEN: ID {$id}");

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'User SEO berhasil dihapus secara permanen'
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Delete Error: ' . $e->getMessage());
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
                ]);
            }
        }

        // Fallback untuk non-AJAX (redirect)
        $groups = $this->getUserGroups((int) $id);
        $isSeo  = in_array('seoteam', $groups, true);

        if (!$isSeo) {
            return redirect()->to(site_url('admin/userseo'))->with('error', 'User bukan Tim SEO');
        }

        if ($this->db->tableExists('auth_groups_users')) {
            $this->db->table('auth_groups_users')->where('user_id', $id)->delete();
        }

        $this->identityModel->where('user_id', $id)->delete();
        $this->seoModel->where('user_id', $id)->delete();
        
        // =================================================================
        // PERUBAHAN UTAMA: Hard delete user dari tabel 'users'
        // =================================================================
        $this->users->delete($id, false);

        return redirect()->to(site_url('admin/userseo'))->with('success', 'User SEO berhasil dihapus secara permanen.');
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
                        'status' => 'error',
                        'message' => 'Hanya Tim SEO yang bisa di-nonaktifkan.'
                    ]);
                }

                $sp = $this->seoModel->where('user_id', $id)->first();
                if (!$sp) {
                    return $this->response->setJSON([
                        'status' => 'error',
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
                    'status' => 'success',
                    'message' => $message,
                    'new_status' => $newStatus,
                    'new_label' => ucfirst($newStatus)
                ]);

            } catch (\Exception $e) {
                log_message('error', 'Toggle suspend SEO error: ' . $e->getMessage());
                
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
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

    private function resetPasswordByEmailIdentity(int $userId, string $newPass): void
    {
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $this->identityModel
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->set('secret2', $hash)
            ->update();
    }

    private function updateEmailIdentity(int $userId, string $email, string $name = null): void
    {
        $data = ['secret' => $email];
        
        // Tambahkan nama jika ada
        if ($name) {
            $data['name'] = $name;
        }
        
        $this->identityModel
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->set($data)
            ->update();
    }
    
    private function updateIdentityName(int $userId, string $name): void
    {
        $this->identityModel
            ->where(['user_id' => $userId, 'type' => 'email_password'])
            ->set('name', $name)
            ->update();
    }
}