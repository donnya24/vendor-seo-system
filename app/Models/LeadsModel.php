<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table      = 'leads';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'vendor_id',
        'tanggal',
        'jumlah_leads_masuk',
        'jumlah_leads_closing',
        'reported_by_vendor',
        'assigned_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
}
