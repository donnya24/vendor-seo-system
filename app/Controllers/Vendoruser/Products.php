<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\VendorProductsModel;

class Products extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())->where('user_id', (int)service('auth')->user()->id)->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        $items = (new VendorProductsModel())->where('vendor_id',$vid)->orderBy('id','DESC')->findAll();
        return view('vendoruser/products/index', ['page'=>'Produk','items'=>$items]);
    }

    public function create() { return view('vendoruser/products/create', ['page'=>'Produk']); }

    public function store()
    {
        (new VendorProductsModel())->insert([
            'vendor_id'    => $this->vendorId(),
            'product_name' => $this->request->getPost('product_name'),
            'description'  => $this->request->getPost('description'),
            'price'        => $this->request->getPost('price') ?: null,
        ]);
        return redirect()->to(site_url('vendor/products'))->with('success','Produk ditambahkan.');
    }

    public function edit($id)
    {
        $vid   = $this->vendorId();
        $item  = (new VendorProductsModel())->where(['id'=>$id,'vendor_id'=>$vid])->first();
        if (!$item) return redirect()->to(site_url('vendor/products'));
        return view('vendoruser/products/edit', ['page'=>'Produk','item'=>$item]);
    }

    public function update($id)
    {
        $vid = $this->vendorId();
        (new VendorProductsModel())->where(['id'=>$id,'vendor_id'=>$vid])->set([
            'product_name' => $this->request->getPost('product_name'),
            'description'  => $this->request->getPost('description'),
            'price'        => $this->request->getPost('price') ?: null,
            'updated_at'   => date('Y-m-d H:i:s')
        ])->update();
        return redirect()->to(site_url('vendor/products'))->with('success','Produk diperbarui.');
    }

    public function delete($id)
    {
        $vid = $this->vendorId();
        (new VendorProductsModel())->where(['id'=>$id,'vendor_id'=>$vid])->delete();
        return redirect()->to(site_url('vendor/products'))->with('success','Produk dihapus.');
    }
}
