<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table         = 'leads';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'customer_name','vendor_id','service_id','area_id',
        'status','source','wa_number_used','created_at','updated_at'
    ];
}
