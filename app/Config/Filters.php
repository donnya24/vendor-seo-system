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
        'csrf'          => CSRF::class,
        'toolbar'       => DebugToolbar::class,
        'honeypot'      => Honeypot::class,
        'invalidchars'  => InvalidChars::class,
        'secureheaders' => SecureHeaders::class,
        'cors'          => Cors::class,
        'forcehttps'    => ForceHTTPS::class,
        'pagecache'     => PageCache::class,
        'performance'   => PerformanceMetrics::class,

        // filter kustom (jika dipakai per-route)
        'auth'          => \App\Filters\AuthFilter::class,
        'role'          => \App\Filters\RoleFilter::class,
        'noCache'       => \App\Filters\NoCacheFilter::class,

        // Shield (akan dipasang di route group, bukan global)
        'session'       => \CodeIgniter\Shield\Filters\SessionAuth::class,
        // 'login'      => \CodeIgniter\Shield\Filters\Login::class,
        // 'token'      => \CodeIgniter\Shield\Filters\TokenAuth::class,
    ];

    // DEV: jangan paksa https / cache global
    public array $required = [
        'before' => [
            // 'forcehttps', // matikan di localhost
            // 'pagecache',
        ],
        'after' => [
            // 'pagecache',
            'performance',
            'toolbar',
        ],
    ];

    public array $globals = [
        'before' => [
            // CUKUP CSRF saja secara global
            'csrf',
        ],
        'after'  => [
            'noCache',
            'toolbar',
        ],
    ];

    public array $methods = [];

    public array $filters = [];
}
