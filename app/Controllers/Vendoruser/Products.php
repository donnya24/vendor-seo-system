<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\VendorProductsModel;

class Products extends BaseController
{
    private function vendorId(): int
    {
        $uid = (int)(service('auth')->user()->id ?? 0);
        if (!$uid) return 0;

        $vp = (new VendorProfilesModel())->where('user_id', $uid)->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        if (!$vid) {
            return redirect()->to(site_url('vendoruser/profile'))
                ->with('errors', ['Profil vendor tidak ditemukan.']);
        }

        $items = (new VendorProductsModel())
            ->where('vendor_id', $vid)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('vendoruser/products/index', [
            'page'  => 'Produk',
            'items' => $items
        ]);
    }

    public function create()
    {
        return view('vendoruser/products/create');
    }

    public function store()
    {
        $vid = $this->vendorId();
        if (!$vid) {
            return $this->response->setJSON(['status'=>'error','message'=>'Profil vendor tidak ditemukan']);
        }

        $rules = [
            'product_name' => 'required|min_length[3]',
            'price'        => 'permit_empty|decimal',
            'attachment'   => 'permit_empty|uploaded[attachment]|max_size[attachment,10240]|ext_in[attachment,pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx]'
        ];

        if (!$this->validate($rules)) {
            $errors = implode("\n", $this->validator->getErrors());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>$errors]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // handle file upload
        $attachmentPath = null;
        if ($file = $this->request->getFile('attachment')) {
            if ($file->isValid() && !$file->hasMoved()) {
                $attachmentPath = $file->getRandomName();
                $file->move(FCPATH . 'uploads/vendor_products/', $attachmentPath);
            }
        }

        (new VendorProductsModel())->insert([
            'vendor_id'    => $vid,
            'product_name' => $this->request->getPost('product_name'),
            'description'  => $this->request->getPost('description'),
            'price'        => $this->request->getPost('price') !== '' ? $this->request->getPost('price') : null,
            'attachment'   => $attachmentPath
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'success','message'=>'Produk berhasil ditambahkan']);
        }

        return redirect()->to(site_url('vendoruser/products'))->with('success','Produk ditambahkan.');
    }

    public function edit($id)
    {
        $vid = $this->vendorId();
        $item = (new VendorProductsModel())->where(['id'=>$id,'vendor_id'=>$vid])->first();

        if (!$item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>'Produk tidak ditemukan']);
            }
            return redirect()->to(site_url('vendoruser/products'));
        }

        return view('vendoruser/products/edit', ['item'=>$item]);
    }

    public function update($id)
    {
        $vid = $this->vendorId();
        $rules = [
            'product_name' => 'required|min_length[3]',
            'price'        => 'permit_empty|decimal',
            'attachment'   => 'permit_empty|uploaded[attachment]|max_size[attachment,10240]|ext_in[attachment,pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx]'
        ];

        if (!$this->validate($rules)) {
            $errors = implode("\n", $this->validator->getErrors());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>$errors]);
            }
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new VendorProductsModel();
        $item  = $model->where(['id'=>$id,'vendor_id'=>$vid])->first();

        if (!$item) {
            return redirect()->back()->with('errors', ['Produk tidak ditemukan']);
        }

        $updateData = [
            'product_name' => $this->request->getPost('product_name'),
            'description'  => $this->request->getPost('description'),
            'price'        => $this->request->getPost('price') !== '' ? $this->request->getPost('price') : null,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        // handle new attachment
        if ($file = $this->request->getFile('attachment')) {
            if ($file->isValid() && !$file->hasMoved()) {
                // hapus file lama jika ada
                if (!empty($item['attachment'])) {
                    $oldFile = FCPATH . 'uploads/vendor_products/' . $item['attachment'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                $attachmentName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/vendor_products/', $attachmentName);
                $updateData['attachment'] = $attachmentName;
            }
        }

        $model->where(['id'=>$id,'vendor_id'=>$vid])->set($updateData)->update();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'success','message'=>'Produk berhasil diperbarui']);
        }

        return redirect()->to(site_url('vendoruser/products'))->with('success','Produk diperbarui.');
    }

    public function delete($id)
    {
        $vid = $this->vendorId();
        $model = new VendorProductsModel();
        $item  = $model->where(['id'=>$id,'vendor_id'=>$vid])->first();

        if ($item) {
            // hapus file lampiran jika ada
            if (!empty($item['attachment'])) {
                $filePath = FCPATH . 'uploads/vendor_products/' . $item['attachment'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // hapus record
            $model->where(['id'=>$id,'vendor_id'=>$vid])->delete();
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'success','message'=>'Produk berhasil dihapus']);
        }

        return redirect()->to(site_url('vendoruser/products'))->with('success','Produk dihapus.');
    }
}
