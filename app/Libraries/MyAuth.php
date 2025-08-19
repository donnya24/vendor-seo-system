<?php
namespace App\Libraries;

use CodeIgniter\Shield\Auth;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Models\RememberModel;

class MyAuth
{
    protected $shield;

    public function __construct()
    {
        $this->shield = service('auth');
    }

    /**
     * Attempt login dengan remember functionality
     */
    public function attempt(array $credentials, bool $remember = false)
    {
        try {
            // Shield akan menangani remember token secara otomatis
            $result = $this->shield->attempt($credentials, $remember);
            
            if ($result->isOK() && $remember) {
                // Log remember token creation
                log_message('info', 'Remember token created for user: ' . $credentials['email']);
                
                // Opsional: Set additional cookie settings jika diperlukan
                $this->configureRememberCookie();
            }
            
            return $result;
            
        } catch (\Exception $e) {
            log_message('error', 'Authentication error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if user is logged in (including via remember token)
     */
    public function loggedIn()
    {
        return $this->shield->loggedIn();
    }

    /**
     * Get current authenticated user
     */
    public function user()
    {
        return $this->shield->user();
    }

    /**
     * Logout user and clean remember tokens
     */
    public function logout()
    {
        try {
            // Shield logout akan:
            // 1. Menghapus session
            // 2. Menghapus remember token dari database
            // 3. Menghapus cookie remember token
            $this->shield->logout();
            
            log_message('info', 'User logged out and remember tokens cleaned');
            
        } catch (\Exception $e) {
            log_message('error', 'Logout error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get remember token info for debugging
     */
    public function getRememberTokenInfo()
    {
        if (!$this->loggedIn()) {
            return null;
        }

        $user = $this->user();
        $rememberModel = new RememberModel();
        
        return $rememberModel->where('user_id', $user->id)
                            ->where('expires >', date('Y-m-d H:i:s'))
                            ->findAll();
    }

    /**
     * Manually clean expired remember tokens
     */
    public function cleanExpiredTokens()
    {
        $rememberModel = new RememberModel();
        $deleted = $rememberModel->where('expires <', date('Y-m-d H:i:s'))->delete();
        
        log_message('info', "Cleaned {$deleted} expired remember tokens");
        
        return $deleted;
    }

    /**
     * Configure remember cookie settings
     */
    private function configureRememberCookie()
    {
        // Opsional: Konfigurasi tambahan untuk cookie remember
        // Shield sudah menangani ini, tapi bisa dikustomisasi jika perlu
        
        $config = config('AuthSession');
        
        // Pastikan remember cookie settings sesuai kebutuhan
        // Ini biasanya sudah dikonfigurasi di Config/AuthSession.php
        
        log_message('debug', 'Remember cookie configured with expiry: ' . $config->rememberLength);
    }
}