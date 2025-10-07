<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\LeadsModel;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;

class Dashboard extends BaseController
{
    protected $targetModel;
    protected $leadModel;
    protected $commissionModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->targetModel = new SeoKeywordTargetsModel();
        $this->leadModel = new LeadsModel();
        $this->commissionModel = new CommissionsModel();
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;

        // Set session vendor_id jika dipilih
        if (!empty($vendorId)) {
            session()->set('vendor_id', $vendorId);
        }

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        // Ambil 10 target terbaru (semua vendor atau vendor tertentu)
        $targetsBuilder = $this->targetModel
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left')
            ->withLatestReport();

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $targetsBuilder->where('seo_keyword_targets.vendor_id', $vendorId);
        }

        $targets = $targetsBuilder->orderBy('seo_keyword_targets.updated_at', 'DESC')
            ->orderBy('seo_keyword_targets.created_at', 'DESC')
            ->limit(10)
            ->findAll();

        foreach ($targets as &$t) {
            $current = (int)($t['current_position'] ?? 0);
            $target  = (int)($t['target_position'] ?? 0);
            $status  = $t['status'] ?? 'pending';

            $t['last_change'] = ($status === 'completed' && $current && $target)
                ? $current - $target
                : null;
        }

        // Menggunakan method baru untuk mendapatkan total keseluruhan leads
        $leadStats = $this->leadModel->getTotalLeadsByVendor($vendorId);

        // Hitung komisi paid
        $commissionBuilder = $this->commissionModel
            ->select('COALESCE(SUM(amount),0) as total_amount, COUNT(*) as count');

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $commissionBuilder->where('vendor_id', $vendorId);
        }

        $paidCommission = $commissionBuilder->where('status', 'paid')
            ->first();

        // Hitung statistik tambahan
        $totalTargets = $this->getTotalTargets($vendorId);
        $completedTargets = $this->getCompletedTargets($vendorId);
        $highPriorityTargets = $this->getHighPriorityTargets($vendorId);

        // Log aktivitas view dashboard
        $logDescription = "Membuka dashboard SEO";
        $extraData = ['module' => 'dashboard'];
        
        if (!empty($vendorId)) {
            $vendorName = $this->getVendorName($vendorId, $vendors);
            $logDescription .= " untuk vendor {$vendorName}";
            $extraData['vendor_id'] = $vendorId;
        } else {
            $logDescription .= " semua vendor";
        }

        log_activity_auto('view', $logDescription, $extraData);

        return view('seo/dashboard', [
            'title'              => 'SEO Dashboard',
            'activeMenu'         => 'dashboard',
            'vendorId'           => $vendorId,
            'vendors'            => $vendors,
            'targets'            => $targets,
            'leadStats'          => $leadStats,
            'paidCommission'     => $paidCommission,
            'totalTargets'       => $totalTargets,
            'completedTargets'   => $completedTargets,
            'highPriorityTargets'=> $highPriorityTargets,
            'start'              => $start,
            'end'                => $end,
        ]);
    }

    /**
     * Helper untuk mendapatkan total targets
     */
    private function getTotalTargets(?int $vendorId = null): int
    {
        $builder = $this->targetModel;
        if (!empty($vendorId)) {
            $builder->where('vendor_id', $vendorId);
        }
        return $builder->countAllResults();
    }

    /**
     * Helper untuk mendapatkan completed targets
     */
    private function getCompletedTargets(?int $vendorId = null): int
    {
        $builder = $this->targetModel->where('status', 'completed');
        if (!empty($vendorId)) {
            $builder->where('vendor_id', $vendorId);
        }
        return $builder->countAllResults();
    }

    /**
     * Helper untuk mendapatkan high priority targets
     */
    private function getHighPriorityTargets(?int $vendorId = null): int
    {
        $builder = $this->targetModel->where('priority', 'high');
        if (!empty($vendorId)) {
            $builder->where('vendor_id', $vendorId);
        }
        return $builder->countAllResults();
    }

    /**
     * Helper untuk mendapatkan nama vendor
     */
    private function getVendorName(int $vendorId, array $vendors): string
    {
        foreach ($vendors as $vendor) {
            if ($vendor['id'] == $vendorId) {
                return $vendor['business_name'];
            }
        }
        return 'Unknown Vendor';
    }
}