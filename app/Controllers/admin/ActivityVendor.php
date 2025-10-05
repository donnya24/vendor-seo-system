<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\VendorProfilesModel;

class ActivityVendor extends BaseController
{
    protected $activityLogs;
    protected $vendorProfiles;

    public function __construct()
    {
        $this->activityLogs   = new ActivityLogsModel();
        $this->vendorProfiles = new VendorProfilesModel();
    }

    public function index()
    {
        $request = service('request');
        $vendorId = $request->getGet('vendor_id'); // filter vendor_id dari query string

        // Ambil daftar semua vendor untuk dropdown
        $vendors = $this->vendorProfiles
            ->select('id, business_name')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        // Query log aktivitas
        $builder = $this->activityLogs
            ->select('activity_logs.*, vendor_profiles.business_name')
            ->join('vendor_profiles', 'vendor_profiles.id = activity_logs.vendor_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC');

        if (!empty($vendorId)) {
            $builder->where('activity_logs.vendor_id', $vendorId);
        }

        $logs = $builder->findAll();

        $data = [
            'title'     => 'Aktivitas Vendor',
            'logs'      => $logs,
            'vendors'   => $vendors,
            'vendor_id' => $vendorId,
        ];

        return view('admin/layouts/header', $data)
            . view('admin/layouts/sidebar', $data)
            . view('admin/activityvendor/index', $data)
            . view('admin/layouts/footer');
    }
}
