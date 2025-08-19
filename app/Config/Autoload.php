<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        // biarkan default lainnya (Config & CodeIgniter) seperti biasa
    ];

    public $classmap = [];

    public $files = [];

    // Autoload helpers (cukup sekali)
    public $helpers = ['auth', 'setting']; // 'setting' opsional, tapi berguna untuk Settings

}
