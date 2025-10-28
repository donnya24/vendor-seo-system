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
    
    /**
     * Create a report when a target is marked as completed
     * @param int $targetId
     * @return bool
     */
    public function createReportFromTarget($targetId)
    {
        // PERBAIKAN: Gunakan builder untuk memastikan query dieksekusi dengan benar
        $target = $this->where('id', $targetId)
                       ->where('status', 'completed')
                       ->first();
        
        if (!$target) {
            return false;
        }
        
        $reportsModel = new \App\Models\SeoReportsModel();
        
        // PERBAIKAN: Hitung perubahan dengan logika yang benar
        $change = null;
        $trend = 'stable';
        
        if (!empty($target['current_position']) && !empty($target['target_position'])) {
            // PERBAIKAN: Perubahan dihitung sebagai current_position - target_position
            // Ini menghitung seberapa banyak peringkat yang ditempuh untuk mencapai target
            // Dalam SEO, angka yang lebih kecil lebih baik, jadi:
            // current_position (10) - target_position (1) = 9 (peningkatan)
            $change = $target['current_position'] - $target['target_position'];
            
            // PERBAIKAN: Trend dihitung berdasarkan arah perubahan
            if ($change > 0) {
                $trend = 'up';    // Posisi membaik (angka mengecil)
            } elseif ($change < 0) {
                $trend = 'down';  // Posisi memburuk (angka membesar)
            }
        }
        
        $reportData = [
            'vendor_id' => $target['vendor_id'],
            'keyword' => $target['keyword'],
            'project' => $target['project_name'],
            'position' => $target['current_position'],
            'change' => $change,
            'trend' => $trend,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $reportsModel->insert($reportData) !== false;
    }
    
    /**
     * Delete reports related to a target
     * @param int $targetId
     * @return bool
     */
    public function deleteRelatedReports($targetId)
    {
        // PERBAIKAN: Gunakan first() untuk memastikan kita mendapatkan array
        $target = $this->where('id', $targetId)->first();
        
        if (!$target) {
            return false;
        }
        
        $reportsModel = new \App\Models\SeoReportsModel();
        
        // Delete all reports for this vendor and keyword combination
        return $reportsModel->where('vendor_id', $target['vendor_id'])
                           ->where('keyword', $target['keyword'])
                           ->delete() !== false;
    }
    
    /**
     * Override delete method to also delete related reports
     * @param int $id
     * @return bool
     */
    public function delete($id = null, bool $purge = false)
    {
        // First delete related reports
        $this->deleteRelatedReports($id);
        
        // Then delete the target
        return parent::delete($id, $purge);
    }
}