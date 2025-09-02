<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->set404Override();
$routes->setAutoRoute(true);

// ==================== PUBLIC ====================
$routes->get('/', 'Landpage\Landpage::index');

// Auth
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

// Util
$routes->get('auth/remember-status', 'Auth\AuthController::checkRememberStatus');

// ==================== ADMIN ====================
$routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');

    // Users
    $routes->get('users',                 'Admin\Users::index');
    $routes->get('users/create',          'Admin\Users::create');
    $routes->post('users/store',          'Admin\Users::store');
    $routes->get('users/(:num)/edit',     'Admin\Users::edit/$1');
    $routes->post('users/(:num)/update',  'Admin\Users::update/$1');
    $routes->post('users/(:num)/delete',  'Admin\Users::delete/$1');
    $routes->post('users/(:num)/toggle-suspend', 'Admin\Users::toggleSuspend/$1');

    // Vendors
    $routes->get('vendors',                   'Admin\Vendors::index');
    $routes->get('vendors/(:num)',            'Admin\Vendors::show/$1');
    $routes->post('vendors/(:num)/verify',    'Admin\Vendors::verify/$1');
    $routes->post('vendors/(:num)/unverify',  'Admin\Vendors::unverify/$1');
    $routes->post('vendors/(:num)/commission','Admin\Vendors::setCommission/$1');

    // Master Data (read only)
    $routes->get('services', 'Admin\Services::index');
    $routes->get('areas',    'Admin\Areas::index');

    // Leads (read only)
    $routes->get('leads',            'Admin\Leads::index');
    $routes->get('leads/(:num)',     'Admin\Leads::show/$1');
    $routes->get('leads/export/csv', 'Admin\Leads::exportCsv');
    $routes->get('leads/export/xlsx','Admin\Leads::exportXlsx');

    // Commissions
    $routes->get('commissions',              'Admin\Commissions::index');
    $routes->post('commissions/(:num)/paid', 'Admin\Commissions::markPaid/$1');

    // Announcements
    $routes->get('announcements',                 'Admin\Announcements::index');
    $routes->get('announcements/create',          'Admin\Announcements::create');
    $routes->post('announcements/store',          'Admin\Announcements::store');
    $routes->get('announcements/(:num)/edit',     'Admin\Announcements::edit/$1');
    $routes->post('announcements/(:num)/update',  'Admin\Announcements::update/$1');
    $routes->post('announcements/(:num)/delete',  'Admin\Announcements::delete/$1');
});
$routes->get('admin/dashboard/index', static fn () => redirect()->to('/admin/dashboard'));

// ==================== SEO ====================
$routes->group('seo', ['filter' => ['session', 'group:seoteam']], static function ($routes) {
    $routes->get('dashboard', 'Seo\Dashboard::index');
});
$routes->get('seoteam/dashboard', static fn () => redirect()->to('/seo/dashboard'));
$routes->get('seo_team/dashboard', static fn () => redirect()->to('/seo/dashboard'));

// ==================== VENDORUSER ====================
$routes->group('vendoruser', ['filter' => ['session', 'group:vendor']], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Vendoruser\Dashboard::index');

    // Profile (COCOKKAN dgn controller yang ada)
    $routes->get('profile',          'Vendoruser\Profile::edit');        // ← tadinya ::index
    $routes->post('profile/update',  'Vendoruser\Profile::update');

    // Password
    $routes->get('password',         'Vendoruser\Profile::password');
    $routes->post('password/update', 'Vendoruser\Profile::passwordUpdate');

    // Services
    $routes->get('services',                'Vendoruser\Services::index');
    $routes->get('services/create',         'Vendoruser\Services::create');
    $routes->post('services/store',         'Vendoruser\Services::store');
    $routes->get('services/(:num)/edit',    'Vendoruser\Services::edit/$1');
    $routes->post('services/(:num)/update', 'Vendoruser\Services::update/$1');
    $routes->post('services/(:num)/delete', 'Vendoruser\Services::delete/$1');

    // areas
    $routes->post('areas/toggle', 'Vendoruser\Areas::toggle');
    $routes->get('areas', 'Vendoruser\Areas::index');
    $routes->get('areas/create', 'Vendoruser\Areas::create');
    $routes->post('areas/store', 'Vendoruser\Areas::store');
    $routes->post('areas/delete', 'Vendoruser\Areas::delete');

    // Products
    $routes->get('products',                'Vendoruser\Products::index');
    $routes->get('products/create',         'Vendoruser\Products::create');
    $routes->post('products/store',         'Vendoruser\Products::store');
    $routes->get('products/(:num)/edit',    'Vendoruser\Products::edit/$1');
    $routes->post('products/(:num)/update', 'Vendoruser\Products::update/$1');
    $routes->post('products/(:num)/delete', 'Vendoruser\Products::delete/$1');


 // Leads
    $routes->get('leads',                'Vendoruser\Leads::index');
    $routes->get('leads/create',         'Vendoruser\Leads::create');
    $routes->post('leads/store',         'Vendoruser\Leads::store');
    $routes->get('leads/(:num)',         'Vendoruser\Leads::show/$1');  // TAMBAHKAN INI
    $routes->get('leads/(:num)/edit',    'Vendoruser\Leads::edit/$1');
    $routes->post('leads/(:num)/update', 'Vendoruser\Leads::update/$1');
    $routes->post('leads/(:num)/delete', 'Vendoruser\Leads::delete/$1');

    // Commissions
    $routes->get('commissions',                'Vendoruser\Commissions::index');
    $routes->get('commissions/create',         'Vendoruser\Commissions::create');
    $routes->post('commissions/store',         'Vendoruser\Commissions::store');
    $routes->get('commissions/(:num)',         'Vendoruser\Commissions::show/$1');
    $routes->get('commissions/(:num)/edit',    'Vendoruser\Commissions::edit/$1');
    $routes->post('commissions/(:num)/update', 'Vendoruser\Commissions::update/$1');
    $routes->post('commissions/(:num)/delete', 'Vendoruser\Commissions::delete/$1');

    // Activity Logs
        $routes->get('activity_logs', 'Vendoruser\ActivityLogs::index');

    // Notifikasi
    $routes->get('notifications',                 'Vendoruser\Notifications::index');
    $routes->post('notifications/(:num)/read',    'Vendoruser\Notifications::markRead/$1');
    $routes->post('notifications/mark/(:num)',    'Vendoruser\Notifications::markRead/$1'); // alias lama
    $routes->post('notifications/mark-all',       'Vendoruser\Notifications::markAllRead');
    $routes->get('notifications/mark-all',        'Vendoruser\Notifications::markAllRead'); // compat utk AJAX GET
    $routes->post('notifications/(:num)/delete',  'Vendoruser\Notifications::delete/$1');
    $routes->post('notifications/delete/(:num)',  'Vendoruser\Notifications::delete/$1');   // alias lama
    $routes->post('notifications/delete-all',     'Vendoruser\Notifications::deleteAll');
});
$routes->get('vendor/dashboard', static fn () => redirect()->to('/vendoruser/dashboard'));