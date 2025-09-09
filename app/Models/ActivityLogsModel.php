<?php
// app/Models/ActivityLogsModel.php
namespace App\Models;

use CodeIgniter\Model;

class ActivityLogsModel extends Model
{
    protected $table         = 'activity_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id',
        'vendor_id',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false; // karena created_at diisi manual saat insert
}
