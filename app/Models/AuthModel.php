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
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    protected $auth;
    protected $googleProvider;

    public function __construct()
    {
        parent::__construct();
        $this->auth = service('auth');
        
        // Setup Google Provider dengan League OAuth2
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
                // Step 1: Redirect ke Google dengan state yang menyimpan action
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
                $user = $this->findOrCreateUser($userData);
            } else {
                // LOGIN: Hanya cari user yang sudah ada
                $user = $this->findUser($userData);
            }

            if (!$user) {
                // User tidak ditemukan (untuk login)
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
     * Cari user (UNTUK LOGIN - TIDAK BUAT OTOMATIS)
     */
    private function findUser($userData)
    {
        // Cari by Google ID
        $user = $this->where('google_id', $userData['id'])->first();
        if ($user) {
            return $this->convertToUserObject($user);
        }

        // Cari by email
        $user = $this->getUserByEmail($userData['email']);
        if ($user) {
            // Update Google ID untuk user yang sudah ada
            $this->update($user->id, [
                'google_id' => $userData['id'],
                'google_profile' => json_encode($userData),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $user;
        }

        // User tidak ditemukan, return null
        return null;
    }

    /**
     * Cari atau buat user (UNTUK REGISTER - BUAT OTOMATIS JIKA TIDAK ADA)
     */
    private function findOrCreateUser($userData)
    {
        // Cari by Google ID
        $user = $this->where('google_id', $userData['id'])->first();
        if ($user) {
            return $this->convertToUserObject($user);
        }

        // Cari by email
        $user = $this->getUserByEmail($userData['email']);
        if ($user) {
            // Update Google ID untuk user yang sudah ada
            $this->update($user->id, [
                'google_id' => $userData['id'],
                'google_profile' => json_encode($userData),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            return $user;
        }

        // Buat user baru (UNTUK REGISTER)
        return $this->createNewUser($userData);
    }

    /**
     * Convert array to User object
     */
    private function convertToUserObject($userData)
    {
        if (is_object($userData)) {
            return $userData;
        }
        
        if (is_array($userData) && isset($userData['id'])) {
            $userModel = new \App\Models\UserModel();
            return $userModel->find($userData['id']);
        }
        
        throw new \Exception('Cannot convert user data to User object');
    }

    /**
     * Get user by email
     */
    private function getUserByEmail($email)
    {
        $identityModel = new \App\Models\IdentityModel();
        $identity = $identityModel->where('secret', $email)->first();
        
        if ($identity) {
            $userModel = new \App\Models\UserModel();
            return $userModel->find($identity['user_id']);
        }
        
        return null;
    }

    /**
     * Create new user
     */
    private function createNewUser($userData)
    {
        $db = db_connect();
        $users = $this->auth->getProvider();

        try {
            $db->transStart();

            // Buat user entity
            $username = $this->generateUsername($userData['email']);
            
            $userEntity = new User([
                'username' => $username,
                'status' => 'active',
                'active' => 1,
                'google_id' => $userData['id'],
                'google_profile' => json_encode($userData)
            ]);

            $userId = $users->insert($userEntity, true);

            // Buat email identity (TANPA PASSWORD untuk user Google)
            $identityModel = new \App\Models\IdentityModel();
            $identityModel->insert([
                'user_id' => $userId,
                'type' => 'email_password',
                'secret' => $userData['email'],
                'secret2' => null, // Password null untuk user Google
                'name' => $userData['name'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Assign ke group vendor
            $this->assignUserToGroup($userId, 'vendor');

            // Buat vendor profile
            $vendorProfileModel = new \App\Models\VendorProfilesModel();
            $vendorProfileModel->insert([
                'user_id' => $userId,
                'business_name' => $userData['name'] . ' Business',
                'owner_name' => $userData['name'],
                'phone' => '-',
                'whatsapp_number' => '-',
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $db->transComplete();

            return $users->findById($userId);

        } catch (\Exception $e) {
            $db->transRollback();
            throw $e;
        }
    }

    /**
     * Assign user to group
     */
    private function assignUserToGroup($userId, $group)
    {
        try {
            // Coba service('authorization')
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
        
        while ($this->where('username', $username)->first()) {
            $username = $originalUsername . $counter;
            $counter++;
        }
        
        return $username;
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
     * Check if user has password (untuk validasi ubah password)
     */
    public function userHasPassword($userId)
    {
        $identityModel = new \App\Models\IdentityModel();
        $identity = $identityModel
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->first();
            
        return $identity && !empty($identity['secret2']);
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
     * Verify current password untuk user yang punya password
     */
    public function verifyCurrentPassword($userId, $currentPassword)
    {
        $identityModel = new \App\Models\IdentityModel();
        $identity = $identityModel
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->first();

        if (!$identity || empty($identity['secret2'])) {
            return false; // User tidak punya password
        }

        return password_verify($currentPassword, $identity['secret2']);
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
        return $this->update($userId, [
            'last_active' => date('Y-m-d H:i:s')
        ]);
    }
}