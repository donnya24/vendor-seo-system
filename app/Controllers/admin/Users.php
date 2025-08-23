<?php
// app/Controllers/Admin/Users.php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use CodeIgniter\Shield\Entities\User as ShieldUser;

class Users extends BaseController
{
    protected $users;
    protected $db;

    public function __construct()
    {
        $this->users = service('auth')->getProvider(); // Shield UserModel
        $this->db    = db_connect();
    }

    // ========== LIST ==========
    public function index()
    {
        // ✅ Ambil sebagai array agar $u['id'] valid
        $list = $this->users->asArray()->orderBy('id', 'DESC')->findAll();

        // Grup & status vendor
        $users = array_map(function (array $u) {
            $u['groups'] = $this->getUserGroups((int) $u['id']);
            $u['vendor_status'] = null;

            if (in_array('vendor', $u['groups'], true)) {
                $vp = (new VendorProfilesModel())
                    ->select('status')
                    ->where('user_id', (int) $u['id'])
                    ->first();
                $u['vendor_status'] = $vp['status'] ?? null; // active/pending/verified/suspended
            }
            return $u;
        }, $list);

        return view('admin/users/index', ['page' => 'Users', 'users' => $users]);
    }

    // ========== CREATE ==========
    public function create()
    {
        return view('admin/users/create', ['page' => 'Users']);
    }

    public function store()
    {
        $role     = (string) $this->request->getPost('role'); // admin|seoteam|vendor
        $username = trim((string) $this->request->getPost('username'));
        $email    = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        // 1) Buat user dasar (Shield entity)
        $entity = new ShieldUser(['username' => $username]);
        $userId = $this->users->insert($entity, true);
        if (! $userId) {
            return redirect()->back()->withInput()->with('error', 'Gagal membuat user');
        }

        // 2) Tambah identitas email+password (Shield)
        if (! function_exists('create_email_password_identity')) {
            // fallback sederhana tanpa helper
            if ($this->db->tableExists('auth_identities')) {
                $this->db->table('auth_identities')->insert([
                    'user_id'    => (int) $userId,
                    'type'       => 'email_password',
                    'secret'     => password_hash($password, PASSWORD_DEFAULT),
                    'extra'      => json_encode(['email' => $email]),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        } else {
            helper('vendoruser');
            create_email_password_identity((int) $userId, $email, $password);
        }

        // 3) Tetapkan 1 grup
        $this->setSingleGroup((int) $userId, $role);

        // 4) Jika vendor: pastikan ada baris vendor_profiles (jika tabel ada)
        if ($role === 'vendor' && $this->db->tableExists('vendor_profiles')) {
            $exists = (new VendorProfilesModel())->where('user_id', $userId)->first();
            if (! $exists) {
                (new VendorProfilesModel())->insert([
                    'user_id'     => $userId,
                    'status'      => 'pending',
                    'is_verified' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }

        return redirect()->to(site_url('admin/users'))->with('success', 'User created.');
    }

    // ========== EDIT/UPDATE ==========
    public function edit($id)
    {
        // ✅ Ambil sebagai array agar view mudah pakai $user['username']
        $user = $this->users->asArray()->find($id);
        if (! $user) {
            return redirect()->to(site_url('admin/users'))->with('error', 'User tidak ditemukan');
        }

        $groups = $this->getUserGroups((int) $id);
        return view('admin/users/edit', ['page' => 'Users', 'user' => $user, 'groups' => $groups]);
    }

    public function update($id)
    {
        $username = trim((string) $this->request->getPost('username'));
        $role     = (string) $this->request->getPost('role'); // admin|seoteam|vendor
        $newPass  = (string) $this->request->getPost('password');

        // update username
        $this->users->update($id, ['username' => $username]);

        // set 1 grup saja (replace)
        $this->setSingleGroup((int) $id, $role);

        // optional reset password
        if ($newPass !== '') {
            $this->resetPasswordByEmailIdentity((int) $id, $newPass);
        }

        return redirect()->to(site_url('admin/users'))->with('success', 'User updated.');
    }

    // ========== DELETE ==========
    public function delete($id)
    {
        // Hapus mapping group
        if ($this->db->tableExists('auth_groups_users')) {
            $this->db->table('auth_groups_users')->where('user_id', (int) $id)->delete();
        }

        // Opsional: hapus identities
        if ($this->db->tableExists('auth_identities')) {
            $this->db->table('auth_identities')->where('user_id', (int) $id)->delete();
        }

        // Hapus user
        $this->users->delete($id);

        // Opsional: hapus profil vendor
        if ($this->db->tableExists('vendor_profiles')) {
            (new VendorProfilesModel())->where('user_id', (int) $id)->delete();
        }

        return redirect()->to(site_url('admin/users'))->with('success', 'User deleted.');
    }

    // ========== SUSPEND/UNSUSPEND VENDOR ==========
    public function toggleSuspend($id)
    {
        // Nonaktifkan/aktifkan vendor (level profil vendor, bukan login global)
        $groups = $this->getUserGroups((int) $id);
        if (! in_array('vendor', $groups, true)) {
            return redirect()->back()->with('error', 'Hanya vendor yang bisa di-nonaktifkan.');
        }

        $vpModel = new VendorProfilesModel();
        $vp = $vpModel->where('user_id', (int) $id)->first();
        if (! $vp) {
            return redirect()->back()->with('error', 'Profil vendor tidak ditemukan.');
        }

        $new = (($vp['status'] ?? 'active') === 'suspended') ? 'active' : 'suspended';
        $vpModel->where('user_id', (int) $id)
                ->set(['status' => $new, 'updated_at' => date('Y-m-d H:i:s')])
                ->update();

        $msg = $new === 'suspended' ? 'Vendor dinonaktifkan.' : 'Vendor diaktifkan kembali.';
        return redirect()->back()->with('success', $msg);
    }

    // ================= helpers =================
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
        if ($this->db->tableExists('auth_identities')) {
            $hash = password_hash($newPass, PASSWORD_DEFAULT);
            $this->db->table('auth_identities')
                ->where(['user_id' => $userId, 'type' => 'email_password'])
                ->set('secret', $hash)
                ->update();
        }
    }
}
