<?php

use CodeIgniter\Database\BaseConnection;

if (! function_exists('resolve_identity_table')) {
    /**
     * Kembalikan nama tabel identity yang tersedia.
     * - 'auth_identities' atau fallback 'auth_identites'
     */
    function resolve_identity_table(): string
    {
        $db = db_connect();
        if ($db->tableExists('auth_identities')) return 'auth_identities';
        if ($db->tableExists('auth_identites'))  return 'auth_identites';
        throw new RuntimeException('Tabel identity tidak ditemukan: auth_identities / auth_identites.');
    }
}

if (! function_exists('identity_exists')) {
    /**
     * Cek apakah identity (email_password) sudah ada untuk email tertentu.
     */
    function identity_exists(string $email, string $type = 'email_password'): bool
    {
        $db    = db_connect();
        $table = resolve_identity_table();
        return (bool) $db->table($table)
            ->where('type', $type)
            ->where('secret', $email)
            ->countAllResults();
    }
}

if (! function_exists('create_email_password_identity')) {
    /**
     * Buat identity email+password untuk user.
     * - email -> secret
     * - hash password -> secret2
     */
    function create_email_password_identity(int $userId, string $email, string $password): void
    {
        $db    = db_connect();
        $table = resolve_identity_table();

        $db->table($table)->insert([
            'user_id'     => $userId,
            'type'        => 'email_password',
            'secret'      => $email,
            'secret2'     => password_hash($password, PASSWORD_DEFAULT),
            'force_reset' => 0,
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}

if (! function_exists('get_identity_email')) {
    /**
     * Ambil email (secret) dari identity milik user_id (type=email_password).
     */
    function get_identity_email(int $userId): ?string
    {
        $db    = db_connect();
        $table = resolve_identity_table();
        $row   = $db->table($table)
            ->select('secret')
            ->where('user_id', $userId)
            ->where('type', 'email_password')
            ->get()->getRow();
        return $row->secret ?? null;
    }
}

if (! function_exists('make_unique_username')) {
    /**
     * Buat username unik (<=30 char) dari vendor_name/email.
     * Hanya a-z, 0-9, dan titik.
     */
    function make_unique_username(string $vendorName, string $email): string
    {
        $db   = db_connect();
        $base = $vendorName !== '' ? $vendorName : (strstr($email, '@', true) ?: 'vendor');
        $base = strtolower($base);
        $base = preg_replace('/[^a-z0-9\.]+/i', '.', $base);
        $base = trim($base, '.');
        if ($base === '') $base = 'vendor';

        // Sisakan ruang untuk suffix
        $base = substr($base, 0, 24);

        $candidate = $base;
        $i = 0;
        while (true) {
            $exists = $db->table('users')->where('username', $candidate)->countAllResults();
            if (! $exists) return $candidate;

            $i++;
            $candidate = substr($base, 0, 24) . sprintf('%02d', $i); // vendor..00, 01, ...
            if ($i > 99) { // fallback random
                $candidate = substr($base, 0, 20) . random_int(1000, 9999);
            }
        }
    }
}

if (! function_exists('assign_user_to_group')) {
    /**
     * Assign user ke grup (string) melalui pivot `auth_groups_users`.
     * Skema kamu tanpa tabel `auth_groups`, jadi langsung insert ke pivot.
     */
    function assign_user_to_group(int $userId, string $group): void
    {
        $db = db_connect();

        if (! $db->tableExists('auth_groups_users')) {
            throw new RuntimeException('Tabel auth_groups_users tidak ditemukan.');
        }

        // Hindari duplikasi
        $exists = $db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->where('group', $group)
            ->countAllResults();

        if ($exists) return;

        $db->table('auth_groups_users')->insert([
            'user_id'    => $userId,
            'group'      => $group,
            'created_at' => date('Y-m-d H:i:s'), // biasanya NOT NULL
        ]);
    }
}
