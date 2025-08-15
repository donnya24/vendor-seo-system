<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::login');
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');


// Protected routes
$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'Dashboard::index');

    // Admin
    $routes->group('admin', ['filter' => 'role:admin'], function($routes) {
        $routes->get('dashboard', 'Admin\Dashboard::index');
    });

    // SEO Team
    $routes->group('seo', ['filter' => 'role:seo_team'], function($routes) {
        $routes->get('dashboard', 'SeoTeam\Dashboard::index');
    });

    // Vendor
    $routes->group('vendor', ['filter' => 'role:vendor'], function($routes) {
        $routes->get('dashboard', 'Vendor\Dashboard::index');
    });
});
