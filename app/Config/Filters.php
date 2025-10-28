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

        // Shield (Auth)
        'session'       => \CodeIgniter\Shield\Filters\SessionAuth::class,
        'group'         => \CodeIgniter\Shield\Filters\GroupFilter::class,
        'permission'    => \CodeIgniter\Shield\Filters\PermissionFilter::class,
        'vendorVerify'  => \App\Filters\VendorVerificationFilter::class,
    ];

    public array $required = [
        'before' => [],
        'after'  => [],
    ];

    public array $globals = [
        'before' => [
            // Security protections
            'secureheaders',
            'forcehttps',

            // Session auth with exceptions (public routes)
            'session' => [
                'except' => [
                    '/',                      // Landing page
                    'login',                  // Login form
                    'register',               // Register form
                    'forgot-password',        // Forgot password
                    'reset-password',         // Reset password
                    'auth/*',                 // Google OAuth routes
                    'vendor/assets/*',        // Public assets if needed
                    'seo/assets/*',
                    'landpage/*',             // Landpage controller routes
                ],
            ],

            // CSRF protection with exceptions
            'csrf' => [
                'except' => [
                    'seo/profile/password/update', 
                ],
            ],
        ],
        'after' => [
            'noCache',
            'toolbar',
        ],
    ];

    public array $filters = [
        // Role & permission based protections
        'admin/vendorrequests/approve' => ['permission:verify_vendor'],
        'admin/vendorrequests/reject'  => ['permission:verify_vendor'],
        'admin/users/*'                => ['permission:manage_users'],
        'seo/commissions/*'            => ['permission:commission_verify'],
        'vendor/leads/*'               => ['permission:leads_crud'],

        // Restrict sensitive admin routes
        'admin/settings' => ['group:admin', 'permission:manage_settings'],
    ];
}