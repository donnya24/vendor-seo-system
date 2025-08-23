<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorAreasModel extends Model
{
    protected $table         = 'vendor_areas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['vendor_id','area_id','created_at'];
}
