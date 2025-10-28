<?php
namespace App\Controllers\Auth;

use CodeIgniter\Controller;
use CodeIgniter\Shield\Entities\User;
use App\Models\VendorProfilesModel;
use App\Models\SeoProfilesModel;

class AuthController extends Controller
{
    protected $auth;
    protected $vendorProfilesModel;
    protected $seoProfilesModel;
    protected $authModel;

    public function __construct()
    {
        $this->auth = service('auth');
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->seoProfilesModel = new SeoProfilesModel();
        $this->authModel = new \App\Models\AuthModel();
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
            log_activity_auto('login_failed', 'Validasi login gagal - ' . json_encode($validation->getErrors()), [
                'module' => 'auth',
                'email' => $this->request->getPost('email')
            ]);
            
            return redirect()->back()->withInput()->with('error', 'Email dan password harus diisi dengan benar.');
        }

        $remember = (bool) $this->request->getPost('remember');
        $email = (string) $this->request->getPost('email');
        $password = (string) $this->request->getPost('password');

        // Gunakan custom authentication
        $result = $this->authModel->attemptLogin($email, $password, $remember);

        if ($result['success']) {
            $user = $result['user'];
            
            // ✅ CEK STATUS USER: Hanya inactive yang tidak bisa login
            $statusCheck = $this->checkUserStatus($user);
            if (!$statusCheck['success']) {
                $this->auth->logout();
                
                log_activity_auto('login_blocked', 'Login ditolak - status ' . $statusCheck['role'] . ' ' . $statusCheck['reason'] . ': ' . $email, [
                    'module' => 'auth',
                    'reason' => $statusCheck['reason']
                ]);
                
                session()->setFlashdata('error', $statusCheck['message']);
                if ($statusCheck['reason'] === 'vendor_inactive' || $statusCheck['reason'] === 'seo_inactive') {
                    session()->setFlashdata('show_contact', true);
                }
                
                return redirect()->back()->withInput();
            }
            
            if ($remember === true) {
                helper('auth_remember');
                try {
                    force_remember_token((int) $user->id);
                } catch (\Throwable $e) {
                    log_message('error', 'force_remember_token failed: ' . $e->getMessage());
                }
            }
            
            $this->logLoginSuccess($user);
            
            return $this->redirectByRole($user);
        }

        // Handle Google OAuth user secara aman dengan subtle hint
        $hint = '';
        if (isset($result['is_google_user']) && $result['is_google_user']) {
            $hint = 'google_hint';
            log_activity_auto('login_failed', 'Google OAuth user attempted password login: ' . $email, [
                'module' => 'auth',
                'reason' => 'google_oauth_user'
            ]);
        } else {
            log_activity_auto('login_failed', 'Login gagal - kredensial salah untuk email: ' . $email, [
                'module' => 'auth'
            ]);
        }
        
