<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\ServicesModel;
use App\Models\VendorProfilesModel;

class Services extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())
                ->where('user_id', (int)service('auth')->user()->id)
                ->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid   = $this->vendorId();
        $items = (new ServicesModel())
                    ->where('vendor_id', $vid)
                    ->orderBy('id','DESC')
                    ->findAll();

        return view('vendoruser/services/index', [
            'page'  => 'Layanan',
            'items' => $items
        ]);
    }

    public function create()
    { 
        return view('vendoruser/services/create', ['page'=>'Tambah Layanan']); 
    }

    public function store()
    {
        $vid = $this->vendorId();

        (new ServicesModel())->insert([
            'vendor_id'   => $vid,
            'name'        => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'status'      => 'pending',
            'created_at'  => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ]);
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Layanan berhasil ditambahkan'
            ]);
        }
        
        return redirect()
            ->to(site_url('vendoruser/services'))
            ->with('success','Layanan ditambahkan (menunggu verifikasi).');
    }

    public function edit($id)
    {
        $vid  = $this->vendorId();
        $item = (new ServicesModel())->where(['id'=>$id,'vendor_id'=>$vid])->first();
        
        if (!$item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Layanan tidak ditemukan'
                ]);
            }
            return redirect()->to(site_url('vendoruser/services'));
        }
        
        return view('vendoruser/services/edit', [
            'page'=>'Edit Layanan',
            'item'=>$item
        ]);
    }

    public function update($id)
    {
        $vid = $this->vendorId();

        (new ServicesModel())
            ->where(['id'=>$id,'vendor_id'=>$vid])
            ->set([
                'name'        => $this->request->getPost('name'),
                'description' => $this->request->getPost('description'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ])->update();
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Layanan berhasil diperbarui'
            ]);
        }
        
        return redirect()
            ->to(site_url('vendoruser/services'))
            ->with('success','Layanan diperbarui.');
    }

    public function delete($id)
    {
        $vid = $this->vendorId();

        (new ServicesModel())
            ->where(['id'=>$id,'vendor_id'=>$vid])
            ->delete();
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Layanan berhasil dihapus'
            ]);
        }
        
        return redirect()
            ->to(site_url('vendoruser/services'))
            ->with('success','Layanan dihapus.');
    }
}
