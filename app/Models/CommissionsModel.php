<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsModel extends Model
{
    protected $table         = 'commissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'vendor_id','lead_id','amount','status','period',
        'payment_note','proof_url','verify_note','paid_at','created_at','updated_at'
    ];
}
