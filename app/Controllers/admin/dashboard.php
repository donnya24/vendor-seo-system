<?php

namespace App\Controllers\Admin;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $auth = service('auth');
        $user = $auth->user();

        // Cek apakah login & punya role admin
        if (! $user || ! $user->inGroup('admin')) {
            return redirect()->to('/login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Kirim data user ke view
        return view('Admin/DashboardAdmin', [
            'title' => 'Dashboard Admin',
            'user'  => $user,
        ]);
    }
}
