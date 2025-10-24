<?php
namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Shield\Entities\User;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class AuthModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'username', 'status', 'status_message', 'active', 
        'last_active', 'google_id', 'google_profile', 
        'created_at', 'updated_at', 'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $auth;
    protected $googleProvider;
    protected $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->auth = service('auth');
        $this->userModel = new \App\Models\UserModel();
        
        // Setup Google Provider
        try {
            $this->googleProvider = new Google([
                'clientId'     => env('GOOGLE_CLIENT_ID'),
                'clientSecret' => env('GOOGLE_CLIENT_SECRET'),
                'redirectUri'  => site_url('auth/google/callback'),
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Google Provider initialization failed: ' . $e->getMessage());
        }
    }

    /**
     * Handle Google OAuth Login/Register
     */
    public function handleGoogleAuth($action = 'login')
    {
        if (!$this->googleProvider) {
            return ['success' => false, 'error' => 'Google OAuth tidak dikonfigurasi.'];
        }

        try {
            $code = service('request')->getGet('code');
            
            if (!$code) {
                // Step 1: Redirect ke Google
                $state = $action . '_' . bin2hex(random_bytes(16));
                $authUrl = $this->googleProvider->getAuthorizationUrl([
                    'scope' => ['email', 'profile'],
                    'state' => $state,
                    'prompt' => 'select_account'
                ]);
                
                session()->set('oauth2state', $state);
                return ['type' => 'redirect', 'url' => $authUrl];
            }

            // Step 2: Verify state
            $state = service('request')->getGet('state');
            $savedState = session()->get('oauth2state');
            
            if (empty($state) || ($state !== $savedState)) {
                session()->remove('oauth2state');
                throw new \Exception('Invalid OAuth state');
            }

            // Step 3: Get token dan user info
            $token = $this->googleProvider->getAccessToken('authorization_code', ['code' => $code]);
            $googleUser = $this->googleProvider->getResourceOwner($token);

            // Step 4: Extract data
            $userData = $this->extractGoogleData($googleUser);
            
            if (empty($userData['id']) || empty($userData['email'])) {
                throw new \Exception('Invalid Google user data: missing ID or email');
            }

            // Step 5: Handle berdasarkan action
            if (strpos($state, 'register') !== false) {
                // REGISTER: Cari atau buat user baru
                $user = $this->findOrCreateUserForRegistration($userData);
            } else {
                // LOGIN: Hanya cari user yang sudah ada
                $user = $this->findUserForLogin($userData);
            }

            if (!$user) {
                session()->remove('oauth2state');
                return [
                    'success' => false,
                    'user_not_found' => true,
                    'google_data' => $userData,
                    'error' => 'Akun tidak terdaftar. Silakan daftar terlebih dahulu.'
                ];
            }

            // Step 6: Login user
            $this->auth->login($user);
            session()->remove('oauth2state');
            
            return ['success' => true, 'user' => $user];

        } catch (\Exception $e) {
            log_message('error', 'Google OAuth Error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Google authentication gagal: ' . $e->getMessage()];
        }
    }

    /**
     * Cari atau buat user khusus untuk REGISTRASI
     */
    private function findOrCreateUserForRegistration($userData)
    {
        // 1. Cari by Google ID menggunakan UserModel
        $user = $this->userModel->findByGoogleId($userData['id']);
        if ($user) {
            return $user;
        }

        // 2. Cari by email menggunakan UserModel
        $existingUser = $this->getUserByEmail($userData['email']);
        if ($existingUser) {
            // Hanya update jika user aktif, belum punya google_id, dan tidak soft deleted
            if ($existingUser->active && empty($existingUser->google_id) && empty($existingUser->deleted_at)) {
                // Update menggunakan UserModel
                $this->userModel->update($existingUser->id, [
                    'google_id' => $userData['id'],
                    'google_profile' => json_encode($userData),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update identity
                $this->updateIdentityForGoogleUser($existingUser->id, $userData['email']);
                
                // Get updated user
                return $this->userModel->find($existingUser->id);
            }
        }

        // 3. Buat user baru
        return $this->createNewUser($userData);
    }

    /**
     * Cari user untuk LOGIN
     */
    private function findUserForLogin($userData)
    {
        // Cari by Google ID
        $user = $this->userModel->findByGoogleId($userData['id']);
        if ($user) {
            return $user;
        }

        // Cari by email - hanya yang aktif dan punya Google ID
        $user = $this->getUserByEmail($userData['email']);
        if ($user && $user->active && !empty($user->google_id)) {
            return $user;
        }

        return null;
    }

    /**
     * Extract Google data
     */
    private function extractGoogleData($googleUser)
    {
        if (is_array($googleUser)) {
            return [
                'id' => $googleUser['sub'] ?? $googleUser['id'] ?? null,
                'email' => $googleUser['email'] ?? null,
                'name' => $googleUser['name'] ?? null,
                'picture' => $googleUser['picture'] ?? null,
                'first_name' => $googleUser['given_name'] ?? null,
                'last_name' => $googleUser['family_name'] ?? null
            ];
        }

        if (is_object($googleUser) && method_exists($googleUser, 'toArray')) {
            $data = $googleUser->toArray();
            return [
                'id' => $data['sub'] ?? $data['id'] ?? null,
                'email' => $data['email'] ?? null,
                'name' => $data['name'] ?? null,
                'picture' => $data['picture'] ?? null,
                'first_name' => $data['given_name'] ?? null,
                'last_name' => $data['family_name'] ?? null
            ];
        }

        throw new \Exception('Cannot extract Google user data');
    }

    /**
     * Update identity untuk user Google
     */
    private function updateIdentityForGoogleUser($userId, $email)
    {
        try {
            $identityModel = new \App\Models\IdentityModel();
            $identity = $identityModel
                ->where('user_id', $userId)
                ->where('type', 'email_password')
                ->first();
                
            if ($identity) {
                $identityModel->update($identity['id'], [
                    'secret' => $email,
                    'secret2' => null,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to update identity for Google user: ' . $e->getMessage());
        }
    }

    /**
     * Get user by email menggunakan UserModel
     */
    private function getUserByEmail($email)
    {
        $identityModel = new \App\Models\IdentityModel();
        $identity = $identityModel->where('secret', $email)->first();
        
        if ($identity) {
            return $this->userModel->find($identity['user_id']);
        }
        
        return null;
    }

    /**
     * Create new user untuk Google OAuth
     */
    private function createNewUser($userData)
    {
        $db = db_connect();

        try {
            $db->transStart();

            // Buat username dari email
            $username = $this->generateUsername($userData['email']);

            // Data user lengkap - GUNAKAN USERMODEL
            $userDataToSave = [
                'username' => $username,
                'google_id' => $userData['id'],
                'google_profile' => json_encode($userData),
                'status' => 'active',
                'active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $userId = $this->userModel->insert($userDataToSave);

            if (!$userId) {
                throw new \Exception('Failed to insert user: ' . implode(', ', $this->userModel->errors()));
            }

            // Buat identity
            $identityModel = new \App\Models\IdentityModel();
            $identityResult = $identityModel->insert([
                'user_id' => $userId,
                'type' => 'email_password',
                'secret' => $userData['email'],
                'secret2' => null,
                'name' => $userData['name'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$identityResult) {
                throw new \Exception('Failed to create identity: ' . implode(', ', $identityModel->errors()));
            }

            // Assign ke group vendor
            $this->assignUserToGroup($userId, 'vendor');

            // Buat vendor profile
            $vendorProfileModel = new \App\Models\VendorProfilesModel();
            $vendorResult = $vendorProfileModel->insert([
                'user_id' => $userId,
                'business_name' => $userData['name'] . ' Business',
                'owner_name' => $userData['name'],
                'phone' => '-',
                'whatsapp_number' => '-',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if (!$vendorResult) {
                throw new \Exception('Failed to create vendor profile: ' . implode(', ', $vendorProfileModel->errors()));
            }

            $db->transComplete();

            // Ambil user yang baru dibuat
            return $this->userModel->find($userId);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Failed to create Google user: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Assign user to group
     */
    private function assignUserToGroup($userId, $group)
    {
        try {
            $authorization = service('authorization');
            
            if ($authorization && method_exists($authorization, 'addUserToGroup')) {
                $authorization->addUserToGroup($userId, $group);
                return true;
            }
            
            // Fallback: manual insert
            $db = db_connect();
            $groupData = [
                'user_id' => $userId,
                'group' => $group,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            return $db->table('auth_groups_users')->insert($groupData);
            
        } catch (\Exception $e) {
            throw new \Exception('Failed to assign user to group: ' . $e->getMessage());
        }
    }

    /**
     * Generate username dari email
     */
    private function generateUsername($email)
    {
        $username = strstr($email, '@', true);
        $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
        
        $counter = 1;
        $originalUsername = $username;
        
        while ($this->userModel->where('username', $username)->first()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
    }

    /**
     * Check if user has password (untuk validasi ubah password)
     */
    public function userHasPassword($userId)
    {
        try {
            $identityModel = new \App\Models\IdentityModel();
            $identity = $identityModel
                ->where('user_id', $userId)
                ->where('type', 'email_password')
                ->first();
            
            if (!$identity) {
                return false;
            }
            
            // USER TIDAK PUNYA PASSWORD JIKA:
            // - secret2 adalah NULL, ATAU
            // - secret2 adalah empty string ''
            $hasPassword = !is_null($identity['secret2']) && $identity['secret2'] !== '';
            
            return $hasPassword;
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking user password status: ' . $e->getMessage());
            return true; // Default safe
        }
    }

    /**
     * Update password untuk user
     */
    public function updateUserPassword($userId, $newPassword)
    {
        $db = db_connect();
        
        try {
            $db->transStart();

            $identityModel = new \App\Models\IdentityModel();
            $identity = $identityModel
                ->where('user_id', $userId)
                ->where('type', 'email_password')
                ->first();

            if (!$identity) {
                throw new \Exception('Identity tidak ditemukan');
            }

            // Update password di identity
            $identityModel->update($identity['id'], [
                'secret2' => password_hash($newPassword, PASSWORD_DEFAULT),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();
            return true;

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Check if user registered via Google OAuth and has no password
     */
    public function isGoogleUserWithoutPassword($userId)
    {
        try {
            $identityModel = new \App\Models\IdentityModel();
            $identity = $identityModel
                ->where('user_id', $userId)
                ->where('type', 'email_password')
                ->first();
            
            if (!$identity) {
                return false;
            }
            
            // User Google tanpa password: secret2 harus NULL atau empty string
            return empty($identity['secret2']) || is_null($identity['secret2']);
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking Google user status: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user has Google ID 
     */
    public function isGoogleUser($userId)
    {
        try {
            $user = $this->userModel->find($userId);
            
            if (!$user) {
                return false;
            }
            
            return !empty($user->google_id);
        } catch (\Exception $e) {
            log_message('error', 'Error checking Google user: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Attempt login dengan email/password
     */
    public function attemptLogin(string $email, string $password, bool $remember = false)
    {
        $result = $this->auth->attempt(['email' => $email, 'password' => $password], $remember);

        if ($result->isOK()) {
            return ['success' => true, 'user' => $this->auth->user()];
        }

        $reason = $result->reason();
        $error = match($reason) {
            'invalid_password' => 'Password salah.',
            'user_not_found'   => 'Akun tidak tersedia.',
            'user_not_active'  => 'Akun belum aktif.',
            default            => 'Login gagal.',
        };

        return ['success' => false, 'error' => $error];
    }

    /**
     * Check if Google OAuth is available
     */
    public function isGoogleOAuthAvailable()
    {
        return class_exists('League\OAuth2\Client\Provider\Google') && 
               !empty(env('GOOGLE_CLIENT_ID')) && 
               !empty(env('GOOGLE_CLIENT_SECRET'));
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset(string $email)
    {
        $user = $this->auth->getUserByEmail(strtolower(trim($email)));
        if (!$user || !$user->active) {
            return ['success' => false, 'error' => 'Email tidak ditemukan atau akun belum aktif.'];
        }

        $token = $this->auth->createPasswordResetToken($user);

        return ['success' => true, 'user' => $user, 'token' => $token];
    }

    /**
     * Reset password by token
     */
    public function resetPassword(string $token, string $password)
    {
        $user = $this->auth->getUserByPasswordResetToken($token);
        if (!$user) return false;

        $this->auth->resetPassword($user, $password);
        return true;
    }

    /**
     * Logout
     */
    public function logout()
    {
        $this->auth->logout();
        session()->destroy();
    }

    /**
     * Update last_active timestamp
     */
    public function updateLastActive($userId)
    {
        return $this->userModel->update($userId, [
            'last_active' => date('Y-m-d H:i:s')
        ]);
    }
}