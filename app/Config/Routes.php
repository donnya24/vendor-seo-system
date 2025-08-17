<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// (opsional, tapi bikin eksplisit)
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Landpage\Landpage');
$routes->setDefaultMethod('index');
$routes->setAutoRoute(false);  // disarankan biar gak tabrakan rute

// Landing di root
$routes->get('/', 'Landpage\Landpage::index', ['as' => 'landing']);

// Rute Shield (login/register/logout) — ini TIDAK menimpa '/'
service('auth')->routes($routes);

// Dashboard (wajib login)
$routes->group('dashboard', ['filter' => 'session'], static function ($routes) {
    $routes->get('/', 'Dashboard::index');
});

// (opsional) dashboard per role yang kamu punya
$routes->group('admin', static function ($routes) {
    $routes->get('dashboard', 'Admin\Dashboard::index');
});
$routes->group('seo_team', static function ($routes) {
    $routes->get('dashboard', 'Seo_Team\Dashboard::index');
});
$routes->group('vendor', static function ($routes) {
    $routes->get('dashboard', 'Vendor\Dashboard::index');
});
