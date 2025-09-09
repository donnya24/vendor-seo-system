<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsModel extends Model
{
    protected $table         = 'commissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'vendor_id',
        'period_start',
        'period_end',
        'earning',      
        'amount',
        'status',
        'proof',
        'paid_at',
        'created_at',
        'updated_at',
    ];
}
