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

        // Simpan vendor_id di session supaya konsisten
        session()->set('vendor_id', $vendorId);

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

        // Ambil target dengan laporan terakhir
        $targets = (new SeoKeywordTargetsModel())
            ->withLatestReport()
            ->where('seo_keyword_targets.vendor_id', $vendorId)
            ->findAll();

        // Statistik leads
        $leadStats = (new LeadsModel())->getDashboardStats(
            $vendorId,
            "{$start} 00:00:00",
            "{$end} 23:59:59"
        );

        // Ambil total komisi untuk periode filter
        $commission = (new CommissionsModel())
            ->select('COALESCE(SUM(amount),0) as total_amount, MAX(status) as status', false)
            ->where('vendor_id', $vendorId)
            ->where('period_start >=', $start)
            ->where('period_end <=', $end)
            ->first();

        // Catat log activity
        $this->logActivity($vendorId, 'dashboard', 'view', "Membuka dashboard periode {$start} - {$end}");

        return view('seo/dashboard', [
            'title'       => 'SEO Dashboard',
            'activeMenu'  => 'dashboard',
            'vendorId'    => $vendorId,
            'targets'     => $targets,
            'leadStats'   => $leadStats ?? ['total' => 0, 'closed' => 0],
            'commission'  => $commission,
            'start'       => $start,
            'end'         => $end,
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
