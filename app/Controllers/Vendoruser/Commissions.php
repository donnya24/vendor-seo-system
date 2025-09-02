<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\NotificationsModel;
use App\Models\VendorProfilesModel;

class Commissions extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())
            ->where('user_id', (int)service('auth')->user()->id)
            ->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        $list = (new CommissionsModel())
            ->where('vendor_id', $vid)
            ->orderBy('period_start', 'DESC')
            ->findAll();
        return view('vendoruser/commissions/index', [
            'page'  => 'Komisi',
            'items' => $list
        ]);
    }

    public function create()
    {
        return view('vendoruser/commissions/create');
    }

    public function store()
    {
        $vid = $this->vendorId();
        $model = new CommissionsModel();

        $rules = [
            'period_start' => 'required|valid_date',
            'period_end'   => 'required|valid_date',
            'amount'       => 'required|decimal',
            'proof'        => 'permit_empty|uploaded[proof]|max_size[proof,10240]|ext_in[proof,pdf,jpg,jpeg,png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'vendor_id'    => $vid,
            'period_start' => $this->request->getPost('period_start'),
            'period_end'   => $this->request->getPost('period_end'),
            'amount'       => $this->request->getPost('amount'),
            'status'       => 'unpaid',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($file = $this->request->getFile('proof')) {
            if ($file->isValid() && !$file->hasMoved()) {
                $filename = $file->getRandomName();
                $file->move(FCPATH.'uploads/commissions/', $filename);
                $data['proof'] = $filename;
            }
        }

        $model->insert($data);
        return redirect()->to(site_url('vendoruser/commissions'))->with('success', 'Komisi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $vid = $this->vendorId();
        $item = (new CommissionsModel())
            ->where(['id' => $id, 'vendor_id' => $vid])
            ->first();
        if (!$item) {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('errors', ['Komisi tidak ditemukan.']);
        }
        return view('vendoruser/commissions/edit', ['item' => $item]);
    }

    public function update($id)
    {
        $vid = $this->vendorId();
        $model = new CommissionsModel();
        $item = $model->where(['id' => $id, 'vendor_id' => $vid])->first();

        if (!$item) {
            return redirect()->back()->with('errors', ['Komisi tidak ditemukan.']);
        }

        $rules = [
            'period_start' => 'required|valid_date',
            'period_end'   => 'required|valid_date',
            'amount'       => 'required|decimal',
            'proof'        => 'permit_empty|uploaded[proof]|max_size[proof,10240]|ext_in[proof,pdf,jpg,jpeg,png]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'period_start' => $this->request->getPost('period_start'),
            'period_end'   => $this->request->getPost('period_end'),
            'amount'       => $this->request->getPost('amount'),
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if ($file = $this->request->getFile('proof')) {
            if ($file->isValid() && !$file->hasMoved()) {
                if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
                    unlink(FCPATH.'uploads/commissions/'.$item['proof']);
                }
                $filename = $file->getRandomName();
                $file->move(FCPATH.'uploads/commissions/', $filename);
                $updateData['proof'] = $filename;
            }
        }

        $model->where(['id' => $id, 'vendor_id' => $vid])->set($updateData)->update();
        return redirect()->to(site_url('vendoruser/commissions'))->with('success', 'Komisi berhasil diperbarui.');
    }

    public function delete($id)
    {
        $vid = $this->vendorId();
        $model = new CommissionsModel();
        $item = $model->where(['id' => $id, 'vendor_id' => $vid])->first();

        if ($item) {
            if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
                unlink(FCPATH.'uploads/commissions/'.$item['proof']);
            }
            $model->where(['id' => $id, 'vendor_id' => $vid])->delete();
        }

        return redirect()->to(site_url('vendoruser/commissions'))->with('success', 'Komisi berhasil dihapus.');
    }
}
