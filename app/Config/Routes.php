<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
 $routes->setDefaultNamespace('App\Controllers');
 $routes->setDefaultController('Home');
 $routes->setDefaultMethod('index');
 $routes->set404Override();
 $routes->setAutoRoute(true);

// ==================== PUBLIC ====================
// Route untuk halaman utama
 $routes->get('/', 'LandingPage\LandingPageController::index');

// ==================== AUTH ====================
// Google OAuth Routes - DIPISAH UNTUK LOGIN DAN REGISTER
$routes->get('auth/google/login', 'Auth\AuthController::googleLogin');
$routes->get('auth/google/register', 'Auth\AuthController::googleRegister');
$routes->get('auth/google/callback', 'Auth\AuthController::googleCallback');

$routes->get('login',  'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->get('logout', 'Auth\AuthController::logout');
$routes->post('logout','Auth\AuthController::logout');

// Register (vendor)
$routes->get('register',  'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

// Forgot / Reset Password
 $routes->get('forgot-password',  'Auth\ForgotPasswordController::showForgotForm');
 $routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
 $routes->get('reset-password',   'Auth\ForgotPasswordController::showResetForm');
 $routes->post('reset-password',  'Auth\ForgotPasswordController::resetPassword');

// Util
 $routes->get('auth/remember-status', 'Auth\AuthController::checkRememberStatus');


// ==================== ADMIN ====================

$routes->group('admin', ['filter' => ['session', 'group:admin'], 'namespace' => 'App\Controllers\Admin'], static function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('api/stats', 'Dashboard::stats');
    $routes->get('api/leads/(:num)', 'Dashboard::getLeadDetail/$1');

    // === ROUTES PROFILE YANG DIPERBAIKI ===
    $routes->group('profile', function($routes) {
        $routes->get('/', 'Profile::index'); // Halaman utama profile
        $routes->get('edit-modal', 'Profile::editModal'); // Modal edit (AJAX)
        $routes->get('password-modal', 'Profile::passwordModal'); // Modal password (AJAX)
        $routes->post('update', 'Profile::update'); // Update profile
        $routes->post('password-update', 'Profile::passwordUpdate'); // Update password
    });

    // ===== NOTIFICATION HEADER ROUTES (BELL ICON dan modal notif pada header) =====
    $routes->group('notifications', function($routes) {
        $routes->get('modal-data', 'Notifications::modalData');
        $routes->post('markRead/(:num)', 'Notifications::markRead/$1');
        $routes->post('markAllRead', 'Notifications::markAllRead');
        $routes->post('delete/(:num)', 'Notifications::delete/$1');
        $routes->post('deleteAll', 'Notifications::deleteAll');
    }); 
    
    
    // Kelolanotifikasi, untuk mengelola seluruh notifikasi pada user admin
    $routes->group('notification-management', function($routes) {
        $routes->get('/', 'KelolaNotifikasi::index');
        $routes->get('create', 'KelolaNotifikasi::create');
        $routes->get('edit/(:num)', 'KelolaNotifikasi::edit/$1');
        $routes->get('get-users', 'KelolaNotifikasi::getUserList');
        $routes->post('store', 'KelolaNotifikasi::store');
        $routes->post('update/(:num)', 'KelolaNotifikasi::update/$1');
        $routes->post('delete/(:num)', 'KelolaNotifikasi::delete/$1');
        $routes->post('delete-selected', 'KelolaNotifikasi::deleteSelected');
        $routes->post('delete-all', 'KelolaNotifikasi::deleteAll');
        $routes->get('user-state', 'KelolaNotifikasi::userState');
        $routes->post('user-state/delete-all', 'KelolaNotifikasi::deleteAllUserState');
        $routes->post('restore/(:num)', 'KelolaNotifikasi::restore/$1');
        $routes->post('restore-all', 'KelolaNotifikasi::restoreAll');
        $routes->post('delete-permanent/(:num)', 'KelolaNotifikasi::deletePermanent/$1');
        $routes->post('delete-selected-permanent', 'KelolaNotifikasi::deleteSelectedPermanent');
        $routes->get('get-notification/(:num)', 'KelolaNotifikasi::getNotification/$1');
    });
        
    // Add this route to map /admin/areas to VendorAreas controller
    $routes->get('areas', 'VendorAreas::index');
    $routes->get('areas/create', 'VendorAreas::create');
    $routes->get('areas/edit/(:segment)', 'VendorAreas::edit/$1');
    $routes->post('areas/attach', 'VendorAreas::attach');
    $routes->post('areas/delete', 'VendorAreas::delete');
    $routes->get('areas/search', 'VendorAreas::search');
    $routes->get('areas/get-selected-areas/(:segment)', 'VendorAreas::getSelectedAreas/$1');
    $routes->post('areas/clear-all/(:segment)', 'VendorAreas::clearAll/$1');

    // Vendor Services & Products
    $routes->get('services', 'VendorServicesProducts::index');
    $routes->get('services/create', 'VendorServicesProducts::create');
    $routes->post('services/store', 'VendorServicesProducts::store');
    $routes->get('services/edit', 'VendorServicesProducts::edit');
    $routes->post('services/update', 'VendorServicesProducts::update');
    $routes->post('services/delete_multiple', 'VendorServicesProducts::deleteMultiple');
    $routes->post('services/delete/(:num)', 'VendorServicesProducts::delete/$1');
    $routes->get('services/search', 'VendorServicesProducts::search');

    // Management User Vendor Routes
    $routes->group('uservendor', ['namespace' => 'App\Controllers\Admin'], function($routes){
        $routes->get('/', 'UserVendor::index');
        $routes->get('create', 'UserVendor::create');
        $routes->post('store', 'UserVendor::store');
        $routes->get('(:num)/edit', 'UserVendor::edit/$1');
        $routes->post('(:num)/update', 'UserVendor::update/$1');
        $routes->post('update', 'UserVendor::update');
        $routes->post('(:num)/delete', 'UserVendor::delete/$1');
        $routes->get('(:num)/vendor-data', 'UserVendor::getVendorData/$1');
        
        // Perbaikan routing untuk verify, reject, dan suspend
        $routes->post('(:num)/verify', 'UserVendor::verifyVendor/$1');
        $routes->post('(:num)/reject', 'UserVendor::rejectVendor/$1');
        $routes->post('(:num)/suspend', 'UserVendor::toggleSuspend/$1');
    });

    // Management User SEO Routes
    $routes->group('userseo', ['namespace' => 'App\Controllers\Admin'], function($routes){
        $routes->get('/', 'UserSeo::index');
        $routes->get('create', 'UserSeo::create');
        $routes->post('store', 'UserSeo::store');    
        $routes->get('edit/(:num)', 'UserSeo::edit/$1');
        $routes->post('update/(:num)', 'UserSeo::update/$1');
        $routes->post('delete/(:num)', 'UserSeo::delete/$1');
        $routes->post('toggle-suspend-seo/(:num)', 'UserSeo::toggleSuspendSeo/$1');
    });

        // Tambahkan route untuk approve dan reject vendor request dengan format dash-separated
    $routes->post('dashboard/approve-vendor-request', 'Dashboard::approveVendorRequest');
    $routes->post('dashboard/reject-vendor-request', 'Dashboard::rejectVendorRequest');

    // Vendors
    $routes->get('vendors',                   'Vendors::index');
    $routes->get('vendors/(:num)',            'Vendors::show/$1');
    $routes->post('vendors/(:num)/verify',    'Vendors::verify/$1');
    $routes->post('vendors/(:num)/unverify',  'Vendors::unverify/$1');
    $routes->post('vendors/(:num)/commission','Vendors::setCommission/$1');

    // Targets Route
    $routes->group('targets', function($routes){
        $routes->get('/', 'Targets::index');
        $routes->post('store', 'Targets::store');
        $routes->post('update/(:num)', 'Targets::update/$1');
        $routes->get('edit/(:num)', 'Targets::edit/$1');
        $routes->post('delete/(:num)', 'Targets::delete/$1');
        $routes->get('export-csv', 'Targets::exportCsv');
    });

    //Reports
    $routes->get('reports', 'Reports::index');
     $routes->get('reports/export-csv', 'Reports::exportCsv');

    // Leads
    $routes->group('leads', function($routes){
        $routes->get('/', 'Leads::index');
        $routes->post('store', 'Leads::store');
        $routes->get('edit/(:num)', 'Leads::edit/$1');
        $routes->post('update/(:num)', 'Leads::update/$1');
        $routes->post('delete/(:num)', 'Leads::delete/$1');
        $routes->post('delete-all', 'Leads::deleteAll');
        $routes->get('(:num)', 'Leads::show/$1');
         $routes->get('export', 'Leads::export');
    });

    // Vendor Requests (Approve / Reject) - Legacy routes
    $routes->post('vendorrequests/approve', 'VendorRequests::approve');
    $routes->post('vendorrequests/reject',  'VendorRequests::reject');

    // Commissions Management
    $routes->get('commissions', 'Commissions::index');
    $routes->post('commissions/verify/(:num)', 'Commissions::verify/$1');
    $routes->post('commissions/delete/(:num)', 'Commissions::delete/$1');
    $routes->post('commissions/bulk-action', 'Commissions::bulkAction');
     $routes->post('commissions/unpaid/(:num)', 'Commissions::unpaid/$1');
    $routes->get('commissions/export-csv', 'Commissions::exportCsv');
        
    // Announcements
    $routes->get('announcements',                 'Announcements::index');
    $routes->get('announcements/create',          'Announcements::create');
    $routes->post('announcements/store',          'Announcements::store');
    $routes->get('announcements/(:num)/edit',     'Announcements::edit/$1');
    $routes->post('announcements/(:num)/update',  'Announcements::update/$1');
    $routes->post('announcements/(:num)/delete',  'Announcements::delete/$1');


    // Admin - SEO Management
    $routes->group('admin/seo-management', static function($routes) {
        $routes->get('/', 'SeoManagement::index');
        $routes->post('toggle-status/(:num)', 'SeoManagement::toggleStatus/$1');
    });

    // Activity Routes - DIPERBAIKI
    $routes->get('activities/vendor', 'ActivityVendor::index');
    $routes->post('activities/vendor/delete-all', 'ActivityVendor::deleteAll');

    $routes->get('activities/seo', 'ActivitySeo::index');
    $routes->post('activities/seo/delete-all', 'ActivitySeo::deleteAll');

    // Admin Activity Logs
    $routes->get('activity-logs', 'ActivityLogs::index');
    $routes->post('activity-logs/delete-all', 'ActivityLogs::deleteAll');
});

// Redirect untuk dashboard yang lebih spesifik
$routes->get('admin/dashboard/index', static fn () => redirect()->to('/admin/dashboard'));

// ---------- SEO ----------
 $routes->group('seo', [
    'namespace' => 'App\Controllers\Seo',
    'filter'    => ['session', 'group:seoteam']
], static function ($routes) {

    // Dashboard
    $routes->get('dashboard', 'Dashboard::index');

    // Profile
    $routes->get('profile', 'Profile::index');
    $routes->get('profile/edit', 'Profile::edit');
    $routes->post('profile/update', 'Profile::update');
    
    // Password - PERBAIKAN ROUTING
    $routes->get('profile/password', 'Profile::password');
    $routes->post('profile/password/update', 'Profile::passwordUpdate');

    // Keyword Targets
    $routes->get('targets', 'Targets::index');
    $routes->get('targets/create', 'Targets::create');
    $routes->post('targets/store', 'Targets::store');
    $routes->get('targets/edit/(:num)', 'Targets::edit/$1');
    $routes->post('targets/update/(:num)', 'Targets::update/$1');
    $routes->post('targets/delete/(:num)', 'Targets::delete/$1');

    // Reports
    $routes->get('reports', 'Reports::index');
    $routes->get('reports/create', 'Reports::create');
    $routes->post('reports/store', 'Reports::store');
    $routes->get('reports/edit/(:num)', 'Reports::edit/$1');
    $routes->post('reports/update/(:num)', 'Reports::update/$1');
    $routes->post('reports/delete/(:num)', 'Reports::delete/$1');

    // Commissions (tidak pakai group seo di dalam seo)
    $routes->get('commissions', 'Commissions::index');
    $routes->post('commissions/approve/(:num)', 'Commissions::approve/$1');
    $routes->post('commissions/reject/(:num)', 'Commissions::reject/$1');
    $routes->post('commissions/mark-paid/(:num)', 'Commissions::markAsPaid/$1');
    $routes->delete('commissions/delete/(:num)', 'Commissions::delete/$1');

    // Pantau Leads - SEO
    $routes->get('leads', 'Leads::index'); 
    $routes->get('leads/export', 'Leads::export'); 

    // Approve & Reject Vendor
    $routes->get('vendor', 'Vendor_verify::index');
    $routes->post('vendor_verify/approve/(:num)', 'Vendor_verify::approve/$1');
    $routes->post('vendor_verify/reject/(:num)',        'Vendor_verify::reject/$1');

    // Log Activity
    $routes->get('logs', 'Logs::index');
    
    // Notifications
    $routes->group('notif', static function ($routes) {
        $routes->get('/', 'Notifications::index');
        $routes->post('mark-read/(:num)', 'Notifications::markRead/$1');
        $routes->post('mark-all-read', 'Notifications::markAllRead');
        $routes->post('delete/(:num)', 'Notifications::delete/$1');
        $routes->post('delete-all', 'Notifications::deleteAll');
        $routes->get('count-unread', 'Notifications::countUnread');
    });

});

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
    $routes->get('leads',                  'Leads::index');
    $routes->get('leads/create',           'Leads::create');
    $routes->post('leads/store',           'Leads::store');
    $routes->get('leads/(:num)',           'Leads::show/$1');
    $routes->get('leads/(:num)/edit',      'Leads::edit/$1');
    $routes->post('leads/(:num)/update',   'Leads::update/$1');
    $routes->post('leads/(:num)/delete',   'Leads::delete/$1');
    $routes->post('leads/delete-multiple', 'Leads::deleteMultiple');

    // ServicesProducts
    $routes->get( 'services-products',                'ServicesProducts::index',        ['as' => 'sp_index']);
    $routes->get( 'services-products/create',         'ServicesProducts::createGroup',  ['as' => 'sp_create_group']);
    $routes->post('services-products/store',          'ServicesProducts::store',        ['as' => 'sp_store']);
    $routes->get( 'services-products/edit-group',     'ServicesProducts::editGroup',    ['as' => 'sp_edit_group']);
    $routes->post('services-products/update-group',   'ServicesProducts::updateGroup',  ['as' => 'sp_update_group']);
    $routes->get( 'services-products/delete/(:num)',  'ServicesProducts::delete/$1',    ['as' => 'sp_delete']);
    $routes->post('services-products/delete-multiple','ServicesProducts::deleteMultiple',['as' => 'sp_delete_multiple']);

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
    $routes->get('activity-logs', 'ActivityLogs::index');

    // Notifications (FIX: hilangkan nested 'vendoruser' ganda)
    $routes->group('notifications', static function ($routes) {
        $routes->get('/',               'Notifications::index',       ['as' => 'vendor_notif_index']);
        $routes->post('(:num)/read',    'Notifications::markRead/$1', ['as' => 'vendor_notif_read']);
        $routes->post('(:num)/delete',  'Notifications::delete/$1',   ['as' => 'vendor_notif_delete']);
        $routes->post('delete-all',     'Notifications::deleteAll',   ['as' => 'vendor_notif_delete_all']);
        $routes->post('mark-all',       'Notifications::markAllRead', ['as' => 'vendor_notif_mark_all']);
        $routes->get('mark-all',        'Notifications::markAllRead'); // opsional (AJAX GET)
        // Tambahkan route baru di routes.php (di dalam group notifications)
        $routes->get('ajax', 'Notifications::getNotificationsAjax', ['as' => 'vendor_notif_ajax']);
        // Di routes file
        $routes->get('vendoruser/notifications/csrf', 'Vendoruser\Notifications::getCsrfToken', ['as' => 'vendor_notif_csrf']);
    });
});

 $routes->get('vendor/dashboard', static fn () => redirect()->to('/vendoruser/dashboard'));