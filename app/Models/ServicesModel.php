<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicesModel extends Model
{
    protected $table         = 'services';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'vendor_id',
        'name',
        'description',
        'status',
        'created_at',
        'updated_at'
    ];
}
