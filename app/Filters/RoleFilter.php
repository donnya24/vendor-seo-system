<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Mengecek apakah pengguna sudah login dan memiliki peran yang sesuai
        $auth = Services::auth();  // Mengakses service 'auth'
        if (! $auth->loggedIn()) {
            // Jika belum login, redirect ke halaman login
            return redirect()->to('/login');
        }

        $user = $auth->user();
        // Mengecek apakah pengguna memiliki peran yang sesuai
        if (! in_array($arguments, $user->getRoles())) {
            // Jika tidak, redirect ke halaman akses ditolak
            return redirect()->to('/access-denied');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi tambahan setelah permintaan (opsional)
    }
}
