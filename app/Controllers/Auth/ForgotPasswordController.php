<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class ForgotPasswordController extends Controller
{
    public function showForgotForm()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $email = $this->request->getPost('email');
        $users = new UserModel();
        $user  = $users->where('email', $email)->first();

        if (! $user) {
            return redirect()->back()->with('error', 'Email tidak terdaftar');
        }

        // Generate token
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Simpan ke DB
        $users->setResetToken($user['id'], $token, $expires);

        // Kirim email (contoh pakai Mailer bawaan CI4)
        $resetLink = base_url("reset-password?token={$token}");
        $emailService = \Config\Services::email();
        $emailService->setTo($email);
        $emailService->setSubject('Reset Password');
        $emailService->setMessage("
            Klik link berikut untuk reset password kamu: 
            <a href='{$resetLink}'>{$resetLink}</a>
        ");
        $emailService->send();

        return redirect()->to('/login')->with('message', 'Cek email kamu untuk link reset password');
    }

    public function showResetForm()
    {
        $token = $this->request->getGet('token');
        return view('auth/reset_password', ['token' => $token]);
    }

    public function resetPassword()
    {
        $token    = $this->request->getPost('token');
        $password = $this->request->getPost('password');

        $users = new UserModel();
        $user  = $users->findByResetToken($token);

        if (! $user) {
            return redirect()->back()->with('error', 'Token tidak valid atau sudah kedaluwarsa');
        }

        // Hash password baru
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Update password + hapus token
        $users->updatePassword($user['id'], $passwordHash);

        return redirect()->to('/login')->with('message', 'Password berhasil direset, silakan login');
    }
}
