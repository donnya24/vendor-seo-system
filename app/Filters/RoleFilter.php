<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login')->with('error', 'Please login first');
        }

        $requiredRole = $arguments[0] ?? null;
        
        if ($requiredRole && !auth()->user()->inGroup($requiredRole)) {
            return redirect()->back()->with('error', 'You do not have permission to access this area');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do something here
    }

    public $aliases = [
    'auth' => \CodeIgniter\Shield\Filters\SessionAuth::class,
    'role' => \App\Filters\RoleFilter::class,
    // ... lainnya
];
}