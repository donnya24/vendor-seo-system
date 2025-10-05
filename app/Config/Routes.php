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
$routes->get('login',  'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->get('logout', 'Auth\AuthController::logout');  // GET logout
$routes->post('logout','Auth\AuthController::logout'); // POST logout (untuk form)

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

// app/Config/Routes.php

// ==================== ADMIN ====================

 $routes->group('admin', ['filter' => ['session', 'group:admin']], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
    $routes->get('api/stats', 'Admin\Dashboard::stats');
    $routes->get('api/leads/(:num)', 'Admin\Dashboard::getLeadDetail/$1');

    // === ROUTES PROFILE YANG DIPERBAIKI ===
    $routes->group('profile', function($routes) {
        $routes->get('/', 'Admin\Profile::index'); // Halaman utama profile
        $routes->get('edit-modal', 'Admin\Profile::editModal'); // Modal edit (AJAX)
        $routes->get('password-modal', 'Admin\Profile::passwordModal'); // Modal password (AJAX)
        $routes->post('update', 'Admin\Profile::update'); // Update profile
        $routes->post('password-update', 'Admin\Profile::passwordUpdate'); // Update password
    });
    
    $routes->group('areas', function($routes){
        $routes->get('/', 'Admin\VendorAreas::index');
        $routes->get('create', 'Admin\VendorAreas::create');
        $routes->get('edit/(:num)', 'Admin\VendorAreas::edit/$1');
        $routes->get('search', 'Admin\VendorAreas::search');
        $routes->post('attach', 'Admin\VendorAreas::attach');
        $routes->post('delete', 'Admin\VendorAreas::delete');
        $routes->post('clear-all/(:num)', 'Admin\VendorAreas::clearAll/$1');
        $routes->delete('clear-all/(:num)', 'Admin\VendorAreas::clearAll/$1');
        $routes->get('get-selected-areas/(:num)', 'Admin\VendorAreas::getSelectedAreas/$1');
    });
        
    // Vendor Services & Products
    $routes->get('services', 'Admin\VendorServices::index');
    $routes->get('services/create', 'Admin\VendorServices::create');
    $routes->post('services/store', 'Admin\VendorServices::store');
    $routes->get('services/edit/(:num)', 'Admin\VendorServices::edit/$1');
    $routes->post('services/update/(:num)', 'Admin\VendorServices::update/$1');
    $routes->post('services/delete/(:num)', 'Admin\VendorServices::delete/$1');
    $routes->get('services/search', 'Admin\VendorServices::search');

    // Users (Vendor & SEO digabung) - ROUTING YANG DIPERBAIKI
    $routes->group('users', function($routes){
        $routes->get('/', 'Admin\Users::index');
        $routes->get('create', 'Admin\Users::create');
        $routes->post('store', 'Admin\Users::store');
        
        // Edit - GET request untuk menampilkan form edit
        $routes->get('(:num)/edit', 'Admin\Users::edit/$1');

<<<<<<< HEAD
        $routes->post('(:num)/update', 'Admin\Users::update/$1');
        $routes->post('update', 'Admin\Users::update');
        
        // Delete
        $routes->post('(:num)/delete', 'Admin\Users::delete/$1');
        
        // API endpoints
        $routes->get('(:num)/email', 'Admin\Users::getEmail/$1');
        $routes->get('(:num)/vendor-data', 'Admin\Users::getVendorData/$1');
        
        // Actions
        $routes->post('(:num)/toggle-suspend', 'Admin\Users::toggleSuspend/$1');
        $routes->post('(:num)/toggle-suspend-seo', 'Admin\Users::toggleSuspendSeo/$1');
        $routes->post('(:num)/verify-vendor', 'Admin\Users::verifyVendor/$1');
        $routes->post('(:num)/reject-vendor', 'Admin\Users::rejectVendor/$1');
=======
        $routes->get('(:num)/edit', 'Admin\Users::edit/$1');   // form edit
        $routes->post('update/(:num)', 'Admin\Users::update/$1'); // PERBAIKI INI
        $routes->post('(:num)/delete', 'Admin\Users::delete/$1'); // hapus

        // Suspend Routes - FIXED (TAMBAHKAN Admin\)
        $routes->post('toggle-suspend/(:num)', 'Admin\Users::toggleSuspend/$1');
        $routes->post('toggle-suspend-seo/(:num)', 'Admin\Users::toggleSuspendSeo/$1');

        // ⭐⭐ VERIFY & REJECT VENDOR ROUTES ⭐⭐
        $routes->post('verify-vendor/(:num)', 'Admin\Users::verifyVendor/$1');
        $routes->post('reject-vendor/(:num)', 'Admin\Users::rejectVendor/$1');
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
    });

    // Vendors
    $routes->get('vendors',                   'Admin\Vendors::index');
    $routes->get('vendors/(:num)',            'Admin\Vendors::show/$1');
    $routes->post('vendors/(:num)/verify',    'Admin\Vendors::verify/$1');
    $routes->post('vendors/(:num)/unverify',  'Admin\Vendors::unverify/$1');
    $routes->post('vendors/(:num)/commission','Admin\Vendors::setCommission/$1');

    // Leads
    $routes->group('leads', function($routes){
        $routes->get('/', 'Admin\Leads::index');
        $routes->post('store', 'Admin\Leads::store');
        $routes->get('edit/(:num)', 'Admin\Leads::edit/$1');
        $routes->post('update/(:num)', 'Admin\Leads::update/$1');
        $routes->post('delete/(:num)', 'Admin\Leads::delete/$1');
        $routes->get('(:num)', 'Admin\Leads::show/$1');
    });

    // Vendor Requests (Approve / Reject) - Legacy routes
    $routes->post('vendorrequests/approve', 'Admin\VendorRequests::approve');
    $routes->post('vendorrequests/reject',  'Admin\VendorRequests::reject');

    // Commissions Management
    $routes->get('commissions', 'Admin\Commissions::index');
    $routes->post('commissions/verify/(:num)', 'Admin\Commissions::verify/$1');
    $routes->post('commissions/delete/(:num)', 'Admin\Commissions::delete/$1');
    $routes->post('commissions/bulk-action', 'Admin\Commissions::bulkAction');
        
    // Announcements
    $routes->get('announcements',                 'Admin\Announcements::index');
    $routes->get('announcements/create',          'Admin\Announcements::create');
    $routes->post('announcements/store',          'Admin\Announcements::store');
    $routes->get('announcements/(:num)/edit',     'Admin\Announcements::edit/$1');
    $routes->post('announcements/(:num)/update',  'Admin\Announcements::update/$1');
    $routes->post('announcements/(:num)/delete',  'Admin\Announcements::delete/$1');

    // Notifications - PERBAIKAN: Tambahkan namespace Admin
    $routes->get('notifications', 'Admin\Notifications::index');
    $routes->post('notifications/markRead/(:num)', 'Admin\Notifications::markRead/$1');
    $routes->post('notifications/markAllRead', 'Admin\Notifications::markAllRead');
    $routes->post('notifications/delete/(:num)', 'Admin\Notifications::delete/$1');
    $routes->post('notifications/deleteAll', 'Admin\Notifications::deleteAll');

    $routes->get('activities/vendor', 'Admin\ActivityVendor::index');
    $routes->get('activities/seo', 'Admin\ActivitySeo::index');
    // Admin Activity Logs
    $routes->get('activity-logs', 'Admin\ActivityLogs::index');
});

