<?php
declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public string $defaultGroup = 'vendor';

    public array $groups = [
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Full access to manage the system.',
        ],
        'seoteam' => [
            'title'       => 'Admin SEO Team',
            'description' => 'SEO Team administrator with SEO access.',
        ],
        'vendor' => [
            'title'       => 'Vendor',
            'description' => 'Vendors who use the system with limited access.',
        ],
    ];

    public array $permissions = [
        // Admin
        'admin.access'    => 'Can access the admin area',
        'admin.settings'  => 'Can manage system settings',
        'users.manage'    => 'Can manage all users',

        // SEO Team
        'seo.access'      => 'Can access SEO features',
        'seo.manage'      => 'Can manage SEO configurations',

        // Vendor
        'vendor.access'   => 'Can access vendor dashboard',
    ];

    public array $matrix = [
        'admin' => [
            'admin.*',
            'users.*',
            'seo.*',
            'vendor.*',
        ],
        'seoteam' => [
            'seo.access',
            'seo.manage',
        ],
        'vendor' => [
            'vendor.access',
        ],
    ];
}
