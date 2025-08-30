<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorProductsModel extends Model
{
    protected $table         = 'vendor_products';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'vendor_id','product_name','description','price','created_at','updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
