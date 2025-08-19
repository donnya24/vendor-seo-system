<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');

// Landing (publik)
$routes->get('/', 'Landpage\Landpage::index');

// Auth (publik)
$routes->get('login', 'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');

// Logout via POST (AMAN & sesuai form di view)
$routes->post('logout', 'Auth\AuthController::logout');

// Register
$routes->get('register', 'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

// Forgot Password
$routes->get('forgot-password', 'Auth\ForgotPasswordController::showForgotForm');
$routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
$routes->get('reset-password', 'Auth\ForgotPasswordController::showResetForm');
$routes->post('reset-password', 'Auth\ForgotPasswordController::resetPassword');

// Vendoruser Dashboard (HARUS login)
$routes->group('vendoruser', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'vendoruser\Dashboard::index');
});

// (Opsional) Redirect rute lama agar link /vendor/dashboard tetap hidup
$routes->get('vendor/dashboard', static function () {
    return redirect()->to('/vendoruser/dashboard');
});

// Dashboard per role (HARUS login)
$routes->group('admin', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});
$routes->group('seo_team', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Seo_Team\Dashboard::index');
});
