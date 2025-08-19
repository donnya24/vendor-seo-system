<?php

namespace App\Controllers\Auth;

use App\Controllers\BaseController;
use App\Models\UserModel;

class ForgotPasswordController extends BaseController
{
    protected $userModel;
    protected $db;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->db = db_connect();
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
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        $user = $this->userModel->find($identity->user_id);
        if (!$user) {
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
        $emailService->send();

        return redirect()->back()->with('success', 'Link reset password telah dikirim ke email Anda.');
    }

    // 🔹 Tampilkan form reset password
    public function showResetForm()
    {
        $token = $this->request->getGet('token');
        return view('auth/ResetPassword', ['token' => $token]);
    }

    // 🔹 Proses reset password
    public function resetPassword()
    {
        $token    = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirm  = $this->request->getPost('password_confirm');

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Password tidak sama.');
        }

        // 🔹 Cari token di auth_password_resets
        $tokenRecord = $this->db->table('auth_password_resets')
            ->where('token', $token)
            ->where('expires >=', date('Y-m-d H:i:s'))
            ->get()
            ->getRow();

        if (!$tokenRecord) {
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

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login dengan password baru Anda.');
    }
}
