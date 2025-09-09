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
$routes->group('vendoruser', [
    'filter'    => ['session', 'group:vendor'],
    'namespace' => 'App\Controllers\Vendoruser'
], static function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');

    // Profile
    $routes->get('profile',         'Profile::edit');
    $routes->post('profile/update', 'Profile::update');

    // Password
    $routes->get('password',         'Profile::password');
    $routes->post('password/update', 'Profile::passwordUpdate');

    // Areas
    $routes->group('areas', static function ($routes) {
        $routes->get('/',       'Areas::index');
        $routes->get('create',  'Areas::create');
        $routes->get('edit',    'Areas::edit');
        $routes->get('search',  'Areas::search');
        $routes->post('attach', 'Areas::attach');
        $routes->post('delete', 'Areas::delete');
    });

    // Leads
    $routes->get('leads',                 'Leads::index');
    $routes->get('leads/create',          'Leads::create');
    $routes->post('leads/store',          'Leads::store');
    $routes->get('leads/(:num)',          'Leads::show/$1');
    $routes->get('leads/(:num)/edit',     'Leads::edit/$1');
    $routes->post('leads/(:num)/update',  'Leads::update/$1');
    $routes->post('leads/(:num)/delete',  'Leads::delete/$1');
    $routes->post('leads/delete-multiple','Leads::deleteMultiple');

    // ServicesProducts
    $routes->get( 'services-products',              'ServicesProducts::index',        ['as' => 'sp_index']);
    $routes->get( 'services-products/create',       'ServicesProducts::createGroup',  ['as' => 'sp_create_group']);
    $routes->post('services-products/store',        'ServicesProducts::store',        ['as' => 'sp_store']);
    $routes->get( 'services-products/edit-group',   'ServicesProducts::editGroup',    ['as' => 'sp_edit_group']);
    $routes->post('services-products/update-group', 'ServicesProducts::updateGroup',  ['as' => 'sp_update_group']);
    $routes->get( 'services-products/delete/(:num)','ServicesProducts::delete/$1',    ['as' => 'sp_delete']);
    $routes->post('services-products/delete-multiple','ServicesProducts::deleteMultiple', ['as' => 'sp_delete_multiple']);

    // Commissions
    $routes->group('commissions', static function($routes) {
        $routes->get('/',               'Commissions::index');
        $routes->get('create',          'Commissions::create');
        $routes->post('store',          'Commissions::store');
        $routes->get('(:num)/edit',     'Commissions::edit/$1');
        $routes->post('(:num)/update',  'Commissions::update/$1');
        $routes->post('(:num)/delete',  'Commissions::delete/$1');
        $routes->post('delete-multiple','Commissions::deleteMultiple');
    });

    // Activity Logs
    $routes->get('activity_logs', 'ActivityLogs::index');

    // Notifications (rapi, dukung AJAX GET utk mark-all)
    $routes->group('notifications', static function ($routes) {
        $routes->get('/',                      'Notifications::index',        ['as' => 'vendor_notif_index']);
        $routes->post('(:num)/read',           'Notifications::markRead/$1',  ['as' => 'vendor_notif_read']);
        $routes->post('(:num)/delete',         'Notifications::delete/$1',    ['as' => 'vendor_notif_delete']);
        $routes->post('delete-all',            'Notifications::deleteAll',    ['as' => 'vendor_notif_delete_all']);
        $routes->post('mark-all',              'Notifications::markAllRead',  ['as' => 'vendor_notif_mark_all']);
        $routes->get('mark-all',               'Notifications::markAllRead'); // AJAX GET compat
        // Alias lama (opsional, boleh dihapus kalau tak dipakai):
        $routes->post('mark/(:num)',           'Notifications::markRead/$1');
        $routes->post('delete/(:num)',         'Notifications::delete/$1');
    });
});

$routes->get('vendor/dashboard', static fn () => redirect()->to('/vendoruser/dashboard'));