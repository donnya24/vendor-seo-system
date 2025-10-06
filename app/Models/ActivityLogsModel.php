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
        'user_id',
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

    protected $useTimestamps = false;

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
                admin_profiles.name AS admin_name,
                vendor_profiles.business_name AS vendor_name
            ')
            ->join('seo_profiles', 'seo_profiles.id = activity_logs.seo_id', 'left')
            ->join('admin_profiles', 'admin_profiles.id = activity_logs.admin_id', 'left')
            ->join('vendor_profiles', 'vendor_profiles.id = activity_logs.vendor_id', 'left')
            ->orderBy('activity_logs.id', 'DESC')
            ->findAll();
    }

    /**
     * Ambil log berdasarkan vendor tertentu
     */
    public function getByVendor($vendorId)
    {
        return $this->where('vendor_id', $vendorId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log berdasarkan admin tertentu
     */
    public function getByAdmin($adminId)
    {
        return $this->where('admin_id', $adminId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log berdasarkan SEO tertentu
     */
    public function getBySeo($seoId)
    {
        return $this->where('seo_id', $seoId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log berdasarkan user tertentu
     */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log aktivitas login
     */
    public function getLoginLogs()
    {
        return $this->where('action', 'login')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log aktivitas logout
     */
    public function getLogoutLogs()
    {
        return $this->where('action', 'logout')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Ambil log aktivitas register
     */
    public function getRegisterLogs()
    {
        return $this->where('action', 'register')
                    ->orderBy('id', 'DESC')
                    ->findAll();
    }

    /**
     * Simpan log aktivitas baru
     */
    public function addLog($data)
    {
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return $this->insert($data);
    }
}