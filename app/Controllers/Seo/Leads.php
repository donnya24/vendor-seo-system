<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\ActivityLogsModel;

class Leads extends BaseController
{
    protected $leadModel;

    public function __construct()
    {
        $this->leadModel = new LeadsModel();
    }

    public function index()
    {
        $vendorId = (int)($this->request->getGet('vendor_id')
            ?? session()->get('vendor_id')
            ?? 1);

        // ambil filter start & end
        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

        $leads = $this->leadModel->getLeadsByVendor($vendorId, $start, $end);

        // catat activity log
        $this->logActivity(
            $vendorId,
            'leads',
            'view',
            "User melihat daftar leads periode {$start} s/d {$end}"
        );

        return view('seo/leads/index', [
            'title'      => 'Pantau Leads',
            'activeMenu' => 'leads',
            'leads'      => $leads,
            'pager'      => $this->leadModel->pager,
            'vendorId'   => $vendorId,
            'start'      => $start,
            'end'        => $end
        ]);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        $user   = service('auth')->user();
        $userId = $user ? $user->id : null;

        (new ActivityLogsModel())->insert([
            'user_id'     => $userId,
            'vendor_id'   => $vendorId ?? 0,
            'module'      => $module,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => (string) $this->request->getUserAgent(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }

}
