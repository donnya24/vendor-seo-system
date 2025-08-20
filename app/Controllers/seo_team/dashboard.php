<?php

namespace App\Controllers\Seo_Team;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $auth = service('auth');
        $user = $auth->user();

        // Cek login & role seoteam
        if (! $user || ! $user->inGroup('seoteam')) {
            return redirect()->to('/login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Render view: app/Views/Seo_Team/Dashboard.php
        return view('Seo_Team/Dashboard', [
            'title' => 'Dashboard Tim SEO',
            'user'  => $user,
        ]);
    }
}
