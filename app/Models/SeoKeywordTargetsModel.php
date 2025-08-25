<?php
// app/Models/SeoKeywordTargetsModel.php
namespace App\Models;

use CodeIgniter\Model;

class SeoKeywordTargetsModel extends Model
{
    protected $table         = 'seo_keyword_targets';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'vendor_id', 'keyword', 'target_url', 'status', 'notes',
        'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
