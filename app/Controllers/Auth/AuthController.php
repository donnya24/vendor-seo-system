<?php
namespace App\Controllers\Auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;

class AuthController extends Controller
{
    protected $auth;

    public function __construct()
    {
        $this->auth = service('auth');
    }

    // ===== LOGIN =====
    public function login()
    {
        if ($this->auth->loggedIn()) {
            return $this->redirectByRole($this->auth->user());
        }
        return view('Auth/login');
    }

    // ===== ATTEMPT LOGIN =====
    public function attemptLogin()
    {
        $validation = service('validation');
        $validation->setRules([
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('error', 'Email dan password harus diisi dengan benar.');
        }

        $remember = (bool) $this->request->getPost('remember');

        $result = $this->auth->attempt([
            'email'    => (string) $this->request->getPost('email'),
            'password' => (string) $this->request->getPost('password'),
        ], $remember);

        if ($result->isOK()) {
            return $this->redirectByRole($this->auth->user());
        }

        // (opsional) mapping error khusus di sini
        return redirect()->back()->withInput()->with('error', 'Login gagal. Periksa kembali kredensial Anda.');
    }

    // ===== LOGOUT =====
    public function logout()
    {
        // Logout jika request adalah POST
        if (! $this->request->is('post')) {
            return redirect()->to('/login');
        }

        $this->auth->logout();  // Proses logout
        session()->destroy();   // Hancurkan sesi

        return redirect()->to('/login')->with('success', 'Anda telah berhasil keluar.');
    }

    // ===== CHECK REMEMBER ME STATUS (debug) =====
    public function checkRememberStatus()
    {
        helper('vendor'); // ambil email dari identity, karena kolom email tidak ada di tabel users

        if (! $this->auth->loggedIn()) {
            return $this->response->setJSON(['logged_in' => false]);
        }

        $user = $this->auth->user();

        $rememberModel = model('CodeIgniter\Shield\Models\RememberModel');
        $tokens = $rememberModel->where('user_id', $user->id)
                                ->where('expires >', date('Y-m-d H:i:s'))
                                ->findAll();

        return $this->response->setJSON([
            'logged_in' => true,
            'user_id' => $user->id,
            'username' => $user->username,
            'email' => get_identity_email((int) $user->id), // ambil dari auth_identities.secret
            'remember_tokens_count' => count($tokens),
            'has_remember_cookie' => isset($_COOKIE['remember']),
        ]);
    }

    // ========== REGISTER (FORM) ==========
    public function registerForm()
    {
        return view('Auth/register_vendor');
    }

    // ========== REGISTER (PROSES) ==========
    public function registerProcess()
    {
        helper('vendor'); // helper util kita

        $validation = service('validation');
        $validation->setRules([
            'vendor_name'  => 'required|min_length[3]',
            'email'        => 'required|valid_email',
            'password'     => 'required|min_length[8]',
            'pass_confirm' => 'required|matches[password]',
        ], [
            'pass_confirm' => ['matches' => 'Konfirmasi kata sandi tidak cocok.'],
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()
                ->withInput()
                ->with('error', implode('<br>', $validation->getErrors()));
        }

        $email      = trim((string) $this->request->getPost('email'));
        $password   = (string) $this->request->getPost('password');
        $vendorName = trim((string) $this->request->getPost('vendor_name'));

        $db    = db_connect();
        $users = $this->auth->getProvider();

        try {
            // Pastikan tabel identity ada & email belum dipakai
            resolve_identity_table();
            if (identity_exists($email)) {
                return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
            }

            $db->transException(true);
            $db->transStart();

            // 1) Insert user ke tabel `users` (WAJIB ada data valid supaya tidak "There is no data to insert")
            $username   = make_unique_username($vendorName, $email);
            $userEntity = new User([
                'username' => $username,
                'active'   => 1,
            ]);
            $userId = $users->insert($userEntity);
            if (! $userId) {
                $errs = method_exists($users, 'errors') ? $users->errors() : [];
                throw new \RuntimeException('Gagal membuat akun user. ' . implode(', ', (array) $errs));
            }

            // 2) Simpan kredensial (email di `secret`, HASH password di `secret2`)
            create_email_password_identity((int) $userId, $email, $password);

            // 3) Masukkan user ke grup 'vendor' (pivot auth_groups_users)
            assign_user_to_group((int) $userId, 'vendor');

            // 4) (opsional) profil vendor jika tabelnya ada
            if ($db->tableExists('vendor_profiles')) {
                $db->table('vendor_profiles')->insert([
                    'user_id'     => $userId,
                    'name'        => $vendorName,
                    'is_verified' => 0,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }

            $db->transComplete();

            return redirect()->to('/login')->with('message', 'Akun vendor berhasil dibuat. Silakan login.');
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            log_message('error', 'Register vendor failed: ' . $e->getMessage());

            $msg = (defined('ENVIRONMENT') && ENVIRONMENT !== 'production')
                ? 'Registrasi gagal: ' . $e->getMessage()
                : 'Registrasi gagal. Coba lagi.';

            return redirect()->back()->withInput()->with('error', $msg);
        }
    }

    // ===== REDIRECT BY ROLE =====
    private function redirectByRole($user)
    {
        if ($user->inGroup('admin')) {
            return redirect()->to('/admin/dashboard');
        }
        if ($user->inGroup('seo_team')) {
            return redirect()->to('/seo_team/dashboard');
        }
        if ($user->inGroup('vendor')) {
            return redirect()->to('/vendor/dashboard');
        }
        return redirect()->to('/dashboard');
    }
}
