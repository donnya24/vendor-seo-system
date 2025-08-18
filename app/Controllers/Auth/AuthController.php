<?php
namespace App\Controllers\Auth;

use CodeIgniter\Controller;
use App\Models\AuthModel;

class AuthController extends Controller
{
    protected $authModel;

    public function __construct()
    {
        $this->authModel = new AuthModel();
    }

    public function login()
    {
        if (service('auth')->loggedIn()) {
            return $this->redirectByRole(service('auth')->user());
        }
        return view('Auth/login');
    }

    public function attemptLogin()
    {
        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');
        $remember = $this->request->getPost('remember') ? true : false;

        $result = $this->authModel->attemptLogin($email, $password, $remember);

        if (!$result['success']) {
            return redirect()->back()->withInput()->with('error', $result['error']);
        }

        return $this->redirectByRole($result['user']);
    }

    public function logout()
    {
        if (! $this->request->is('post')) return redirect()->to('/login');

        $this->authModel->logout();
        return redirect()->to('/login')->with('success', 'Anda telah keluar.');
    }

    public function forgotPassword()
    {
        return view('Auth/forgot_password');
    }

    public function attemptForgotPassword()
    {
        $email = $this->request->getPost('email');
        $result = $this->authModel->requestPasswordReset($email);

        if (!$result['success']) {
            return redirect()->back()->with('error', $result['error']);
        }

        // Kirim email
        $resetLink = site_url("reset-password/{$result['token']}");
        $emailService = \Config\Services::email();
        $emailService->setFrom('noreply@domain.com', 'Vendor SEO System');
        $emailService->setTo($result['user']->email);
        $emailService->setSubject('Reset Password Anda');
        $emailService->setMessage("
            Halo {$result['user']->email},<br><br>
            Anda meminta reset password. Silakan klik link di bawah untuk membuat password baru:<br>
            <a href='{$resetLink}' target='_blank'>Reset Password</a><br><br>
            Jika bukan Anda yang meminta, abaikan email ini.
        ");
        $emailService->setMailType('html');

        if ($emailService->send()) {
            return redirect()->back()->with('success', 'Link reset password telah dikirim ke email Anda.');
        } else {
            log_message('error', 'Gagal mengirim email: ' . $emailService->printDebugger(['headers']));
            return redirect()->back()->with('error', 'Gagal mengirim email. Cek konfigurasi email.');
        }
    }

    public function resetPassword($token)
    {
        return view('Auth/reset_password', ['token' => $token]);
    }

    public function attemptResetPassword($token)
    {
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('error', 'Password dan konfirmasi tidak sama.');
        }

        $success = $this->authModel->resetPassword($token, $password);
        if (!$success) {
            return redirect()->back()->with('error', 'Token reset tidak valid atau sudah kadaluarsa.');
        }

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }

    private function redirectByRole($user)
    {
        if ($user->inGroup('admin')) return redirect()->to('/admin/dashboard');
        if ($user->inGroup('seo_team')) return redirect()->to('/seo_team/dashboard');
        if ($user->inGroup('vendor')) return redirect()->to('/vendor/dashboard');
        return redirect()->to('/dashboard');
    }
}
