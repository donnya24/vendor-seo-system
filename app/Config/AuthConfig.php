<?php

namespace App\Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

class AuthConfig extends ShieldAuth
{
    /**
     * Model untuk menangani user.
     */
    public string $userProvider = \CodeIgniter\Shield\Models\UserModel::class;

    /**
     * Daftar guards yang digunakan.
     */
    public array $guards = [
        'session' => [
            'class' => \CodeIgniter\Shield\Authentication\Handlers\SessionHandler::class,
        ],
    ];

    /**
     * Opsi lain sesuai kebutuhan.
     * Misalnya mengizinkan pendaftaran user baru:
     */
    public bool $allowRegistration = true;
}
