<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoReportsModel extends Model
{
    protected $table            = 'seo_reports';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Gabungan allowedFields dari kedua versi
    protected $allowedFields    = [
        'vendor_id',
        'keyword',          // dari versi pertama
        'project',          // dari versi pertama
        'position',         // dari versi pertama
        'change',           // dari versi pertama
        'trend',            // dari versi pertama
        'volume',           // dari versi pertama
        'status',           // dari versi pertama
        'report_date',      // dari versi kedua
        'summary',          // dari versi kedua
        'notes',            // dari versi kedua
        'metrics_json',     // dari versi kedua
    ];

    /**
     * Ambil laporan terbaru berdasarkan keyword untuk vendor tertentu.
     */
    public function latestByKeyword(int $vendorId, string $keyword)
    {
        return $this->where([
                    'vendor_id' => $vendorId,
                    'keyword'   => $keyword,
                    'status'    => 'active'
                ])
                ->orderBy('created_at', 'DESC')
                ->first();
    }
}
