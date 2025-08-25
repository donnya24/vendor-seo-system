<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\AreasModel;
use App\Models\VendorAreasModel;
use App\Models\VendorProfilesModel;

class Areas extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())->where('user_id', (int)service('auth')->user()->id)->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        $all = (new AreasModel())->orderBy('name','ASC')->findAll();
        $attached = (new VendorAreasModel())->where('vendor_id',$vid)->findAll();
        $attachedIds = array_column($attached,'area_id');
        return view('vendoruser/areas/index', ['page'=>'Area','areas'=>$all,'attachedIds'=>$attachedIds]);
    }

    public function attach()
    {
        $vid = $this->vendorId();
        $aid = (int)$this->request->getPost('area_id');
        $va  = new VendorAreasModel();
        if (! $va->where(['vendor_id'=>$vid,'area_id'=>$aid])->first()) {
            $va->insert(['vendor_id'=>$vid,'area_id'=>$aid]);
        }
        return redirect()->back()->with('success','Area ditambahkan.');
    }

    public function detach($areaId)
    {
        $vid = $this->vendorId();
        (new VendorAreasModel())->where(['vendor_id'=>$vid,'area_id'=>(int)$areaId])->delete();
        return redirect()->back()->with('success','Area dihapus.');
    }
}
