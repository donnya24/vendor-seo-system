<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');

// Landing (publik)
$routes->get('/', 'Landpage\Landpage::index');

// Auth (publik)
$routes->get('login', 'Auth\AuthController::login');
$routes->post('login', 'Auth\AuthController::attemptLogin');
$routes->get('logout', 'Auth\AuthController::logout');  // Change POST to GET for logout
$routes->post('logout', 'Auth\AuthController::logout');  // Add POST route as well

$routes->get('register', 'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

// Forgot Password Routes
$routes->get('forgot-password', 'Auth\ForgotPasswordController::showForgotForm');
$routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
$routes->get('reset-password', 'Auth\ForgotPasswordController::showResetForm');
$routes->post('reset-password', 'Auth\ForgotPasswordController::resetPassword');

// Vendor Dashboard Route (authenticated)
$routes->get('vendor/dashboard', 'Vendor\Dashboard::index', ['filter' => 'auth']);

// Dashboard per role (session filter)
$routes->group('admin', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});
$routes->group('seo_team', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Seo_Team\Dashboard::index');
});
$routes->group('vendor', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Vendor\Dashboard::index');
});