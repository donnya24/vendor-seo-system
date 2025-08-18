<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setDefaultNamespace('App\Controllers');
$routes->get('/', 'Landpage\Landpage::index');

// Auth
$routes->get('/login', 'Auth\AuthController::login');
$routes->post('auth/attemptLogin', 'Auth\AuthController::attemptLogin');
$routes->post('logout', 'Auth\AuthController::logout');

// Forgot & Reset Password
$routes->get('forgot-password', 'Auth\AuthController::forgotPassword');
$routes->post('forgot-password', 'Auth\AuthController::attemptForgotPassword');
$routes->get('reset-password/(:any)', 'Auth\AuthController::resetPassword/$1');
$routes->post('reset-password/(:any)', 'Auth\AuthController::attemptResetPassword/$1');

// Dashboard per role
$routes->group('admin', ['filter' => 'session'], static fn($routes) => $routes->get('dashboard', 'Admin\Dashboard::index'));
$routes->group('seo_team', ['filter' => 'session'], static fn($routes) => $routes->get('dashboard', 'Seo_Team\Dashboard::index'));
$routes->group('vendor', ['filter' => 'session'], static fn($routes) => $routes->get('dashboard', 'Vendor\Dashboard::index'));
