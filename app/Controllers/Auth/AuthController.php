<?php
namespace App\Controllers\Auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use App\Models\VendorProfilesModel;

class AuthController extends Controller
{
    protected $auth;
    protected $vendorProfilesModel;

    public function __construct()
    {
        $this->auth = service('auth');
        $this->vendorProfilesModel = new VendorProfilesModel();
    }

    // ===== LOGIN =====
    public function login()
    {
        if ($this->auth->loggedIn()) {
            return $this->redirectByRole($this->auth->user());
        }
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
            // Log activity untuk validasi login gagal menggunakan helper
            log_activity_auto('login_failed', 'Validasi login gagal - ' . json_encode($validation->getErrors()), [
                'module' => 'auth',
                'email' => $this->request->getPost('email')
            ]);
            
            return redirect()->back()->withInput()->with('error', 'Email dan password harus diisi dengan benar.');
        }

        $remember = (bool) $this->request->getPost('remember');
        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        $result = $this->auth->attempt([
            'email'    => $email,
            'password' => $password,
        ], $remember);

        if ($result->isOK()) {
            $user = $this->auth->user();
            
            if ($remember === true) {
                helper('auth_remember');
                try {
                    force_remember_token((int) $user->id);
                } catch (\Throwable $e) {
                    log_message('error', 'force_remember_token failed: ' . $e->getMessage());
                }
            }
            
            // Log activity untuk login berhasil menggunakan helper
            $this->logLoginSuccess($user);
            
            return $this->redirectByRole($user);
        }

        // Log activity untuk login gagal menggunakan helper
        log_activity_auto('login_failed', 'Login gagal - kredensial salah untuk email: ' . $email, [
            'module' => 'auth'
        ]);
        
        return redirect()->back()->withInput()->with('error', 'Login gagal. Periksa kembali kredensial Anda.');
    }

    // ===== LOGOUT =====
    public function logout()
    {
        $user = null;
        
        // Ambil data user sebelum logout
        if ($this->auth->loggedIn()) {
            $user = $this->auth->user();
            
            // Log activity sebelum logout menggunakan helper
            $this->logLogout($user);
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
        helper('vendoruser');

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
        helper(['vendoruser', 'activity']);

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
            // Log activity untuk validasi gagal menggunakan helper
            log_activity_auto('register_failed', 'Validasi pendaftaran vendor gagal - ' . json_encode($validation->getErrors()), [
                'module' => 'auth',
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
                // Log activity untuk email sudah terdaftar menggunakan helper
                log_activity_auto('register_failed', 'Email sudah terdaftar: ' . $email, [
                    'module' => 'auth'
                ]);
                
                return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
            }

            $db->transException(true);
            $db->transStart();

            // 1) Buat user Shield dengan status ACTIVE
            $username   = make_unique_username($vendorName, $email);
            
            // Buat user entity dengan status active
            $userEntity = new User([
                'username' => $username,
                'status'   => 'active', // Set status langsung active
                'active'   => 1         // Set active = 1
            ]);
            
            $userId = $users->insert($userEntity, true);

            // 2) Identitas email+password & grup vendor
            create_email_password_identity((int) $userId, $email, $password);
            assign_user_to_group((int) $userId, 'vendor');

            // **Tambahkan update 'name' di auth_identities**
            $db->table('auth_identities')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->update(['name' => $vendorName]);

            // 3) Buat profil vendor - HAPUS is_verified
            $vendorProfileId = null;
            
            $vendorData = [
                'user_id'        => $userId,
                'business_name'  => $vendorName,
                'owner_name'     => $vendorName,
                'phone'          => '-',
                'whatsapp_number'=> '-',
                'status'         => 'pending',
            ];

            $vendorProfileId = $this->vendorProfilesModel->insert($vendorData);

            // 4) Catat aktivitas registrasi berhasil menggunakan helper
            $this->logRegisterSuccess($userId, $vendorName, $email, $vendorProfileId);

            $db->transComplete();

            return redirect()->to('/login')->with('message', 'Akun vendor berhasil dibuat. Silakan login.');
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            
            // Log activity untuk error registrasi menggunakan helper
            log_activity_auto('register_error', 'Registrasi vendor gagal: ' . $e->getMessage(), [
                'module' => 'auth',
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
        if ($user->inGroup('vendor'))  return redirect()->to('/vendoruser/dashboard');
        return redirect()->to('/');
    }
    
    // ===== LOGIN SUCCESS METHOD =====
    private function logLoginSuccess($user)
    {
        if (!$user) {
            log_message('error', 'logLoginSuccess: User is null');
            return;
        }
        
        $role = $this->getUserRole($user);
        $description = "Login berhasil sebagai {$role} - {$user->username}";
        
        // Gunakan helper untuk log activity
        log_activity_auto('login', $description, [
            'module' => 'auth'
        ]);

        // Debug info
        log_message('info', "Login success - User ID: {$user->id}, Username: {$user->username}, Role: {$role}");
    }

    // ===== LOGOUT METHOD =====
    private function logLogout($user)
    {
        if (!$user) {
            log_message('error', 'logLogout: User is null');
            return;
        }
        
        $role = $this->getUserRole($user);
        $description = "Logout sebagai {$role} - {$user->username}";
        
        // Gunakan helper untuk log activity
        log_activity_auto('logout', $description, [
            'module' => 'auth'
        ]);

        // Debug info
        log_message('info', "Logout - User ID: {$user->id}, Username: {$user->username}, Role: {$role}");
    }

    // ===== REGISTER SUCCESS METHOD =====
    private function logRegisterSuccess($userId, $vendorName, $email, $vendorProfileId)
    {
        try {
            // Karena user belum login, kita perlu manual insert ke activity logs
            $logs = new \App\Models\ActivityLogsModel();
            
            $data = [
                'user_id'     => $userId,
                'vendor_id'   => $vendorProfileId,
                'module'      => 'auth',
                'action'      => 'register',
                'description' => "Vendor '{$vendorName}' berhasil mendaftar - {$email}",
                'ip_address'  => service('request')->getIPAddress(),
                'user_agent'  => service('request')->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            
            $logs->insert($data);
            
            log_message('info', "Register success - User ID: {$userId}, Vendor: {$vendorName}, Email: {$email}");
            
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log register activity: ' . $e->getMessage());
        }
    }

    // ===== HELPER METHODS =====
    private function getUserRole($user)
    {
        if ($user->inGroup('admin')) return 'admin';
        if ($user->inGroup('seoteam')) return 'seo';
        if ($user->inGroup('vendor')) return 'vendor';
        return 'user';
    }
}