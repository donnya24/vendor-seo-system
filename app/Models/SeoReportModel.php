<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoReportModel extends Model
{
    protected $table = 'seo_reports';
    protected $primaryKey = 'id';
    protected $allowedFields = ['keyword', 'ranking_position', 'traffic', 'vendor_id', 'service_id', 'date_report'];
    protected $useTimestamps = true;
}
