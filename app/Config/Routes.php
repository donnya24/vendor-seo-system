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

$routes->get('register', 'Auth\AuthController::registerForm');
$routes->post('register', 'Auth\AuthController::registerProcess');

$routes->get('forgot-password', 'Auth\ForgotPasswordController::showForgotForm');
$routes->post('forgot-password', 'Auth\ForgotPasswordController::sendResetLink');
$routes->get('reset-password', 'Auth\ForgotPasswordController::showResetForm');
$routes->post('reset-password', 'Auth\ForgotPasswordController::resetPassword');

// Dashboard per role (PRIVAT) -> pasang filter 'session'
$routes->group('admin', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});
$routes->group('seo_team', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Seo_Team\Dashboard::index');
});
$routes->group('vendor', ['filter' => 'session'], static function ($routes) {
    $routes->get('dashboard', 'Vendor\Dashboard::index');
});
