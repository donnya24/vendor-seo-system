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
        'user_id', 'action', 'entity_type', 'entity_id',
        'meta_json', 'ip_address',
        'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
