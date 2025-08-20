<?php

namespace App\Controllers\Vendoruser;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $auth = service('auth');

        // wajib login + wajib group vendor
        if (! $auth->loggedIn() || ! $auth->user()->inGroup('vendor')) {
            return redirect()->to('/login');
        }

        return view('Vendoruser/Dashboard'); // view kamu
    }
}
