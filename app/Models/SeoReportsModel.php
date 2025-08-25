<?php
// app/Models/SeoReportsModel.php
namespace App\Models;

use CodeIgniter\Model;

class SeoReportsModel extends Model
{
    protected $table         = 'seo_reports';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'vendor_id', 'report_date', 'summary', 'notes', 'metrics_json',
        'created_at', 'updated_at',
    ];

    protected $useTimestamps = true;           // otomatis isi created_at & updated_at
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
