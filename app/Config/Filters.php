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
    public array $aliases = [
        // Core
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,
        'noCache'       => \App\Filters\NoCacheFilter::class,

        // Shield
        'session'       => \CodeIgniter\Shield\Filters\SessionAuth::class,
        'group'         => \CodeIgniter\Shield\Filters\GroupFilter::class,       // <— DITAMBAH
        'permission'    => \CodeIgniter\Shield\Filters\PermissionFilter::class,  // <— opsional, biarin ada
        'vendorVerify' => \App\Filters\VendorVerificationFilter::class,
    ];

    // kosongkan required agar tidak dobel
    public array $required = [
        'before' => [],
        'after'  => [],
    ];
public array $globals = [
    'before' => [
        'csrf' => [
            'except' => [
                'admin/vendorrequests/approve',
                'admin/vendorrequests/reject',
            ],
        ],
    ],
    'after'  => [
        'noCache',
        'toolbar',
    ],
];

    // Atur CSRF hanya untuk method tulis (opsional; aman juga biarkan di $globals)
    public array $methods = [
        // 'post'   => ['csrf'],
        // 'put'    => ['csrf'],
        // 'patch'  => ['csrf'],
        // 'delete' => ['csrf'],
    ];

    public array $filters = [];
}
