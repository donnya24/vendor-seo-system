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
    
    /**
     * Get reports with vendor information and target position
     */
    public function getWithVendor($vendorId = null, $position = null, $changeFilter = null)
    {
        $builder = $this->select('seo_reports.*, vendor_profiles.business_name as vendor_name, seo_keyword_targets.target_position')
                        ->join('vendor_profiles', 'vendor_profiles.id = seo_reports.vendor_id', 'left')
                        ->join('seo_keyword_targets', 'seo_keyword_targets.vendor_id = seo_reports.vendor_id AND seo_keyword_targets.keyword = seo_reports.keyword AND seo_keyword_targets.status = "completed"', 'left')
                        ->where('seo_reports.status', 'active');
        
        if ($vendorId) {
            $builder->where('seo_reports.vendor_id', $vendorId);
        }
        
        // Filter berdasarkan posisi
        if ($position) {
            if ($position === 'top3') {
                $builder->where('seo_reports.position <=', 3);
            } elseif ($position === 'top10') {
                $builder->where('seo_reports.position <=', 10)
                        ->where('seo_reports.position >', 3);
            } elseif ($position === 'top20') {
                $builder->where('seo_reports.position <=', 20)
                        ->where('seo_reports.position >', 10);
            } elseif ($position === 'below20') {
                $builder->where('seo_reports.position >', 20);
            }
        }
        
        // Filter berdasarkan perubahan
        if ($changeFilter) {
            if ($changeFilter === 'high_improvement') {
                $builder->where('seo_reports.change >=', 10)
                        ->where('seo_reports.trend', 'up');
            } elseif ($changeFilter === 'moderate_improvement') {
                $builder->where('seo_reports.change >=', 5)
                        ->where('seo_reports.change <', 10)
                        ->where('seo_reports.trend', 'up');
            } elseif ($changeFilter === 'low_improvement') {
                $builder->where('seo_reports.change >', 0)
                        ->where('seo_reports.change <', 5)
                        ->where('seo_reports.trend', 'up');
            } elseif ($changeFilter === 'no_change') {
                $builder->where('seo_reports.change', 0)
                        ->orWhere('seo_reports.change IS NULL');
            } elseif ($changeFilter === 'decline') {
                $builder->where('seo_reports.change <', 0)
                        ->where('seo_reports.trend', 'down');
            }
        }
        
        return $builder->orderBy('seo_reports.created_at', 'DESC')
                       ->findAll();
    }
    
    /**
     * Delete reports by vendor and keyword
     * @param int $vendorId
     * @param string $keyword
     * @return bool
     */
    public function deleteByVendorAndKeyword($vendorId, $keyword)
    {
        return $this->where('vendor_id', $vendorId)
                   ->where('keyword', $keyword)
                   ->delete() !== false;
    }
    
    /**
     * Get top keywords with high priority and target position 1,2,3
     * 
     * @param int $limit Number of keywords to return
     * @return array
     */
    public function getTopKeywords($limit = 3)
    {
        $db = \Config\Database::connect();
        
        // Query untuk mendapatkan keyword dengan prioritas High dan target position 1,2,3
        // yang sudah complete, diurutkan berdasarkan perubahan posisi (change)
        // dan waktu complete (created_at)
        $query = $db->table('seo_keyword_targets skt')
            ->select('skt.keyword, skt.current_position, skt.target_position, 
                     vp.business_name as vendor_name, sr.change, sr.created_at as report_date')
            ->join('vendor_profiles vp', 'vp.id = skt.vendor_id', 'left')
            ->join('seo_reports sr', 'sr.vendor_id = skt.vendor_id AND sr.keyword = skt.keyword', 'left')
            ->where('skt.priority', 'High')
            ->whereIn('skt.target_position', ['Top 1', 'Top 2', 'Top 3'])
            ->where('skt.status', 'complete')
            ->where('sr.status', 'active')
            ->orderBy('sr.change', 'DESC') // Prioritaskan yang perubahan posisinya paling signifikan
            ->orderBy('skt.created_at', 'ASC') // Jika perubahan sama, prioritaskan yang complete lebih dulu
            ->limit($limit);
            
        return $query->get()->getResultArray();
    }
    
    /**
     * Get top keywords by position change from seo_reports table
     * 
     * @param int $limit Number of keywords to return
     * @return array
     */
    public function getTopKeywordsByChange($limit = 3)
    {
        $db = \Config\Database::connect();
        
        // Query untuk mendapatkan keyword dengan perubahan posisi terbesar dari tabel seo_reports
        // Hanya mengambil yang statusnya 'active' dan memiliki nilai change (tidak null)
        $query = $db->table('seo_reports sr')
            ->select('sr.keyword, sr.position as current_position, sr.change, sr.trend,
                     vp.business_name as vendor_name')
            ->join('vendor_profiles vp', 'vp.id = sr.vendor_id', 'left')
            ->where('sr.status', 'active')
            ->where('sr.change IS NOT NULL')
            ->where('sr.change !=', 0) // Hanya yang memiliki perubahan
            ->orderBy('sr.change', 'DESC') // Urutkan berdasarkan perubahan terbesar
            ->orderBy('sr.created_at', 'DESC') // Jika perubahan sama, ambil yang terbaru
            ->limit($limit);
        
        return $query->get()->getResultArray();
    }
    
    /**
     * Get top keywords by position from seo_reports table
     * 
     * @param int $limit Number of keywords to return
     * @return array
     */
    public function getTopKeywordsByPosition($limit = 3)
    {
        $db = \Config\Database::connect();
        
        // Query untuk mendapatkan keyword dengan posisi terbaik dari tabel seo_reports
        // Hanya mengambil yang statusnya 'active'
        $query = $db->table('seo_reports sr')
            ->select('sr.keyword, sr.position as current_position, sr.change, sr.trend,
                     vp.business_name as vendor_name')
            ->join('vendor_profiles vp', 'vp.id = sr.vendor_id', 'left')
            ->where('sr.status', 'active')
            ->orderBy('sr.position', 'ASC') // Urutkan berdasarkan posisi terbaik (1, 2, 3, ...)
            ->orderBy('sr.created_at', 'DESC') // Jika posisi sama, ambil yang terbaru
            ->limit($limit);
        
        return $query->get()->getResultArray();
    }
}