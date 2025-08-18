<?php
namespace App\Models;

use CodeIgniter\Model;

class AuthModel
{
    protected $auth;

    public function __construct()
    {
        $this->auth = service('auth'); // Shield auth service
    }

    /**
     * Attempt login
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
     * Request password reset
     */
    public function requestPasswordReset(string $email)
    {
        $user = $this->auth->getUserByEmail(strtolower(trim($email)));
        if (!$user || !$user->active) return ['success' => false, 'error' => 'Email tidak ditemukan atau akun belum aktif.'];

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
}
