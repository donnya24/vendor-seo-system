<?php

namespace Config;

use CodeIgniter\Events\Events;
use CodeIgniter\Exceptions\FrameworkException;
use CodeIgniter\HotReloader\HotReloader;

Events::on('login', static function ($user) {
    // redirect sesuai role user
    if ($user->can('admin.access')) {
        return redirect()->to('/admin/dashboard');
    } elseif ($user->can('seo_team.access')) {
        return redirect()->to('/seo_team/dashboard');
    } elseif ($user->can('vendor.access')) {
        return redirect()->to('/vendor/dashboard');
    }

    // default kalau role tidak cocok
    return redirect()->to('/dashboard');
});

Events::on('logout', static function () {
    // setelah logout kembali ke landing page
    return redirect()->to('/');
});


Events::on('pre_system', static function (): void {
    if (ENVIRONMENT !== 'testing') {
        if (ini_get('zlib.output_compression')) {
            throw FrameworkException::forEnabledZlibOutputCompression();
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }

        ob_start(static fn ($buffer) => $buffer);
    }

    /*
     * --------------------------------------------------------------------
     * Debug Toolbar Listeners.
     * --------------------------------------------------------------------
     * If you delete, they will no longer be collected.
     */
    if (CI_DEBUG && ! is_cli()) {
        Events::on('DBQuery', 'CodeIgniter\Debug\Toolbar\Collectors\Database::collect');
        service('toolbar')->respond();
        // Hot Reload route - for framework use on the hot reloader.
        if (ENVIRONMENT === 'development') {
            service('routes')->get('__hot-reload', static function (): void {
                (new HotReloader())->run();
            });
        }
    }
});
