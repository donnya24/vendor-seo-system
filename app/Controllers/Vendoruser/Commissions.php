<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Commissions extends BaseController
{
    private $vendorProfile;
    private $vendorId;
    private $isVerified;
    private $commissionModel;

    public function __construct()
    {
        $this->commissionModel = new CommissionsModel();
    }

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int)$user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? 0;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool)$this->vendorProfile;
    }

    private function checkVerifiedAccess(): bool
    {
        if (! $this->initVendor()) {
            return false;
        }
        
        if (! $this->isVerified) {
            return false;
        }
        
        return true;
    }

    private function withVendorData(array $data = []): array
    {
        return array_merge($data, [
            'vp' => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function logActivity(string $action, ?string $description = null): void
    {
        $user = service('auth')->user();
        (new ActivityLogsModel())->insert([
            'user_id'    => $user->id,
            'vendor_id'  => $this->vendorId,
            'module'     => 'commission',
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function index()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $list = $this->commissionModel
            ->where('vendor_id', $this->vendorId)
            ->orderBy('period_start', 'DESC')
            ->findAll();

        $this->logActivity('view', 'Melihat daftar komisi');

        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Komisi',
            'content_view' => 'vendoruser/commissions/index',
            'content_data' => [
                'page'  => 'Komisi',
                'items' => $list,
            ],
        ]));
    }

    public function create()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $this->logActivity('create_form', 'Membuka form tambah komisi');

        return view('vendoruser/commissions/create', $this->withVendorData([
            'page' => 'Tambah Komisi',
        ]));
    }

    public function store()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $rules = [
            'period_start' => 'required|valid_date',
            'period_end'   => 'required|valid_date',
            'earning'      => 'required|decimal',
            'amount'       => 'required|decimal',
            'proof'        => 'permit_empty|max_size[proof,10240]|ext_in[proof,pdf,jpg,jpeg,png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'vendor_id'    => $this->vendorId,
            'period_start' => $this->request->getPost('period_start'),
            'period_end'   => $this->request->getPost('period_end'),
            'earning'      => $this->request->getPost('earning'),
            'amount'       => $this->request->getPost('amount'),
            'status'       => 'unpaid',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($file = $this->request->getFile('proof')) {
            if ($file->isValid() && !$file->hasMoved()) {
                $filename = $file->getRandomName();
                $file->move(FCPATH.'uploads/commissions/', $filename);
                $data['proof'] = $filename;
            }
        }

        $this->commissionModel->insert($data);
        $this->logActivity('create', "Menambahkan komisi periode {$data['period_start']} - {$data['period_end']}");

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', 'Komisi berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $item = $this->commissionModel
            ->where(['id' => $id, 'vendor_id' => $this->vendorId])
            ->first();

        if (! $item) {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi tidak ditemukan.');
        }

        // Cek jika status sudah paid
        if ($item['status'] === 'paid') {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi yang sudah dibayar tidak dapat diedit.');
        }

        $this->logActivity('edit_form', "Membuka form edit komisi ID {$id}");

        return view('vendoruser/commissions/edit', $this->withVendorData([
            'page' => 'Edit Komisi',
            'item' => $item,
        ]));
    }

    public function update($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $item = $this->commissionModel
            ->where(['id' => $id, 'vendor_id' => $this->vendorId])
            ->first();

        if (! $item) {
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        // Cek jika status sudah paid
        if ($item['status'] === 'paid') {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi yang sudah dibayar tidak dapat diedit.');
        }

        $rules = [
            'period_start' => 'required|valid_date',
            'period_end'   => 'required|valid_date',
            'earning'      => 'required|decimal',
            'amount'       => 'required|decimal',
            'proof'        => 'permit_empty|max_size[proof,10240]|ext_in[proof,pdf,jpg,jpeg,png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'period_start' => $this->request->getPost('period_start'),
            'period_end'   => $this->request->getPost('period_end'),
            'earning'      => $this->request->getPost('earning'),
            'amount'       => $this->request->getPost('amount'),
            'updated_at'   => date('Y-m-d H:i:s'),
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

        $this->commissionModel->update($id, $updateData);
        $this->logActivity('update', "Memperbarui komisi ID {$id}");

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', 'Komisi berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (! $this->checkVerifiedAccess()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $item = $this->commissionModel
            ->where(['id' => (int)$id, 'vendor_id' => $this->vendorId])
            ->first();

        if (! $item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Komisi tidak ditemukan.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi tidak ditemukan.');
        }

        // Cek jika status sudah paid
        if ($item['status'] === 'paid') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Komisi yang sudah dibayar tidak dapat dihapus.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi yang sudah dibayar tidak dapat dihapus.');
        }

        if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
            @unlink(FCPATH.'uploads/commissions/'.$item['proof']);
        }

        $this->commissionModel->delete((int)$id);
        $this->logActivity('delete', "Menghapus komisi ID {$id}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Komisi berhasil dihapus.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', 'Komisi berhasil dihapus.');
    }

    public function deleteMultiple()
    {
        if (! $this->checkVerifiedAccess()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $ids = $this->request->isAJAX()
            ? ($this->request->getJSON(true)['ids'] ?? [])
            : ($this->request->getPost('ids') ?? []);

        $ids = array_values(array_unique(array_map('intval', (array)$ids)));

        if (empty($ids)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Tidak ada komisi yang dipilih.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Tidak ada komisi yang dipilih.');
        }

        $items = $this->commissionModel
            ->whereIn('id', $ids)
            ->where('vendor_id', $this->vendorId)
            ->findAll();

        if (empty($items)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Data tidak ditemukan atau bukan milik Anda.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan atau bukan milik Anda.');
        }

        // Filter hanya yang status bukan paid
        $editableItems = array_filter($items, function($item) {
            return $item['status'] !== 'paid';
        });

        if (empty($editableItems)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Komisi yang sudah dibayar tidak dapat dihapus.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Komisi yang sudah dibayar tidak dapat dihapus.');
        }

        $deleted = 0;
        foreach ($editableItems as $item) {
            if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
                @unlink(FCPATH.'uploads/commissions/'.$item['proof']);
            }
            $this->commissionModel->delete((int)$item['id']);
            $this->logActivity('delete', "Menghapus komisi ID {$item['id']}");
            $deleted++;
        }

        $msg = "Berhasil menghapus {$deleted} data.";

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => $msg,
                'csrfHash' => csrf_hash(),
            ]);
        }

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', $msg);
    }
}