<?php
namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Vendor_verify extends BaseController
{
    protected $vendorModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        $vendors = $this->vendorModel->findAll();
        return view('seo/vendor_verify/index', [
            'vendors'    => $vendors,
            'title'      => 'Daftar Vendor',
            'activeMenu' => 'vendor'
        ]);
    }

    public function approve($id)
{
    $user   = service('auth')->user();
    $vendor = $this->vendorModel->find($id);

    if (!$vendor) {
        return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
    }

    // Update status dan info approval
    $this->vendorModel->update($id, [
        'status'      => 'verified',
        'approved_at' => date('Y-m-d H:i:s'),
        'action_by'   => $user->id
    ]);

    // Catat log aktivitas
    $this->activityLogsModel->insert([
        'user_id'     => $user->id,
        'vendor_id'   => $id,
        'module'      => 'vendor',
        'action'      => 'approve',
        'status'      => 'success',
        'description' => 'Vendor disetujui oleh tim SEO',
        'created_at'  => date('Y-m-d H:i:s')
    ]);

    return redirect()->back()->with('success', 'Vendor berhasil disetujui.');
}

}