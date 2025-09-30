<?php

namespace Config;

use CodeIgniter\Config\AutoloadConfig;

class Autoload extends AutoloadConfig
{
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'App\Helpers'  => APPPATH . 'Helpers',
    ];

    public $classmap = [];

    public $files = [];

    // Autoload helpers (cukup sekali)
    public $helpers = ['auth', 'setting', 'activity', 'url', 'form', 'text', 'seo'];
}