        return redirect()->back()
            ->withInput()
            ->with('error', $result['error'] ?? 'Login gagal. Periksa kembali email dan password Anda.')
            ->with('login_hint', $hint);
    }

    // ===== GOOGLE OAUTH LOGIN =====
    public function googleLogin()
    {
        $result = $this->authModel->handleGoogleAuth('login');
        
        if (isset($result['type']) && $result['type'] === 'redirect') {
            return redirect()->to($result['url']);
        }
        
        if ($result['success']) {
            // Get fresh user from auth service
            $user = $this->auth->user();
            
            if (!$user) {
                return redirect()->to('/login')->with('error', 'Failed to authenticate user.');
            }
            
            // ✅ CEK STATUS USER untuk Google login juga
            $statusCheck = $this->checkUserStatus($user);
            if (!$statusCheck['success']) {
                $this->auth->logout();
                
                session()->setFlashdata('error', $statusCheck['message']);
                if ($statusCheck['reason'] === 'vendor_inactive' || $statusCheck['reason'] === 'seo_inactive') {
                    session()->setFlashdata('show_contact', true);
                }
                
                return redirect()->to('/login');
            }
            
            $this->logLoginSuccess($user);
            return $this->redirectByRole($user);
        }
        
        // Jika user tidak terdaftar di login, beri pesan error
        if (isset($result['user_not_found']) && $result['user_not_found']) {
            session()->setFlashdata('google_user_data', $result['google_data']);
            session()->setFlashdata('error', 'Akun tidak terdaftar. Silakan daftar terlebih dahulu.');
            return redirect()->to('/login');
        }
        
        return redirect()->to('/login')->with('error', $result['error'] ?? 'Google login gagal.');
    }

    // ===== GOOGLE OAUTH REGISTER =====
    public function googleRegister()
    {
        $result = $this->authModel->handleGoogleAuth('register');
        
        if (isset($result['type']) && $result['type'] === 'redirect') {
            return redirect()->to($result['url']);
        }
        
        if ($result['success']) {
            // Get fresh user from auth service
            $user = $this->auth->user();
            
            if (!$user) {
                return redirect()->to('/register')->with('error', 'Failed to authenticate user.');
            }
            
            // ✅ CEK STATUS USER untuk Google register
            $statusCheck = $this->checkUserStatus($user);
            if (!$statusCheck['success']) {
                $this->auth->logout();
                
                session()->setFlashdata('error', $statusCheck['message']);
                if ($statusCheck['reason'] === 'vendor_inactive' || $statusCheck['reason'] === 'seo_inactive') {
                    session()->setFlashdata('show_contact', true);
                }
                
                return redirect()->to('/register');
            }
            
            $this->logLoginSuccess($user);
            return $this->redirectByRole($user);
        }
        
        return redirect()->to('/register')->with('error', $result['error'] ?? 'Google registration gagal.');
    }

    // ===== GOOGLE CALLBACK =====
    public function googleCallback()
    {
        // Cek dari state apakah ini untuk login atau register
        $state = service('request')->getGet('state');
        
        if (strpos($state, 'register') !== false) {
            return $this->googleRegister();
        } else {
            return $this->googleLogin();
        }
    }

    // ===== CEK STATUS USER =====
    private function checkUserStatus($user)
    {
        $response = [
            'success' => true,
            'message' => '',
            'role' => '',
            'reason' => ''
        ];

        if ($user->inGroup('vendor')) {
            $response['role'] = 'vendor';
            $vendorProfile = $this->vendorProfilesModel->getByUserId($user->id);
            
            if (!$vendorProfile) {
                $response['success'] = false;
                $response['message'] = 'Profil vendor tidak ditemukan. Silakan hubungi administrator.';
                $response['reason'] = 'vendor_profile_not_found';
            } elseif ($vendorProfile['status'] === 'inactive') {
                $response['success'] = false;
                $response['message'] = 'Akun vendor Anda dinonaktifkan. Silakan hubungi administrator.';
                $response['reason'] = 'vendor_inactive';
            }
            
        } elseif ($user->inGroup('seoteam')) {
            $response['role'] = 'seoteam';
            $seoProfile = $this->seoProfilesModel->getByUserId($user->id);
            
            if (!$seoProfile) {
                $response['success'] = false;
                $response['message'] = 'Profil SEO tidak ditemukan. Silakan hubungi administrator.';
                $response['reason'] = 'seo_profile_not_found';
            } elseif ($seoProfile['status'] === 'inactive') {
                $response['success'] = false;
                $response['message'] = 'Akun SEO Anda dinonaktifkan. Silakan hubungi administrator.';
                $response['reason'] = 'seo_inactive';
            }
        } elseif ($user->inGroup('admin')) {
            $response['role'] = 'admin';
        }

        return $response;
    }

 public function logout()
{
    $user = null;
    
    if ($this->auth->loggedIn()) {
        $user = $this->auth->user();
        $this->logLogout($user);
    }

    helper('auth_remember');
    try {
        forget_remember_token_from_cookie();
    } catch (\Throwable $e) {
        log_message('error', 'forget_remember_token_from_cookie failed: ' . $e->getMessage());
    }

    $this->auth->logout();
    session()->destroy();

    // Set flashdata untuk notifikasi di login page
    session()->setFlashdata('success', 'Password berhasil diperbarui! Silakan login dengan password baru Anda.');

    return redirect()->to('/login');
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
        // Cek apakah ada data Google dari proses login
        $googleData = session()->getFlashdata('google_user_data');
        $prefillData = [];
        
        if ($googleData) {
            $prefillData = [
                'vendor_name' => $googleData['name'] ?? '',
                'email' => $googleData['email'] ?? ''
            ];
        }
        
        return view('auth/register_vendor', ['prefillData' => $prefillData]);
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
            log_activity_auto('register_failed', 'Validasi pendaftaran vendor gagal - ' . json_encode($validation->getErrors()), [
                'module' => 'auth',
                'email' => $this->request->getPost('email')
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', implode('<br>', $validation->getErrors()));
        }

        $email      = trim((string) $this->request->getPost('email'));
        $password   = ((string) $this->request->getPost('password'));
        $vendorName = trim((string) $this->request->getPost('vendor_name'));

        $db    = db_connect();
        $users = $this->auth->getProvider();

        try {
            resolve_identity_table();
            if (identity_exists($email)) {
                log_activity_auto('register_failed', 'Email sudah terdaftar: ' . $email, [
                    'module' => 'auth'
                ]);
                
                return redirect()->back()->withInput()->with('error', 'Email sudah digunakan.');
            }

            $db->transException(true);
            $db->transStart();

            $username   = make_unique_username($vendorName, $email);
            
            $userEntity = new User([
                'username' => $username,
                'status'   => 'active',
                'active'   => 1
            ]);
            
            $userId = $users->insert($userEntity, true);

            create_email_password_identity((int) $userId, $email, $password);
            assign_user_to_group((int) $userId, 'vendor');

            $db->table('auth_identities')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->update(['name' => $vendorName]);

            $vendorData = [
                'user_id'        => $userId,
                'business_name'  => $vendorName,
                'owner_name'     => $vendorName,
                'phone'          => '-',
                'whatsapp_number'=> '-',
                'status'         => 'pending',
            ];

            $vendorProfileId = $this->vendorProfilesModel->insert($vendorData);

            $this->logRegisterSuccess($userId, $vendorName, $email, $vendorProfileId);

            $db->transComplete();

            return redirect()->to('/login')->with('message', 'Akun vendor berhasil dibuat. Silakan login.');
        } catch (\Throwable $e) {
            if ($db->transStatus() !== false) {
                $db->transRollback();
            }
            
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
        
        log_activity_auto('login', $description, [
            'module' => 'auth'
        ]);

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
        
        log_activity_auto('logout', $description, [
            'module' => 'auth'
        ]);

        log_message('info', "Logout - User ID: {$user->id}, Username: {$user->username}, Role: {$role}");
    }

    // ===== REGISTER SUCCESS METHOD =====
    private function logRegisterSuccess($userId, $vendorName, $email, $vendorProfileId)
    {
        try {
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