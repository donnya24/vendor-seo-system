<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Config\AdminHeaderTrait; // Tambahkan use trait

class BaseAdminController extends BaseController
{
    use AdminHeaderTrait; // Gunakan trait
    
    // PERBAIKAN: Tambahkan : void pada akhir deklarasi metode
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger): void
    {
        parent::initController($request, $response, $logger);
    }
    
    // Hapus method loadCommonData() dari sini karena sudah ada di trait
}