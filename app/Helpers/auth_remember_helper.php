<?php
use CodeIgniter\I18n\Time;

if (! function_exists('force_remember_token')) {
    /**
     * Buat token remember + simpan ke auth_remember_tokens + set cookie.
     * Panggil setelah login sukses kalau $remember === true.
     */
    function force_remember_token(int $userId): void
    {
        $db = db_connect();
        if (! $db->tableExists('auth_remember_tokens')) {
            log_message('error', 'Tabel auth_remember_tokens tidak ditemukan.');
            return;
        }

        $authConfig   = config('Auth');   // Shield
        $cookieConfig = config('Cookie'); // app/Config/Cookie

        $cookieName = property_exists($authConfig, 'rememberCookieName')
            ? $authConfig->rememberCookieName : 'remember';

        $ttl = property_exists($authConfig, 'rememberLength')
            ? (int) $authConfig->rememberLength : 60 * 60 * 24 * 30; // 30 hari

        // opsional: hanya 1 token aktif/user
        $db->table('auth_remember_tokens')->where('user_id', $userId)->delete();

        $selector  = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(20));
        $hashedValidator = hash('sha256', $validator);

        $now     = Time::now()->toDateTimeString();
        $expires = Time::now()->addSeconds($ttl)->toDateTimeString();

        $db->table('auth_remember_tokens')->insert([
            'selector'        => $selector,
            'hashedValidator' => $hashedValidator,
            'user_id'         => $userId,
            'expires'         => $expires,
            'created_at'      => $now,
            'updated_at'      => $now,
        ]);

        // Simpan cookie "selector:validator"
        service('response')->setCookie(
            $cookieName,
            $selector . ':' . $validator,
            [
                'expires'  => $ttl,
                'path'     => $cookieConfig->path,
                'domain'   => $cookieConfig->domain,
                'secure'   => $cookieConfig->secure,
                'httponly' => $cookieConfig->httponly,
                'samesite' => $cookieConfig->samesite,
            ]
        );
    }
}

if (! function_exists('forget_remember_token_from_cookie')) {
    /**
     * Hapus row token berdasarkan cookie saat logout, lalu hapus cookienya.
     */
    function forget_remember_token_from_cookie(): void
    {
        $db         = db_connect();
        $authConfig = config('Auth');

        $cookieName = property_exists($authConfig, 'rememberCookieName')
            ? $authConfig->rememberCookieName : 'remember';

        if (! isset($_COOKIE[$cookieName])) {
            return;
        }

        $raw = (string) $_COOKIE[$cookieName];
        if (strpos($raw, ':') === false) {
            service('response')->deleteCookie($cookieName);
            return;
        }

        [$selector] = explode(':', $raw, 2);

        if ($db->tableExists('auth_remember_tokens')) {
            $db->table('auth_remember_tokens')->where('selector', $selector)->delete();
        }

        service('response')->deleteCookie($cookieName);
    }
}
