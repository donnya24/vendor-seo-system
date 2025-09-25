<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Leads extends BaseController
{
    private $vendorProfile;
    private $isVerified;
    private $vendorId;
    private $activityLogsModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
    }

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? null;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorId;
    }

    private function withVendorData(array $data = [])
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function logActivity(string $action, string $description = null, array $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            $data = [
                'user_id'     => $user->id,
                'vendor_id'   => $this->vendorId,
                'module'      => 'leads',
                'action'      => $action,
                'status'      => 'success',
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Leads: ' . $e->getMessage());
        }
    }

    public function index()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $m = new LeadsModel();

        $start = (string) $this->request->getGet('start_date');
        $end   = (string) $this->request->getGet('end_date');

        $m->where('vendor_id', $this->vendorId);

        if ($start !== '' && $end !== '') {
            $m->where('tanggal >=', $start)
            ->where('tanggal <=', $end);
        }

        $m->orderBy('tanggal', 'DESC');

        $this->logActivity('view', 'Melihat daftar leads', [
            'start_date' => $start,
            'end_date' => $end
        ]);

        // ⬇️ gunakan layout master
        return view('vendoruser/layouts/vendor_master', [
            'title'        => 'Laporan Leads',
            // data yang dibutuhkan layout (header/sidebar)
            'vp'           => $this->vendorProfile,
            'isVerified'   => $this->isVerified,

            // view konten utama & datanya
            'content_view' => 'vendoruser/leads/index',
            'content_data' => [
                'page'       => 'Laporan Leads',
                'leads'      => $m->findAll(),
                'start_date' => $start,
                'end_date'   => $end,
            ],
        ]);
    }

    public function create()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $this->logActivity('create_form', 'Membuka form tambah leads');

        return view('vendoruser/leads/create', $this->withVendorData([
            'page' => 'Tambah Laporan Leads',
        ]));
    }

    public function show($id)
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>'Data tidak ditemukan']);
            }
            return redirect()->to(site_url('vendoruser/leads'))->with('error','Data tidak ditemukan');
        }

        $this->logActivity('view_detail', "Melihat detail leads ID {$id}", [
            'lead_id' => $id
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'success','lead'=>$lead]);
        }

        return view('vendoruser/leads/show', $this->withVendorData(['lead'=>$lead]));
    }

    public function edit($id)
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada.');
        }

        $lead = (new LeadsModel())->where([
            'id'        => (int) $id,
            'vendor_id' => $this->vendorId,
        ])->first();

        if (! $lead) {
            return redirect()->to(site_url('vendoruser/leads'))
                ->with('error', 'Laporan tidak ditemukan.');
        }

        $this->logActivity('edit_form', "Membuka form edit leads ID {$id}", [
            'lead_id' => $id
        ]);

        return view('vendoruser/leads/edit', $this->withVendorData([
            'page' => 'Edit Laporan Leads',
            'lead' => $lead,
        ]));
    }

    public function store()
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $rules = [
            'tanggal'             => 'required|valid_date[Y-m-d]',
            'jumlah_leads_masuk'  => 'required|integer',
            'jumlah_leads_closing'=> 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'vendor_id'           => $this->vendorId,
            'tanggal'             => $this->request->getPost('tanggal'),
            'jumlah_leads_masuk'  => (int) $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_closing'=> (int) $this->request->getPost('jumlah_leads_closing'),
            'reported_by_vendor'  => $this->vendorId,
            'assigned_at'         => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $leadsModel = new LeadsModel();
        $result = $leadsModel->insert($data);
        $insertId = $leadsModel->getInsertID();

        if ($result) {
            $this->logActivity('create', "Menambahkan laporan leads tanggal {$data['tanggal']}", [
                'lead_id' => $insertId,
                'tanggal' => $data['tanggal'],
                'jumlah_leads_masuk' => $data['jumlah_leads_masuk'],
                'jumlah_leads_closing' => $data['jumlah_leads_closing']
            ]);
            return $this->respondAjax('success', 'Laporan leads berhasil ditambahkan.');
        } else {
            return $this->respondAjax('error', 'Gagal menambahkan laporan leads.');
        }
    }

    public function update($id)
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            return $this->respondAjax('error', 'Laporan tidak ditemukan.');
        }

        $rules = [
            'tanggal'             => 'required|valid_date[Y-m-d]',
            'jumlah_leads_masuk'  => 'required|integer',
            'jumlah_leads_closing'=> 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
        }

        $updateData = [
            'tanggal'             => $this->request->getPost('tanggal'),
            'jumlah_leads_masuk'  => (int) $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_closing'=> (int) $this->request->getPost('jumlah_leads_closing'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $result = $m->update((int) $id, $updateData);

        if ($result) {
            $this->logActivity('update', "Memperbarui laporan leads ID {$id}", [
                'lead_id' => $id,
                'tanggal' => $updateData['tanggal'],
                'jumlah_leads_masuk' => $updateData['jumlah_leads_masuk'],
                'jumlah_leads_closing' => $updateData['jumlah_leads_closing']
            ]);
            return $this->respondAjax('success', 'Laporan leads berhasil diperbarui.');
        } else {
            return $this->respondAjax('error', 'Gagal memperbarui laporan leads.');
        }
    }

    public function delete($id)
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            return $this->respondAjax('error', 'Laporan tidak ditemukan.');
        }

        $result = $m->delete((int) $id);

        if ($result) {
            $this->logActivity('delete', "Menghapus laporan leads ID {$id}", [
                'lead_id' => $id,
                'tanggal' => $lead['tanggal']
            ]);
            return $this->respondAjax('success', 'Laporan leads berhasil dihapus.');
        } else {
            return $this->respondAjax('error', 'Gagal menghapus laporan leads.');
        }
    }

    private function respondAjax(string $status, string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => $status,
                'message' => $message
            ]);
        }

        $type = $status === 'success' ? 'success' : 'error';
        return redirect()->back()->with($type, $message);
    }

    public function deleteMultiple()
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada.');
        }

        $ids = $this->request->getJSON(true)['ids'] ?? [];

        if (empty($ids)) {
            return $this->respondAjax('error', 'Tidak ada data terpilih.');
        }

        $m = new LeadsModel();
        $deleted = $m->where('vendor_id', $this->vendorId)
                    ->whereIn('id', $ids)
                    ->delete();

        if ($deleted) {
            $this->logActivity('delete_multiple', "Menghapus multiple laporan leads", [
                'lead_ids' => $ids,
                'count' => count($ids)
            ]);
            return $this->respondAjax('success', 'Data terpilih berhasil dihapus.');
        }

        return $this->respondAjax('error', 'Gagal menghapus data terpilih.');
    }
}