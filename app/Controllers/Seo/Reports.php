<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\ActivityLogsModel;

class Reports extends BaseController
{
    public function index()
    {
        $vendorId = $this->request->getGet('vendor_id') 
            ?? session()->get('vendor_id') 
            ?? 1;

        // Ambil target yg sudah completed
        $targets = (new SeoKeywordTargetsModel())
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left')
            ->where('seo_keyword_targets.vendor_id', $vendorId)
            ->where('seo_keyword_targets.status', 'completed')
            ->orderBy('seo_keyword_targets.updated_at', 'DESC')
            ->findAll();

        // Hitung perubahan otomatis
        foreach ($targets as &$t) {
            $cur = (int)($t['current_position'] ?? 0);
            $tar = (int)($t['target_position'] ?? 0);

            if ($cur && $tar) {
                $t['change'] = $cur - $tar; // current - target
            } else {
                $t['change'] = null;
            }
        }

        return view('seo/reports/index', [
            'title'      => 'Laporan SEO',
            'activeMenu' => 'reports',
            'reports'    => $targets,
            'vendorId'   => $vendorId,
        ]);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        (new ActivityLogsModel())->insert([
            'user_id'    => session()->get('user_id'),
            'vendor_id'  => $vendorId ?? session()->get('vendor_id') ?? 1,
            'module'     => $module,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
