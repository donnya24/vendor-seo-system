<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorServicesModel extends Model
{
    protected $table         = 'vendor_services';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = ['vendor_id','service_id','created_at'];
}
