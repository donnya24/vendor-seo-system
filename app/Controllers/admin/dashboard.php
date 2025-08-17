<?php

namespace App\Controllers;

use App\Models\VendorModel;
use App\Models\SeoReportModel;
use App\Models\LeadModel;
use App\Models\CommissionModel;

class Dashboard extends BaseController
{
    public function index()
    {
        // Load models
        $vendorModel = new VendorModel();
        $seoReportModel = new SeoReportModel();
        $leadModel = new LeadModel();
        $commissionModel = new CommissionModel();

        // Fetch data from the database
        $vendors = $vendorModel->findAll();
        $seoReports = $seoReportModel->findAll();
        $leads = $leadModel->findAll();
        $commissions = $commissionModel->findAll();

        return view('admin/Dashboard_admin', [
            'vendors' => $vendors,
            'seoReports' => $seoReports,
            'leads' => $leads,
            'commissions' => $commissions,
        ]);
    }
}
