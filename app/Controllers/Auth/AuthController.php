<?php

namespace App\Controllers\Vendor;

use App\Controllers\BaseController;

class AuthController extends BaseController
{
    public function login()
    {
        return view('Auth/Login');   // kamu punya app/Views/Auth/Login.php
    }

    public function attemptLogin()
    {
        // TODO: validasi kredensial, set session user & role, lalu redirect:
        // misal: return redirect()->to('/vendor/dashboard');
    }

    public function register()
    {
        return view('Auth/Register'); // kamu punya app/Views/Auth/Register.php
    }

    public function attemptRegister()
    {
        // TODO: simpan user lalu redirect ke login
        // return redirect()->to('/login')->with('message','Registrasi berhasil');
    }

    public function logout()
    {
        // TODO: destroy session
        // return redirect()->to('/');
    }
}
