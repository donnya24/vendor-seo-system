<?php
namespace App\Models;

use CodeIgniter\Model;

class AreasModel extends Model
{
    protected $table      = 'areas';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name','created_at','updated_at'];
    protected $useTimestamps = false;
}
