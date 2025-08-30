<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\ServicesModel;
use App\Models\VendorProfilesModel;

class Leads extends BaseController
{
    private function requireVendorId(): ?int
    {
        $user = service('auth')->user();
        $vp   = (new VendorProfilesModel())
                    ->select('id')
                    ->where('user_id', (int) $user->id)
                    ->first();

        return $vp['id'] ?? null;
    }

    public function index()
    {
        $vid = $this->requireVendorId();
        if (! $vid) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $m = new LeadsModel();
        $m->select('leads.*, services.name as service_name')
          ->join('services', 'services.id = leads.service_id', 'left')
          ->where('leads.vendor_id', $vid)
          ->orderBy('leads.tanggal', 'DESC');

        return view('vendoruser/leads/index', [
            'page'  => 'Laporan Leads',
            'leads' => $m->findAll(),
        ]);
    }

    public function create()
    {
        $vid = $this->requireVendorId();
        if (! $vid) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $svc = new ServicesModel();
        $services = $svc->where('vendor_id', $vid)->orderBy('name', 'ASC')->findAll();
        if (empty($services)) {
            $services = $svc->orderBy('name', 'ASC')->findAll();
        }

        return view('vendoruser/leads/create', [
            'page'     => 'Tambah Laporan Leads',
            'services' => $services,
        ]);
    }
    public function show($id)
    {
        $vid = $this->requireVendorId();
        $m   = new LeadsModel();

        $lead = $m->select('leads.*, services.name as service_name')
                ->join('services', 'services.id = leads.service_id', 'left')
                ->where(['leads.id' => (int) $id, 'leads.vendor_id' => $vid])
                ->first();

        if (! $lead) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>'Data tidak ditemukan']);
            }
            return redirect()->to(site_url('vendoruser/leads'))->with('error','Data tidak ditemukan');
        }

        // Response AJAX → JSON
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => 'success',
                'lead'    => $lead
            ]);
        }

        // Response biasa (misal akses langsung via URL)
        return view('vendoruser/leads/show', ['lead'=>$lead]);
    }

    public function edit($id)
    {
        $vid = $this->requireVendorId();
        if (! $vid) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $lead = (new LeadsModel())->where([
            'id'        => (int) $id,
            'vendor_id' => $vid,
        ])->first();

        if (! $lead) {
            return redirect()->to(site_url('vendoruser/leads'))
                ->with('error', 'Laporan tidak ditemukan.');
        }

        $svc = new ServicesModel();
        $services = $svc->where('vendor_id', $vid)->orderBy('name', 'ASC')->findAll();
        if (empty($services)) {
            $services = $svc->orderBy('name', 'ASC')->findAll();
        }

        return view('vendoruser/leads/edit', [
            'page'     => 'Edit Laporan Leads',
            'lead'     => $lead,
            'services' => $services,
        ]);
    }

    public function store()
    {
        $vid = $this->requireVendorId();
        if (! $vid) {
            return $this->respondAjax('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $rules = [
            'tanggal'               => 'required|valid_date[Y-m-d]',
            'service_id'            => 'required|integer',
            'jumlah_leads_masuk'    => 'required|integer',
            'jumlah_leads_diproses' => 'required|integer',
            'jumlah_leads_ditolak'  => 'required|integer',
            'jumlah_leads_closing'  => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'vendor_id'             => $vid,
            'tanggal'               => $this->request->getPost('tanggal'),
            'service_id'            => (int) $this->request->getPost('service_id'),
            'jumlah_leads_masuk'    => (int) $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_diproses' => (int) $this->request->getPost('jumlah_leads_diproses'),
            'jumlah_leads_ditolak'  => (int) $this->request->getPost('jumlah_leads_ditolak'),
            'jumlah_leads_closing'  => (int) $this->request->getPost('jumlah_leads_closing'),
            'reported_by_vendor'    => $vid,
            'assigned_at'           => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ];

        (new LeadsModel())->insert($data);

        return $this->respondAjax('success', 'Laporan leads berhasil ditambahkan.');
    }

    public function update($id)
    {
        $vid = $this->requireVendorId();
        $m   = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $vid])->first();

        if (! $lead) {
            return $this->respondAjax('error', 'Laporan tidak ditemukan.');
        }

        $rules = [
            'tanggal'               => 'required|valid_date[Y-m-d]',
            'service_id'            => 'required|integer',
            'jumlah_leads_masuk'    => 'required|integer',
            'jumlah_leads_diproses' => 'required|integer',
            'jumlah_leads_ditolak'  => 'required|integer',
            'jumlah_leads_closing'  => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
        }

        $m->update((int) $id, [
            'tanggal'               => $this->request->getPost('tanggal'),
            'service_id'            => (int) $this->request->getPost('service_id'),
            'jumlah_leads_masuk'    => (int) $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_diproses' => (int) $this->request->getPost('jumlah_leads_diproses'),
            'jumlah_leads_ditolak'  => (int) $this->request->getPost('jumlah_leads_ditolak'),
            'jumlah_leads_closing'  => (int) $this->request->getPost('jumlah_leads_closing'),
            'updated_at'            => date('Y-m-d H:i:s'),
        ]);

        return $this->respondAjax('success', 'Laporan leads berhasil diperbarui.');
    }

    public function delete($id)
    {
        $vid = $this->requireVendorId();
        $m   = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $vid])->first();

        if (! $lead) {
            return $this->respondAjax('error', 'Laporan tidak ditemukan.');
        }

        $m->delete((int) $id);

        return $this->respondAjax('success', 'Laporan leads berhasil dihapus.');
    }

    /**
     * Helper untuk balikan response sesuai request:
     * - Kalau AJAX → JSON
     * - Kalau normal → redirect dengan flashdata
     */
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
}
