<?php
// app/Controllers/Admin/Users.php

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

        // Ambil data user dengan join ke seo_profiles dan auth_identities
        $users = [];
        
        if ($currentTab === 'seo') {
            // Query khusus untuk user SEO
            $users = $this->db->table('users u')
                ->select('u.id, u.username, sp.name, sp.phone, sp.status as seo_status, ai.secret as email')
                ->join('auth_groups_users agu', 'agu.user_id = u.id')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
                ->where('agu.group', 'seoteam')
                ->orderBy('u.id', 'DESC')
                ->get()
                ->getResultArray();
            
            // Format data untuk konsistensi
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
            // Query untuk vendor (tetap seperti sebelumnya)
            $list = $this->users->asArray()->orderBy('id', 'DESC')->findAll();
            
            $users = array_map(function (array $u) {
                $u['groups']       = $this->getUserGroups((int) $u['id']);
                $u['vendor_status'] = null;
                $u['seo_status']    = null;

                if (in_array('vendor', $u['groups'], true)) {
                    $vp = $this->vendorModel->select('status, is_verified, commission_rate')
                        ->where('user_id', (int) $u['id'])
                        ->first();
                    $u['vendor_status'] = $vp['status'] ?? 'pending';
                    $u['is_verified'] = (int)($vp['is_verified'] ?? 0) === 1;
                    $u['commission_rate'] = $vp['commission_rate'] ?? null;
                }

                if (in_array('seoteam', $u['groups'], true)) {
                    $sp = $this->seoModel->select('name, phone, status')
                        ->where('user_id', (int) $u['id'])
                        ->first();
                    $u['name'] = $sp['name'] ?? '-';
                    $u['phone'] = $sp['phone'] ?? '-';
                    $u['seo_status'] = $sp['status'] ?? 'active';
                }

                // Ambil email dari auth_identities
                $identity = $this->identityModel->where(['user_id' => $u['id'], 'type' => 'email_password'])->first();
                $u['email'] = $identity['secret'] ?? '-';

                return $u;
            }, $list);
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

    public function store()
    {
        $role     = (string) $this->request->getPost('role'); // admin|seoteam|vendor
        $username = trim((string) $this->request->getPost('username'));
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $name     = trim((string) $this->request->getPost('fullname'));
        $phone    = trim((string) $this->request->getPost('phone'));

        // buat user dasar
        $entity = new ShieldUser(['username' => $username]);
        $userId = $this->users->insert($entity, true);
        if (! $userId) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat user');
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
            $vendorStatus = $this->request->getPost('vendor_status') ?? 'pending';
            $isVerified = (int) $this->request->getPost('is_verified') === 1;
            $commissionRate = $this->request->getPost('commission_rate');
            
            $this->vendorModel->insert([
                'user_id'         => $userId,
                'status'          => $vendorStatus,
                'is_verified'     => $isVerified ? 1 : 0,
                'commission_rate' => $commissionRate !== '' ? (float) $commissionRate : null,
                'created_at'      => date('Y-m-d H:i:s'),
                'updated_at'      => date('Y-m-d H:i:s'),
            ]);
        }

        // seo profile
        if ($role === 'seoteam') {
            $this->seoModel->insert([
                'user_id'     => $userId,
                'name'        => $name,
                'phone'       => $phone,
                'status'      => 'active',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);
        }

        $tab = $role === 'vendor' ? 'vendor' : 'seo';
        return redirect()->to(site_url('admin/users?tab=' . $tab))->with('success', 'User created.');
    }

    public function edit($id)
    {
        $role = $this->request->getGet('role') ?? 'seoteam';

        $user = $this->users->asArray()->find($id);
        if (! $user) {
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'User tidak ditemukan']);
            }
            return redirect()->to(site_url('admin/users'))->with('error', 'User tidak ditemukan');
        }

        $groups  = $this->getUserGroups((int) $id);
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
            'page'          => 'Users',
            'user'          => $user,
            'groups'        => $groups,
            'role'          => $role,
            'profile'       => $profile,
            'vendorProfile' => $profile,
        ];

        // Tampilkan view yang sesuai berdasarkan role
        if ($role === 'vendor') {
            return view('admin/users/edit_vendor', $data);
        } else {
            return view('admin/users/edit_seo', $data);
        }
    }

    // ========== UPDATE ==========
    public function update($id)
    {
        $username = trim((string) $this->request->getPost('username'));
        $role     = (string) $this->request->getPost('role');
        $newPass  = (string) $this->request->getPost('password');
        $email    = trim((string) $this->request->getPost('email'));

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
            $fullname       = trim((string) $this->request->getPost('fullname'));
            $phone          = trim((string) $this->request->getPost('phone'));
            $vendorStatus   = (string) $this->request->getPost('vendor_status');
            $isVerified     = (int) $this->request->getPost('is_verified') === 1;
            $commissionRate = $this->request->getPost('commission_rate');

            $exists = $this->vendorModel->where('user_id', $id)->first();
            $data = [
                'status'         => $vendorStatus,
                'is_verified'    => $isVerified ? 1 : 0,
                'commission_rate' => $commissionRate !== '' ? (float) $commissionRate : null,
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            if ($exists) {
                $this->vendorModel->where('user_id', $id)->set($data)->update();
            } else {
                $data['user_id']    = $id;
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->vendorModel->insert($data);
            }
        }

        $tab = $role === 'vendor' ? 'vendor' : 'seo';
        return redirect()->to(site_url('admin/users?tab=' . $tab))->with('success', 'User updated.');
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

    // ========== TOGGLE SUSPEND ==========
    public function toggleSuspend($id)
    {
        $groups = $this->getUserGroups((int) $id);
        if (! in_array('vendor', $groups, true)) {
            return redirect()->back()->with('error', 'Hanya vendor yang bisa di-nonaktifkan.');
        }

        $vp = $this->vendorModel->where('user_id', $id)->first();
        if (! $vp) {
            return redirect()->back()->with('error', 'Profil vendor tidak ditemukan.');
        }

        $new = (($vp['status'] ?? 'active') === 'suspended') ? 'active' : 'suspended';
        $this->vendorModel->where('user_id', $id)
            ->set(['status' => $new, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();

        $msg = $new === 'suspended' ? 'Vendor dinonaktifkan.' : 'Vendor diaktifkan kembali.';
        return redirect()->back()->with('success', $msg);
    }

    public function toggleSuspendSeo($id)
    {
        $groups = $this->getUserGroups((int) $id);
        if (! in_array('seoteam', $groups, true)) {
            return redirect()->back()->with('error', 'Hanya Tim SEO yang bisa di-nonaktifkan.');
        }

        $sp = $this->seoModel->where('user_id', $id)->first();
        if (! $sp) {
            return redirect()->back()->with('error', 'Profil SEO tidak ditemukan.');
        }

        $new = (($sp['status'] ?? 'active') === 'inactive') ? 'active' : 'inactive';
        $this->seoModel->where('user_id', $id)
            ->set(['status' => $new, 'updated_at' => date('Y-m-d H:i:s')])
            ->update();

        $msg = $new === 'inactive' ? 'Tim SEO dinonaktifkan.' : 'Tim SEO diaktifkan kembali.';
        return redirect()->back()->with('success', $msg);
    }

    // ========== HELPERS ==========
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