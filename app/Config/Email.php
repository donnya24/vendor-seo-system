<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Email extends BaseConfig
{
    public string $fromEmail  = 'donnyk300@gmail.com';
    public string $fromName   = 'Vendor Partnership & SEO Performance';
    public string $recipients = '';

    public string $protocol   = 'smtp';
    public string $SMTPHost   = 'smtp.gmail.com';
    public string $SMTPUser   = 'donnyk300@gmail.com';
    public string $SMTPPass   = '11111111'; // jangan pakai password utama Gmail
    public int    $SMTPPort   = 587;
    public string $SMTPCrypto = 'tls';
    
    public string $mailType   = 'html';
    public string $charset    = 'utf-8';
    public bool   $validate   = true;

    public string $CRLF       = "\r\n";
    public string $newline    = "\r\n";
}
