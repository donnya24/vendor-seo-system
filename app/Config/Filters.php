<?php

namespace Config;

use CodeIgniter\Config\Filters as BaseFilters;
use CodeIgniter\Filters\CSRF;
use CodeIgniter\Filters\DebugToolbar;
use CodeIgniter\Filters\Honeypot;
use CodeIgniter\Filters\InvalidChars;
use CodeIgniter\Filters\SecureHeaders;
use CodeIgniter\Filters\Cors;
use CodeIgniter\Filters\ForceHTTPS;
use CodeIgniter\Filters\PageCache;
use CodeIgniter\Filters\PerformanceMetrics;

class Filters extends BaseFilters
{
    // Menyatakan alias untuk filter
    public array $aliases = [
        'csrf'          => CSRF::class,             // Filter CSRF
        'toolbar'       => DebugToolbar::class,     // Filter untuk toolbar debug
        'honeypot'      => Honeypot::class,          // Filter Honeypot untuk mencegah bot
        'invalidchars'  => InvalidChars::class,     // Filter untuk karakter invalid
        'secureheaders' => SecureHeaders::class,    // Filter untuk header keamanan
        'cors'          => Cors::class,             // Filter untuk CORS
        'forcehttps'    => ForceHTTPS::class,       // Filter untuk memaksa HTTPS
        'pagecache'     => PageCache::class,        // Filter untuk cache halaman
        'performance'   => PerformanceMetrics::class, // Filter untuk metrik performa

        // Filter kustom yang digunakan dalam routing tertentu
        'auth'          => \App\Filters\AuthFilter::class,   // Filter untuk autentikasi pengguna
        'role'          => \App\Filters\RoleFilter::class,   // Filter untuk peran pengguna
        'noCache'       => \App\Filters\NoCacheFilter::class, // Filter untuk menghindari cache

        // Filter Shield untuk autentikasi session
        'session'       => \CodeIgniter\Shield\Filters\SessionAuth::class,
    ];

    // Filter yang dibutuhkan sebelum dan setelah permintaan diterima
    public array $required = [
        'before' => [
            // 'forcehttps', // Matikan di localhost jika tidak ingin memaksa HTTPS
            // 'pagecache', // Matikan caching di lingkungan pengembangan
        ],
        'after' => [
            'performance',  // Menampilkan metrik performa setelah request
            'toolbar',      // Menampilkan toolbar debug setelah request
        ],
    ];

    // Filter global yang digunakan untuk semua rute
    public array $globals = [
        'before' => [
            'csrf',  // Cegah CSRF pada semua request secara global
        ],
        'after'  => [
            'noCache',  // Tidak cache untuk respons
            'toolbar',  // Tampilkan toolbar debug setelah request
        ],
    ];

    // Filter berdasarkan metode (GET, POST, dll)
    public array $methods = [];

    // Filter berdasarkan kondisi rute tertentu (jika diperlukan)
    public array $filters = [];
}
