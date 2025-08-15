<?php namespace App\Controllers;

use CodeIgniter\HTTP\RedirectResponse;

class Dashboard extends BaseController
{
    public function index(): RedirectResponse
    {
        $user = auth()->user();
        
        if ($user->inGroup('admin')) {
            return redirect()->to('admin/dashboard');
        }
        
        if ($user->inGroup('seo_team')) {
            return redirect()->to('seo/dashboard');
        }
        
        if ($user->inGroup('vendor')) {
            return redirect()->to('vendor/dashboard');
        }
        
        // Default fallback
        return redirect()->to('logout');
    }
}