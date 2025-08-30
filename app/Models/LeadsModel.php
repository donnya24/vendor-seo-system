<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table            = 'leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;

    // Data dikembalikan dalam bentuk array
    protected $returnType       = 'array';

    // Kolom yang boleh diisi
    protected $allowedFields    = [
        'vendor_id',
        'tanggal',
        'jumlah_leads_masuk',
        'jumlah_leads_diproses',
        'jumlah_leads_ditolak',
        'jumlah_leads_closing',
        'service_id',
        'reported_by_vendor',
        'assigned_at',
        'updated_at',
    ];

    // Kalau pakai created_at/updated_at otomatis
    protected $useTimestamps = false; // karena created_at tidak ada di tabel
}
