<?php

namespace App\Models;

use CodeIgniter\Model;

class ActivityLogsModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'vendor_id',
        'seo_id',
        'admin_id',
        'module',
        'action',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false; // karena kita isi created_at manual di controller

    // ==============================
    // 🔗 RELATION HELPERS
    // ==============================

    /**
     * Ambil semua log beserta nama SEO & Admin (join)
     */
    public function getAllWithRelations()
    {
        return $this->select('
                activity_logs.*,
                seo_profiles.name AS seo_name,
                admin_profiles.name AS admin_name
            ')
            ->join('seo_profiles', 'seo_profiles.id = activity_logs.seo_id', 'left')
            ->join('admin_profiles', 'admin_profiles.id = activity_logs.admin_id', 'left')
            ->orderBy('activity_logs.id', 'DESC')
            ->findAll();
    }

    /**
     * Ambil log berdasarkan vendor tertentu
     */
    public function getByVendor($vendorId)
    {
        return $this->select('
                activity_logs.*,
                seo_profiles.name AS seo_name,
                admin_profiles.name AS admin_name
            ')
            ->join('seo_profiles', 'seo_profiles.id = activity_logs.seo_id', 'left')
            ->join('admin_profiles', 'admin_profiles.id = activity_logs.admin_id', 'left')
            ->where('activity_logs.vendor_id', $vendorId)
            ->orderBy('activity_logs.id', 'DESC')
            ->findAll();
    }

    /**
     * Simpan log aktivitas baru
     */
    public function addLog($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }
}
