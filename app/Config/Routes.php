<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->set404Override();

// ==================== PUBLIC ====================

// Landing (publik)
$routes->get('/', 'Landpage\Landpage::index');

// Auth (publik)
$routes->get('login', 'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->post('logout', 'Auth\AuthController::logout');

// Register (vendor)
$routes->get('register', 'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

// Forgot / Reset Password
$routes->get('forgot-password', 'Auth\ForgotPasswordController::showForgotForm');
$routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
$routes->get('reset-password', 'Auth\ForgotPasswordController::showResetForm');
$routes->post('reset-password', 'Auth\ForgotPasswordController::resetPassword');

// Util: cek status remember (opsional)
$routes->get('auth/remember-status', 'Auth\AuthController::checkRememberStatus');

// ==================== ADMIN AREA ====================
// NOTE: multiple filter harus array: ['session', 'group:admin']
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // API ringan untuk dashboard cards
    $routes->get('api/stats', 'Admin\Dashboard::stats'); // JSON

    // -------- Services (master global) --------
    $routes->get('services',                 'Admin\Services::index');
    $routes->get('services/create',          'Admin\Services::create');
    $routes->post('services/store',          'Admin\Services::store');
    $routes->get('services/(:num)/edit',     'Admin\Services::edit/$1');
    $routes->post('services/(:num)/update',  'Admin\Services::update/$1');
    $routes->post('services/(:num)/delete',  'Admin\Services::delete/$1');

    // JSON: daftar vendor berdasarkan service (opsional, untuk leads.js)
    $routes->get('services/(:num)/vendors',  'Admin\Services::vendorsByService/$1');

    // -------- Areas (master global) --------
    $routes->get('areas',                    'Admin\Areas::index');
    $routes->get('areas/create',             'Admin\Areas::create');
    $routes->post('areas/store',             'Admin\Areas::store');
    $routes->get('areas/(:num)/edit',        'Admin\Areas::edit/$1');
    $routes->post('areas/(:num)/update',     'Admin\Areas::update/$1');
    $routes->post('areas/(:num)/delete',     'Admin\Areas::delete/$1');

    // -------- Vendors --------
    $routes->get('vendors',                  'Admin\Vendors::index');
    $routes->get('vendors/(:num)',           'Admin\Vendors::show/$1');
    $routes->post('vendors/(:num)/verify',   'Admin\Vendors::verify/$1');
    $routes->post('vendors/(:num)/unverify', 'Admin\Vendors::unverify/$1');
    $routes->post('vendors/(:num)/delete',   'Admin\Vendors::delete/$1');

    // --- Vendor Products (nested) ---
    $routes->get('vendors/(:num)/products',                'Admin\VendorProducts::index/$1');
    $routes->get('vendors/(:num)/products/create',         'Admin\VendorProducts::create/$1');
    $routes->post('vendors/(:num)/products/store',         'Admin\VendorProducts::store/$1');
    $routes->get('vendors/(:num)/products/(:num)/edit',    'Admin\VendorProducts::edit/$1/$2');
    $routes->post('vendors/(:num)/products/(:num)/update', 'Admin\VendorProducts::update/$1/$2');
    $routes->post('vendors/(:num)/products/(:num)/delete', 'Admin\VendorProducts::delete/$1/$2');

    // --- Vendor Services (attach/detach ke master services) ---
    $routes->get('vendors/(:num)/services',                'Admin\VendorServices::index/$1');
    $routes->post('vendors/(:num)/services/attach',        'Admin\VendorServices::attach/$1');        // expects service_id
    $routes->post('vendors/(:num)/services/(:num)/detach', 'Admin\VendorServices::detach/$1/$2');     // $2 = service_id

    // --- Vendor Areas (attach/detach ke master areas) ---
    $routes->get('vendors/(:num)/areas',                   'Admin\VendorAreas::index/$1');
    $routes->post('vendors/(:num)/areas/attach',           'Admin\VendorAreas::attach/$1');           // expects area_id
    $routes->post('vendors/(:num)/areas/(:num)/detach',    'Admin\VendorAreas::detach/$1/$2');        // $2 = area_id

    // -------- Leads --------
    $routes->get('leads',                  'Admin\Leads::index');
    $routes->get('leads/create',           'Admin\Leads::create');
    $routes->post('leads/store',           'Admin\Leads::store');
    $routes->get('leads/(:num)/edit',      'Admin\Leads::edit/$1');
    $routes->post('leads/(:num)/update',   'Admin\Leads::update/$1');
    $routes->post('leads/(:num)/delete',   'Admin\Leads::delete/$1');

    // (opsional) API daftar vendors by area/service untuk filter
    $routes->get('api/vendors',            'Admin\Vendors::apiList'); // ?service_id=&area_id=
});

// Alias lama -> baru (biar link legacy tidak 404)
$routes->get('admin/dashboard/index', static fn () => redirect()->to('/admin/dashboard'));

// ==================== SEO AREA ====================
// NOTE: gunakan array untuk multiple filter
$routes->group('seo', ['filter' => ['session', 'group:seoteam']], static function ($routes) {
    $routes->get('dashboard', 'Seo\Dashboard::index');
    // Tambah route SEO lain nanti
});
// Alias path lama
$routes->get('seoteam/dashboard', static fn () => redirect()->to('/seo/dashboard'));
$routes->get('seo_team/dashboard', static fn () => redirect()->to('/seo/dashboard'));

// ==================== VENDOR AREA ====================
$routes->group('vendor', ['filter' => ['session', 'group:vendor']], static function ($routes) {
    $routes->get('dashboard', 'Vendor\Dashboard::index');
    // Tambah route vendor lain nanti
});
// Alias lama
$routes->get('vendoruser/dashboard', static fn () => redirect()->to('/vendor/dashboard'));
