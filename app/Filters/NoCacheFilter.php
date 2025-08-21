<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class NoCacheFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Penting: JANGAN return Response dari sini,
        // cukup biarkan kosong supaya controller tetap dieksekusi.
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Set header setelah controller dijalankan agar pasti menempel di response final
        $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0');
        $response->setHeader('Pragma', 'no-cache');
        $response->setHeader('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
        $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');

        // Tidak perlu return apa pun di after()
    }
}
