<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoKeywordTargetsModel extends Model
{
    protected $table            = 'seo_keyword_targets';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    // Gabungan allowedFields dari kedua versi
    protected $allowedFields    = [
        'vendor_id',
        'project_name',       // dari versi pertama
        'keyword',
        'target_url',         // dari versi kedua
        'current_position',   // dari versi pertama
        'target_position',    // dari versi pertama
        'deadline',           // dari versi pertama
        'status',
        'priority',           // dari versi pertama
        'notes',
    ];

    /**
     * Ambil keyword target dengan report terbaru (ranking terakhir).
     * Join ke tabel seo_reports berdasarkan keyword & vendor_id.
     */
    public function withLatestReport()
    {
        return $this->select('seo_keyword_targets.*, r.position AS last_position, r.change AS last_change, r.trend AS last_trend, r.created_at AS last_checked')
            ->join(
                '(SELECT vendor_id, keyword, MAX(created_at) as max_created
                  FROM seo_reports GROUP BY vendor_id, keyword) lr',
                'lr.vendor_id = seo_keyword_targets.vendor_id AND lr.keyword = seo_keyword_targets.keyword',
                'left'
            )
            ->join(
                'seo_reports r',
                'r.vendor_id = lr.vendor_id AND r.keyword = lr.keyword AND r.created_at = lr.max_created',
                'left'
            );
    }
}
