<?php
namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class VendorVerificationFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $auth = service('auth');
        
        // Jika user belum login, redirect ke login
        if (!$auth->isLoggedIn()) {
            return redirect()->to(site_url('login'));
        }
        
        // Jika user bukan vendor, redirect ke dashboard sesuai role
        if (!$auth->user()->inGroup('vendor')) {
            return redirect()->to(site_url($auth->user()->getGroups()[0] . '/dashboard'));
        }
        
        // Periksa status verifikasi vendor
        $vendorProfileModel = new \App\Models\VendorProfilesModel();
        $vp = $vendorProfileModel->where('user_id', $auth->user()->id)->first();
        
        // Jika vendor belum terverifikasi, batasi akses ke beberapa fitur
        if (!$vp || $vp['status'] !== 'verified') {
            $currentRoute = $request->getUri()->getPath();
            
            // Daftar route yang diblokir untuk vendor belum terverifikasi
            $blockedRoutes = [
                'vendoruser/products/create',
                'vendoruser/products/store',
                'vendoruser/products/edit',
                'vendoruser/products/update',
                'vendoruser/services/create',
                'vendoruser/services/store',
                'vendoruser/services/edit',
                'vendoruser/services/update'
            ];
            
            foreach ($blockedRoutes as $route) {
                if (strpos($currentRoute, $route) !== false) {
                    return redirect()->back()->with('error', 'Akun Anda belum terverifikasi. Silakan lengkapi profil dan tunggu verifikasi admin untuk dapat mengupload produk atau layanan.');
                }
            }
        }
        
        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }
}