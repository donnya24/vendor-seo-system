<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');

// Landing (publik)
$routes->get('/', 'Landpage\Landpage::index');

// Auth (publik)
$routes->get('login', 'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->post('logout', 'Auth\AuthController::logout');

// Register
$routes->get('register', 'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

// Forgot / Reset Password
$routes->get('forgot-password', 'Auth\ForgotPasswordController::showForgotForm');
$routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
$routes->get('reset-password', 'Auth\ForgotPasswordController::showResetForm');
$routes->post('reset-password', 'Auth\ForgotPasswordController::resetPassword');

// Vendor Dashboard (login via Shield session)
$routes->group('vendoruser', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Vendoruser\Dashboard::index');
});
// Alias lama
$routes->get('vendor/dashboard', static function () {
    return redirect()->to('/vendoruser/dashboard');
});

// Admin Dashboard (login via Shield session)
$routes->group('admin', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});

// SEO Team Dashboard (login via Shield session)
$routes->group('seoteam', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Seo_Team\Dashboard::index');
});
// Alias path lama -> baru
$routes->get('seo_team/dashboard', static function () {
    return redirect()->to('/seoteam/dashboard');
});
