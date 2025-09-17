<?php
declare(strict_types=1);

namespace Config;

use CodeIgniter\Shield\Config\AuthGroups as ShieldAuthGroups;

class AuthGroups extends ShieldAuthGroups
{
    public string $defaultGroup = 'vendor';

    /**
     * Grup user yang ada di sistem
     */
    public array $groups = [
        'admin' => [
            'title'       => 'Administrator',
            'description' => 'Full access untuk mengelola sistem.',
        ],
        'seoteam' => [
            'title'       => 'SEO Team',
            'description' => 'Tim SEO untuk monitoring, laporan, dan komisi.',
        ],
        'vendor' => [
            'title'       => 'Vendor',
            'description' => 'Vendor yang menggunakan sistem dengan akses terbatas.',
        ],
    ];

    /**
     * Daftar permission yang tersedia
     */
    public array $permissions = [
        // --- Admin ---
        'verify_vendor'      => 'Verifikasi / Tolak vendor',
        'manage_users'       => 'Kelola akun user (Admin/SEO/Vendor)',
        'master_data_read'   => 'Lihat master data layanan & area (read-only)',
        'leads_audit'        => 'Pantau & audit leads (read/export)',
        'commission_manage'  => 'Pantau & verifikasi komisi',
        'announcement_crud'  => 'CRUD pengumuman',
        'activity_log'       => 'Lihat activity log',

        // --- SEO Team ---
        'approve_vendor'     => 'Approve vendor (opsional)',
        'leads_view'         => 'Pantau leads (read-only)',
        'commission_verify'  => 'Verifikasi pembayaran komisi',
        'reports_manage'     => 'Kelola laporan SEO & target keyword',

        // --- Vendor ---
        'profile_manage'     => 'Lengkapi & kelola profil vendor',
        'products_manage'    => 'CRUD layanan, produk & area',
        'commission_request' => 'Ajukan persentase komisi',
        'leads_crud'         => 'Kelola leads sendiri (CRUD)',
        'reports_payment'    => 'Input laporan bulanan & pembayaran komisi',
    ];

    /**
     * Mapping grup ke permission
     */
    public array $matrix = [
        'admin' => [
            'verify_vendor',
            'manage_users',
            'master_data_read',
            'leads_audit',
            'commission_manage',
            'announcement_crud',
            'activity_log',
        ],

        'seoteam' => [
            'approve_vendor',
            'leads_view',
            'commission_verify',
            'reports_manage',
        ],

        'vendor' => [
            'profile_manage',
            'products_manage',
            'commission_request',
            'leads_crud',
            'reports_payment',
        ],
    ];
}
