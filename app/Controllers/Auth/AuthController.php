<?php
namespace App\Controllers\Auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use App\Models\ActivityLogsModel; // Tambahkan ini

class AuthController extends Controller
{
    protected $auth;
    protected $activityLogsModel; // Tambahkan property untuk model

    public function __construct()
    {
        $this->auth = service('auth');
        $this->activityLogsModel = new ActivityLogsModel(); // Inisialisasi model
    }

    // ===== LOGIN =====
    public function login()
    {
        if ($this->auth->loggedIn()) {
            return $this->redirectByRole($this->auth->user());
        }
        // view path gunakan lowercase "auth/login"
        return view('auth/login');
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
            if ($remember === true) {
                helper('auth_remember');
                try {
                    force_remember_token((int) $this->auth->user()->id);
                } catch (\Throwable $e) {
                    log_message('error', 'force_remember_token failed: ' . $e->getMessage());
                }
            }
            
            // Log activity untuk login berhasil
            $user = $this->auth->user();
            $this->logActivity($user->id, null, 'login', 'success', 'Login berhasil ke sistem');
            
            return $this->redirectByRole($this->auth->user());
        }

        // Log activity untuk login gagal
        $this->logActivity(null, null, 'login', 'failed', 'Login gagal - kredensial salah', [
            'email' => $this->request->getPost('email'),
            'ip' => $this->request->getIPAddress()
        ]);
        
        return redirect()->back()->withInput()->with('error', 'Login gagal. Periksa kembali kredensial Anda.');
    }

    // ===== LOGOUT =====
    public function logout()
    {
        // Log activity sebelum logout
        if ($this->auth->loggedIn()) {
            $user = $this->auth->user();
            $this->logActivity($user->id, null, 'logout', 'success', 'Logout dari sistem');
        }

        // Hapus remember token jika ada
        helper('auth_remember');
        try {
            forget_remember_token_from_cookie();
        } catch (\Throwable $e) {
            log_message('error', 'forget_remember_token_from_cookie failed: ' . $e->getMessage());
        }

        // Logout menggunakan Shield
        $this->auth->logout();
        
        // Hancurkan session
        session()->destroy();

        // Redirect ke halaman login dengan pesan sukses
        return redirect()->to('/login')->with('success', 'Anda telah berhasil keluar.');
    }

    // ===== REMEMBER STATUS =====
    public function checkRememberStatus()
    {
        helper('vendoruser'); // fungsi helper custom kamu

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
            'user_id'   => $user->id,
            'username'  => $user->username,
            'email'     => get_identity_email((int) $user->id),
            'remember_tokens_count' => count($tokens),
            'has_remember_cookie'   => isset($_COOKIE['remember']),
        ]);
    }

    // ========== REGISTER (FORM) ==========
    public function registerForm()
    {
        return view('auth/register_vendor');
    }

    // ========== REGISTER (PROSES) ==========
    public function registerProcess()
    {
        helper('vendoruser');

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
            // Log activity untuk validasi gagal
            $this->logActivity(null, null, 'register', 'failed', 'Validasi pendaftaran gagal', [
                'errors' => $validation->getErrors(),
                'email' => $this->request->getPost('email')
            ]);
            
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
            resolve_identity_table();
            if (identity_exists($email)) {
                // Log activity untuk email sudah terdaftar
                $this->logActivity(null, null, 'register', 'failed', 'Email sudah terdaftar', [
                    'email' => $email
                ]);
                
                return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
            }

            $db->transException(true);
            $db->transStart();

            // 1) Buat user Shield
            $username   = make_unique_username($vendorName, $email);
            $userEntity = new \CodeIgniter\Shield\Entities\User(['username' => $username]);
            $userId     = $users->insert($userEntity, true);
            if (! $userId) {
                $errs = method_exists($users, 'errors') ? $users->errors() : [];
                throw new \RuntimeException('Gagal membuat akun user. ' . implode(', ', (array) $errs));
            }

            // 2) Identitas email+password & grup vendor
            create_email_password_identity((int) $userId, $email, $password);
            assign_user_to_group((int) $userId, 'vendor');

            // **Tambahkan update 'name' di auth_identities**
            $db->table('auth_identities')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->update(['name' => $vendorName]);

            // 3) Buat profil vendor
            $vendorProfileId = null;
            if ($db->tableExists('vendor_profiles')) {
                $hasBusiness = $db->query("SHOW COLUMNS FROM vendor_profiles LIKE 'business_name'")->getNumRows() > 0;
                $hasOwner    = $db->query("SHOW COLUMNS FROM vendor_profiles LIKE 'owner_name'")->getNumRows() > 0;
                $hasName     = $db->query("SHOW COLUMNS FROM vendor_profiles LIKE 'name'")->getNumRows() > 0;
                $hasPhone    = $db->query("SHOW COLUMNS FROM vendor_profiles LIKE 'phone'")->getNumRows() > 0;

                $data = [
                    'user_id'     => $userId,
                    'is_verified' => 0,
                    'status'      => 'pending',
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ];
                if ($hasBusiness) $data['business_name'] = $vendorName;
                if ($hasOwner)    $data['owner_name']    = $vendorName;
                if ($hasName)     $data['name']          = $vendorName;
                if ($hasPhone)    $data['phone']         = '-';

                $db->table('vendor_profiles')->insert($data);
                $vendorProfileId = $db->insertID(); // ← dapatkan ID profil vendor
            }

            // 4) Catat aktivitas registrasi ke activity_logs
            $this->logActivity($userId, $vendorProfileId, 'register', 'success', "Vendor '$vendorName' mendaftar ke sistem", [
                'vendor_name' => $vendorName,
                'email' => $email
            ]);

            $db->transComplete();

            return redirect()->to('/login')->with('message', 'Akun vendor berhasil dibuat. Silakan login.');
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            
            // Log activity untuk error registrasi
            $this->logActivity(null, null, 'register', 'error', 'Registrasi vendor gagal: ' . $e->getMessage(), [
                'email' => $email,
                'vendor_name' => $vendorName
            ]);
            
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
        if ($user->inGroup('admin'))   return redirect()->to('/admin/dashboard');
        if ($user->inGroup('seoteam')) return redirect()->to('/seo/dashboard');
        if ($user->inGroup('vendor'))  return redirect()->to('/vendoruser/dashboard'); // <- pakai Vendoruser
        return redirect()->to('/');
    }
    
    // ===== LOG ACTIVITY METHOD =====
    private function logActivity($userId = null, $vendorId = null, $action, $status, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'vendor_id'   => $vendorId,
                'module'      => 'auth',
                'action'      => $action,
                'status'      => $status,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            
            // Gabungkan dengan data tambahan jika ada
            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }
            
            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}