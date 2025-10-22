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

    public function withLatestReport()
    {
        return $this->select('seo_keyword_targets.*, 
                r.position AS last_position, 
                r.created_at AS last_checked,
                vp.business_name as vendor_name')
            ->join('vendor_profiles vp', 'vp.id = seo_keyword_targets.vendor_id', 'left')
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
    
    /**
     * Get top keywords by position (ascending) with status 'completed'
     * @param int $limit
     * @return array
     */
    public function getTopKeywords($limit = 3)
    {
        return $this->where('status', 'completed')
                    ->orderBy('current_position', 'ASC')
                    ->orderBy('updated_at', 'DESC') // if same position, take the latest
                    ->limit($limit)
                    ->find();
    }
}