<?php
namespace App\Models;

use CodeIgniter\Model;

class VendorServicesModel extends Model
{
    protected $table      = 'vendor_services';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id','service_id','approval_status','commission_rate','active_from','active_to','created_at','updated_at'];
    protected $useTimestamps = false;
}
