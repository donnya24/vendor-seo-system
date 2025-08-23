<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\ServicesModel;
use App\Models\AreasModel;
use App\Models\VendorServicesModel;
use App\Models\VendorAreasModel;

class Vendors extends BaseController
{
    public function index()
    {
        $vendors = (new VendorProfilesModel())->orderBy('id','DESC')->findAll();
        return view('admin/vendors/index', ['page'=>'Vendors','vendors'=>$vendors]);
    }

    public function show($vendorId)
    {
        $vp = new VendorProfilesModel();
        $vendor = $vp->find($vendorId);

        $services = (new ServicesModel())->findAll();
        $areas    = (new AreasModel())->findAll();
        $attachedServices = (new VendorServicesModel())->where('vendor_id',$vendorId)->findAll();
        $attachedAreas    = (new VendorAreasModel())->where('vendor_id',$vendorId)->findAll();

        return view('admin/vendors/show', [
            'page' => 'Vendor Detail',
            'vendor' => $vendor,
            'services' => $services,
            'areas' => $areas,
            'attachedServiceIds' => array_column($attachedServices, 'service_id'),
            'attachedAreaIds'    => array_column($attachedAreas, 'area_id'),
        ]);
    }

    public function verify($vendorId)
    {
        (new VendorProfilesModel())->update($vendorId, [
            'is_verified' => 1,
            'status'      => 'verified',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success','Vendor verified.');
    }

    public function unverify($vendorId)
    {
        (new VendorProfilesModel())->update($vendorId, [
            'is_verified' => 0,
            'status'      => 'rejected',
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('success','Vendor set to rejected.');
    }

    public function setCommission($vendorId)
    {
        $rate = (float) ($this->request->getPost('commission_rate') ?? 0);
        (new VendorProfilesModel())->update($vendorId, [
            'commission_rate' => $rate,
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('success','Commission rate updated.');
    }
}
