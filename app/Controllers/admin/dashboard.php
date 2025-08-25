<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\CommissionsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vendors     = (new VendorProfilesModel())->countAllResults();
        $todayLeads  = (new LeadsModel())->where('DATE(created_at)', date('Y-m-d'))->countAllResults();
        $monthlyDeals= (new LeadsModel())->where('status', 'closed')->where('MONTH(updated_at)', date('m'))->countAllResults();
        $topKeywords = 0; // placeholder

        return view('admin/dashboard', [
            'page'  => 'Dashboard',
            'stats' => [
                'totalVendors' => $vendors,
                'todayLeads'   => $todayLeads,
                'monthlyDeals' => $monthlyDeals,
                'topKeywords'  => $topKeywords,
            ]
        ]);
    }

    public function stats()
    {
        return $this->response->setJSON([
            'totalVendors' => (new VendorProfilesModel())->countAllResults(),
            'todayLeads'   => (new LeadsModel())->where('DATE(created_at)', date('Y-m-d'))->countAllResults(),
            'monthlyDeals' => (new LeadsModel())->where('status', 'closed')->where('MONTH(updated_at)', date('m'))->countAllResults(),
            'topKeywords'  => 0,
        ]);
    }
}