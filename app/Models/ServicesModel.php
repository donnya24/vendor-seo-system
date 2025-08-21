<?php
namespace App\Models;

use CodeIgniter\Model;

class ServicesModel extends Model
{
    protected $table      = 'services';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','description','vendor_id','created_at','updated_at'];
    protected $useTimestamps = false;
}
