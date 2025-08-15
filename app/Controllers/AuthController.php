<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Shield\Models\UserModel;
use CodeIgniter\Shield\Authentication\Authentication;

class AuthController extends BaseController
{
    public function login()
    {
        return view('auth/login');
    }

    public function attemptLogin()
    {
        $auth = service('auth');

        $credentials = [
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ];

        if ($auth->attempt($credentials)) {
            return $this->response->setJSON(['status' => 'success', 'message' => 'Login berhasil']);
        }

        return $this->response->setJSON(['status' => 'error', 'message' => 'Email atau password salah']);
    }

    public function logout()
    {
        service('auth')->logout();
        return redirect()->to('/login');
    }
}
