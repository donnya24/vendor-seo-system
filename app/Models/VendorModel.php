<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table = 'vendor_profiles';
    protected $primaryKey = 'id';
    protected $allowedFields = ['business_name', 'owner_name', 'phone', 'status'];
    protected $useTimestamps = true;
}
