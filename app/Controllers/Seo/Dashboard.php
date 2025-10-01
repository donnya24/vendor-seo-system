<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\LeadsModel;
use App\Models\CommissionsModel;
use App\Models\ActivityLogsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vendorId = $this->request->getGet('vendor_id') 
            ?? session()->get('vendor_id') 
            ?? 1;

        session()->set('vendor_id', $vendorId);

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

        $targets = (new SeoKeywordTargetsModel())
            ->withLatestReport()
            ->where('seo_keyword_targets.vendor_id', $vendorId)
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
        $leadStats = (new LeadsModel())->getTotalLeadsByVendor($vendorId);

        $paidCommission = (new CommissionsModel())
            ->select('COALESCE(SUM(amount),0) as total_amount, COUNT(*) as count')
            ->where('vendor_id', $vendorId)
            ->where('status', 'paid')
            ->first();

        $this->logActivity(
            $vendorId,
            'dashboard',
            'view',
            "Membuka dashboard"
        );

        return view('seo/dashboard', [
            'title'          => 'SEO Dashboard',
            'activeMenu'     => 'dashboard',
            'vendorId'       => $vendorId,
            'targets'        => $targets,
            'leadStats'      => $leadStats,
            'paidCommission' => $paidCommission,
            'start'          => $start,
            'end'            => $end,
        ]);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        (new ActivityLogsModel())->insert([
            'user_id'    => session()->get('user_id'),
            'vendor_id'  => $vendorId,
            'module'     => $module,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}