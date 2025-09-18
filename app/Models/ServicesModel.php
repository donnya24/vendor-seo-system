<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicesModel extends Model
{
    protected $table      = 'services';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Kolom sesuai tabel `services` di database kamu
    protected $allowedFields = [
        'service_name',
        'service_description',
        'product_name',
        'price',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false; // kalau tabel punya kolom created_at, updated_at otomatis, bisa diganti true
    protected $dateFormat    = 'datetime';
}
