<?php

namespace App\Controllers\Vendoruser;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $auth = service('auth');

        // Guard tambahan (meski sudah difilter di routes)
        if (! $auth->loggedIn() || ! $auth->user()->inGroup('vendor')) {
            return redirect()->to('/login');
        }

        // Pakai path view yang sesuai nama file (dashboard.php)
        return view('vendoruser/dashboard');
    }
}
