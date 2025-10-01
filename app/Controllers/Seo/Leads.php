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

        // Ambil tanggal dari request, jika kosong maka jadikan null
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        // Jika salah satu tanggal kosong, tampilkan semua data
        if (empty($start) || empty($end)) {
            $start = null;
            $end = null;
        }

        $leads = $this->leadModel->getLeadsWithVendor($vendorId, $start, $end);

        $this->logActivity(
            $vendorId,
            'leads',
            'view',
            "User melihat daftar leads" . ($start ? " periode {$start} s/d {$end}" : " seluruhnya")
        );

        return view('seo/leads/index', [
            'title'      => 'Pantau Leads',
            'activeMenu' => 'leads',
            'leads'      => $leads,
            'pager'      => $this->leadModel->pager,
            'vendorId'   => $vendorId,
            'start'      => $start ?? date('Y-m-01'), // Untuk nilai default di form
            'end'        => $end ?? date('Y-m-t')     // Untuk nilai default di form
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