// Redirect untuk dashboard yang lebih spesifik
 $routes->get('admin/dashboard/index', static fn () => redirect()->to('/admin/dashboard'));

<<<<<<< HEAD
=======
 
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
    
    // Password
    $routes->get('password', 'Profile::password');
    $routes->post('password/update', 'Profile::passwordUpdate');

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

    // Commissions (✅ tidak pakai group seo di dalam seo)
    $routes->get('commissions', 'Commissions::index');
    $routes->post('commissions/approve/(:num)', 'Commissions::approve/$1');
    $routes->post('commissions/reject/(:num)', 'Commissions::reject/$1');
    $routes->post('commissions/mark-paid/(:num)', 'Commissions::markAsPaid/$1');
    $routes->delete('commissions/delete/(:num)', 'Commissions::delete/$1');

    // Pantau Leads - SEO
    $routes->get('leads', 'Leads::index'); 
    $routes->get('leads/export', 'Leads::export'); 

    // Approve Vendor
    $routes->get('vendor', 'Vendor_verify::index');
    $routes->post('vendor_verify/approve/(:num)', 'Vendor_verify::approve/$1');

    // Log Activity
    $routes->get('logs', 'Logs::index');
    
    // Notifications
        $routes->group('notif', static function ($routes) {
        $routes->get('/', 'Notifications::index');
        $routes->post('mark-read/(:num)', 'Notifications::markRead/$1');
        $routes->post('mark-all-read', 'Notifications::markAllRead');
        $routes->post('delete/(:num)', 'Notifications::delete/$1');
        $routes->post('delete-all', 'Notifications::deleteAll');
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
    $routes->get('activity_logs', 'ActivityLogs::index');

    // Notifications (FIX: hilangkan nested 'vendoruser' ganda)
    $routes->group('notifications', static function ($routes) {
        $routes->get('/',               'Notifications::index',       ['as' => 'vendor_notif_index']);
        $routes->post('(:num)/read',    'Notifications::markRead/$1', ['as' => 'vendor_notif_read']);
        $routes->post('(:num)/delete',  'Notifications::delete/$1',   ['as' => 'vendor_notif_delete']);
        $routes->post('delete-all',     'Notifications::deleteAll',   ['as' => 'vendor_notif_delete_all']);
        $routes->post('mark-all',       'Notifications::markAllRead', ['as' => 'vendor_notif_mark_all']);
        $routes->get('mark-all',        'Notifications::markAllRead'); // opsional (AJAX GET)
    });
});

 $routes->get('vendor/dashboard', static fn () => redirect()->to('/vendoruser/dashboard'));