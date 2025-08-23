<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->set404Override();

// ---------- PUBLIC ----------
$routes->get('/', 'Landpage\Landpage::index');
$routes->get('login', 'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->post('logout', 'Auth\AuthController::logout');

$routes->get('register', 'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

$routes->get('forgot-password', 'Auth\ForgotPasswordController::showForgotForm');
$routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
$routes->get('reset-password', 'Auth\ForgotPasswordController::showResetForm');
$routes->post('reset-password', 'Auth\ForgotPasswordController::resetPassword');

$routes->get('auth/remember-status', 'Auth\AuthController::checkRememberStatus');

// ---------- ADMIN ----------
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('api/stats', 'Admin\Dashboard::stats');

    $routes->get('users',                 'Admin\Users::index');
    $routes->get('users/create',          'Admin\Users::create');
    $routes->post('users/store',          'Admin\Users::store');
    $routes->get('users/(:num)/edit',     'Admin\Users::edit/$1');
    $routes->post('users/(:num)/update',  'Admin\Users::update/$1');
    $routes->post('users/(:num)/delete',  'Admin\Users::delete/$1');
    $routes->post('users/(:num)/toggle-suspend', 'Admin\Users::toggleSuspend/$1');

    // Vendors (verifikasi / tolak + lihat detail)
    $routes->get('vendors',            'Admin\Vendors::index');
    $routes->get('vendors/(:num)',     'Admin\Vendors::show/$1');
    $routes->post('vendors/(:num)/verify',   'Admin\Vendors::verify/$1');     // set verified
    $routes->post('vendors/(:num)/unverify', 'Admin\Vendors::unverify/$1');   // set pending/rejected
    $routes->post('vendors/(:num)/commission', 'Admin\Vendors::setCommission/$1'); // set commission_rate

    // Master Data (READ-ONLY untuk Admin)
    $routes->get('services', 'Admin\Services::index'); // list saja
    $routes->get('areas',    'Admin\Areas::index');    // list saja

    // Leads (READ-ONLY untuk Admin)
    $routes->get('leads',        'Admin\Leads::index'); // filter + list
    $routes->get('leads/(:num)', 'Admin\Leads::show/$1'); // detail
    $routes->get('leads/export/csv',  'Admin\Leads::exportCsv');
    $routes->get('leads/export/xlsx', 'Admin\Leads::exportXlsx');

    // Commissions (pantau & verifikasi pembayaran)
    $routes->get('commissions',           'Admin\Commissions::index');           // rekap/bulan
    $routes->post('commissions/(:num)/paid', 'Admin\Commissions::markPaid/$1');  // set paid & paid_at

    // Announcements (CRUD)
    $routes->get('announcements',              'Admin\Announcements::index');
    $routes->get('announcements/create',       'Admin\Announcements::create');
    $routes->post('announcements/store',       'Admin\Announcements::store');
    $routes->get('announcements/(:num)/edit',  'Admin\Announcements::edit/$1');
    $routes->post('announcements/(:num)/update','Admin\Announcements::update/$1');
    $routes->post('announcements/(:num)/delete','Admin\Announcements::delete/$1');
});

$routes->get('admin/dashboard/index', static fn () => redirect()->to('/admin/dashboard'));

// ---------- SEO (biarkan dulu) ----------
$routes->group('seo', ['filter' => ['session', 'group:seoteam']], static function ($routes) {
    $routes->get('dashboard', 'Seo\Dashboard::index');
});
$routes->get('seoteam/dashboard', static fn () => redirect()->to('/seo/dashboard'));
$routes->get('seo_team/dashboard', static fn () => redirect()->to('/seo/dashboard'));

// ---------- VENDOR (biarkan dulu) ----------
$routes->group('vendor', ['filter' => ['session', 'group:vendor']], static function ($routes) {
    $routes->get('dashboard', 'Vendor\Dashboard::index');
});
$routes->get('vendoruser/dashboard', static fn () => redirect()->to('/vendor/dashboard'));
