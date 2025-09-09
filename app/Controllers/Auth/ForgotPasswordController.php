<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\ActivityLogsModel; // Tambahkan ini

class ForgotPasswordController extends BaseController
{
    protected $userModel;
    protected $db;
    protected $activityLogsModel; // Tambahkan property untuk model

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->db = db_connect();
        $this->activityLogsModel = new ActivityLogsModel(); // Inisialisasi model
    }

    // 🔹 Tampilkan form "Forgot Password"
    public function showForgotForm()
    {
        return view('auth/ForgotPassword'); 
    }

    // 🔹 Proses kirim link reset
    public function sendResetLink()
    {
        $email = $this->request->getPost('email');

        $identity = $this->db->table('auth_identities')
            ->where('secret', $email)
            ->where('type', 'email_password')
            ->get()
            ->getRow();

        if (!$identity) {
            // Log activity untuk permintaan reset password dengan email tidak ditemukan
            $this->logActivity(null, null, 'forgot_password', 'failed', 'Permintaan reset password - email tidak ditemukan', [
                'email' => $email,
                'ip' => $this->request->getIPAddress()
            ]);
            
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        $user = $this->userModel->find($identity->user_id);
        if (!$user) {
            // Log activity untuk user tidak ditemukan
            $this->logActivity(null, null, 'forgot_password', 'failed', 'Permintaan reset password - user tidak ditemukan', [
                'email' => $email,
                'identity_user_id' => $identity->user_id
            ]);
            
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        // 🔹 Buat token di tabel auth_password_resets
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Hapus token lama jika ada
        $this->db->table('auth_password_resets')->where('user_id', $user->id)->delete();

        $this->db->table('auth_password_resets')->insert([
            'user_id'   => $user->id,
            'token'     => $token,
            'expires'   => $expires,
            'created_at'=> date('Y-m-d H:i:s'),
        ]);

        // 🔹 Kirim email reset
        $resetLink = site_url("reset-password?token={$token}");
        $emailService = \Config\Services::email();
        $emailService->setFrom('noreply@domain.com', 'System');
        $emailService->setTo($email);
        $emailService->setSubject('Reset Password');
        $emailService->setMessage(
            "Klik link berikut untuk reset password: <a href='{$resetLink}'>Reset Password</a>"
        );
        $emailService->setMailType('html');
        
        $emailSent = $emailService->send();

        if ($emailSent) {
            // Log activity untuk permintaan reset password berhasil
            $this->logActivity($user->id, null, 'forgot_password', 'success', 'Permintaan reset password berhasil dikirim', [
                'email' => $email,
                'token_created' => date('Y-m-d H:i:s'),
                'token_expires' => $expires
            ]);
            
            return redirect()->back()->with('success', 'Link reset password telah dikirim ke email Anda.');
        } else {
            // Log activity untuk gagal mengirim email reset
            $this->logActivity($user->id, null, 'forgot_password', 'error', 'Gagal mengirim email reset password', [
                'email' => $email,
                'error' => $emailService->printDebugger(['headers'])
            ]);
            
            return redirect()->back()->with('error', 'Gagal mengirim email. Silakan coba lagi.');
        }
    }

    // 🔹 Tampilkan form reset password
    public function showResetForm()
    {
        $token = $this->request->getGet('token');
        
        // Log activity untuk mengakses form reset password
        $this->logActivity(null, null, 'reset_password', 'view', 'Mengakses form reset password', [
            'token' => $token
        ]);
        
        return view('auth/ResetPassword', ['token' => $token]);
    }

    // 🔹 Proses reset password
    public function resetPassword()
    {
        $token    = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('password_confirm');

        if ($password !== $confirm) {
            // Log activity untuk password tidak sama
            $this->logActivity(null, null, 'reset_password', 'failed', 'Reset password gagal - password tidak sama', [
                'token' => $token
            ]);
            
            return redirect()->back()->with('error', 'Password tidak sama.');
        }

        // 🔹 Cari token di auth_password_resets
        $tokenRecord = $this->db->table('auth_password_resets')
            ->where('token', $token)
            ->where('expires >=', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$tokenRecord) {
            // Log activity untuk token tidak valid atau kadaluarsa
            $this->logActivity(null, null, 'reset_password', 'failed', 'Reset password gagal - token tidak valid atau kadaluarsa', [
                'token' => $token
            ]);
            
            return redirect()->to('/forgot-password')->with('error', 'Token tidak valid atau kadaluarsa.');
        }

        $user = $this->userModel->find($tokenRecord->user_id);

        // 🔹 Update password di secret2
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->db->table('auth_identities')
            ->where('user_id', $user->id)
            ->where('type', 'email_password')
            ->update([
                'secret2' => $hash,
                'force_reset' => 0,
            ]);

        // 🔹 Hapus token yang sudah digunakan
        $this->db->table('auth_password_resets')
            ->where('id', $tokenRecord->id)
            ->delete();

        // Log activity untuk reset password berhasil
        $this->logActivity($user->id, null, 'reset_password', 'success', 'Reset password berhasil', [
            'token_used' => $token,
            'password_updated_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
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
            log_message('error', 'Failed to log activity in ForgotPasswordController: ' . $e->getMessage());
        }
    }
}