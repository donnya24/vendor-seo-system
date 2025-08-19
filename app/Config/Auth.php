<?php
namespace Config;

use CodeIgniter\Shield\Config\Auth as ShieldAuth;

class Auth extends ShieldAuth
{
    // Pastikan 'session' ada dan menjadi default
    public array $authenticators = [
        'session' => \CodeIgniter\Shield\Authentication\Authenticators\Session::class,
        'tokens'  => \CodeIgniter\Shield\Authentication\Authenticators\AccessTokens::class,
        'hmac'    => \CodeIgniter\Shield\Authentication\Authenticators\HmacSha256::class,
        // 'jwt'   => \CodeIgniter\Shield\Authentication\Authenticators\JWT::class,
    ];

    public string $defaultAuthenticator = 'session';

    // Inilah yang dipakai versi Shield-mu: $sessionConfig (bukan AuthSession)
    public array $sessionConfig = [
        'field'              => 'user',
        'allowRemembering'   => true,
        'rememberCookieName' => 'remember',     // nama cookie
        'rememberLength'     => 30 * DAY,       // lama remember (detik)
    ];
}
