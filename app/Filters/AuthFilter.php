<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Mengecek apakah pengguna sudah login menggunakan service
        $auth = Services::auth();  // Mengakses service 'auth' yang benar
        if (! $auth->loggedIn()) {
            // Jika belum login, redirect ke halaman login
            return redirect()->to('/login');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi tambahan setelah permintaan (opsional)
    }
}
