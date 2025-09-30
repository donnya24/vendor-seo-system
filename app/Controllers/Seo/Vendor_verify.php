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
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vendor tidak ditemukan.']);
        }
        return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
    }

    $this->vendorModel->update($id, [
        'status'      => 'verified',
        'approved_at' => date('Y-m-d H:i:s'),
        'action_by'   => $user->id
    ]);

    $this->activityLogsModel->insert([
        'user_id'     => $user->id,
        'vendor_id'   => $id,
        'module'      => 'vendor',
        'action'      => 'approve',
        'status'      => 'success',
        'description' => 'Vendor disetujui oleh tim SEO',
        'created_at'  => date('Y-m-d H:i:s')
    ]);

    if ($this->request->isAJAX()) {
        return $this->response->setJSON(['success' => true, 'message' => 'Vendor berhasil disetujui.']);
    }

    return redirect()->back()->with('success', 'Vendor berhasil disetujui.');
}


}