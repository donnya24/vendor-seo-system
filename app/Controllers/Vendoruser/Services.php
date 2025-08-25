<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\ServicesModel;
use App\Models\VendorProfilesModel;

class Services extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())->where('user_id', (int)service('auth')->user()->id)->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        $items = (new ServicesModel())->where('vendor_id',$vid)->orderBy('id','DESC')->findAll();
        return view('vendoruser/services/index', ['page'=>'Layanan','items'=>$items]);
    }

    public function create(){ return view('vendoruser/services/create', ['page'=>'Layanan']); }

    public function store()
    {
        (new ServicesModel())->insert([
            'vendor_id'    => $this->vendorId(),
            'name'         => $this->request->getPost('name'),
            'service_type' => $this->request->getPost('service_type') ?: 'vendor_service',
            'description'  => $this->request->getPost('description'),
            'status'       => 'pending',
        ]);
        return redirect()->to(site_url('vendor/services'))->with('success','Layanan ditambahkan (menunggu verifikasi).');
    }

    public function edit($id)
    {
        $vid = $this->vendorId();
        $item= (new ServicesModel())->where(['id'=>$id,'vendor_id'=>$vid])->first();
        if (!$item) return redirect()->to(site_url('vendor/services'));
        return view('vendoruser/services/edit', ['page'=>'Layanan','item'=>$item]);
    }

    public function update($id)
    {
        $vid = $this->vendorId();
        (new ServicesModel())->where(['id'=>$id,'vendor_id'=>$vid])->set([
            'name'         => $this->request->getPost('name'),
            'service_type' => $this->request->getPost('service_type') ?: 'vendor_service',
            'description'  => $this->request->getPost('description'),
            'updated_at'   => date('Y-m-d H:i:s')
        ])->update();
        return redirect()->to(site_url('vendor/services'))->with('success','Layanan diperbarui.');
    }

    public function delete($id)
    {
        $vid = $this->vendorId();
        (new ServicesModel())->where(['id'=>$id,'vendor_id'=>$vid])->delete();
        return redirect()->to(site_url('vendor/services'))->with('success','Layanan dihapus.');
    }
}
