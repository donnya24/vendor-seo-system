<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');

// Halaman utama
$routes->get('/', 'Landpage\Landpage::index');

// ============================
// 🔐 Auth Routes
// ============================
$routes->group('auth', static function ($routes) {
    $routes->get('login', 'Auth\AuthController::login');
    $routes->post('attemptLogin', 'Auth\AuthController::attemptLogin');
    $routes->post('logout', 'Auth\AuthController::logout');

    // Forgot & Reset Password
    $routes->get('forgot-password', 'Auth\AuthController::forgotPassword');
    $routes->post('forgot-password', 'Auth\AuthController::attemptForgotPassword');
    $routes->get('reset-password/(:any)', 'Auth\AuthController::resetPassword/$1');
    $routes->post('reset-password/(:any)', 'Auth\AuthController::attemptResetPassword/$1');
});

// ============================
// 📊 Dashboard Routes (per role)
// ============================
$routes->group('admin', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});

$routes->group('seo_team', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Seo_Team\Dashboard::index');
});

$routes->group('vendor', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Vendor\Dashboard::index');
});
