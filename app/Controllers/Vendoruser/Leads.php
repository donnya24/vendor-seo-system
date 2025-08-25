<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\ServicesModel;
use App\Models\VendorProfilesModel;

class Leads extends BaseController
{
    /**
     * Ambil vendor_id milik user login; redirect bila belum punya profil.
     */
    private function requireVendorId(): int
    {
        $user = service('auth')->user();
        $vp   = (new VendorProfilesModel())
                    ->select('id')
                    ->where('user_id', (int) $user->id)
                    ->first();

        if (! $vp || empty($vp['id'])) {
            // aman: hentikan eksekusi setelah redirect
            redirect()->to(site_url('vendor/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.')
                ->send();
            exit;
        }

        return (int) $vp['id'];
    }

    public function index()
    {
        $vid   = $this->requireVendorId();
        $m     = new LeadsModel();

        // Filter opsional
        $status = trim((string) $this->request->getGet('status'));
        $q      = trim((string) $this->request->getGet('q'));

        $m->where('vendor_id', $vid)->orderBy('id', 'DESC');

        if ($status !== '' && in_array($status, ['new','in_progress','closed','rejected'], true)) {
            $m->where('status', $status);
        }
        if ($q !== '') {
            $m->groupStart()
                ->like('customer_name', $q)
                ->orLike('customer_phone', $q)
                ->orLike('summary', $q)
            ->groupEnd();
        }

        return view('vendoruser/leads/index', [
            'page'  => 'Leads',
            'leads' => $m->findAll(),
            'status'=> $status,
            'q'     => $q,
        ]);
    }

    public function create()
    {
        $vid = $this->requireVendorId();

        // Ambil service: bila skemamu punya kolom vendor_id di services
        // tampilkan service milik vendor; jika tidak ada, fallback ke semua service.
        $svc = new ServicesModel();

        // Coba prioritas milik vendor
        $services = $svc->where('vendor_id', $vid)->orderBy('name', 'ASC')->findAll();

        // Fallback: kalau kosong, tampilkan semua (atau sesuaikan kebijakanmu)
        if (empty($services)) {
            $services = $svc->orderBy('name', 'ASC')->findAll();
        }

        return view('vendoruser/leads/create', [
            'page'     => 'Leads',
            'services' => $services,
        ]);
    }

    public function store()
    {
        $vid = $this->requireVendorId();

        $rules = [
            'customer_name'  => 'required|min_length[2]',
            'customer_phone' => 'required|min_length[6]',
            'source'         => 'required|in_list[whatsapp,vendor_manual]',
            // kolom opsional → validasi longgar
            'service_id'     => 'permit_empty|integer',
            'status'         => 'permit_empty|in_list[new,in_progress,closed,rejected]',
            'contact_time'   => 'permit_empty', // kalau kolomnya ada
            'summary'        => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $data = [
            'vendor_id'      => $vid,
            'customer_name'  => trim((string) $this->request->getPost('customer_name')),
            'customer_phone' => trim((string) $this->request->getPost('customer_phone')),
            'service_id'     => (int) ($this->request->getPost('service_id') ?: 0) ?: null,
            'status'         => $this->request->getPost('status') ?: 'new',
            'source'         => $this->request->getPost('source') ?: 'vendor_manual',
            // opsional — hanya tersimpan jika ada di $allowedFields LeadsModel
            'summary'        => $this->request->getPost('summary') ?: null,
            'contact_time'   => $this->request->getPost('contact_time') ?: null,
            'created_at'     => date('Y-m-d H:i:s'),
        ];

        (new LeadsModel())->insert($data);

        return redirect()->to(site_url('vendor/leads'))->with('success', 'Lead berhasil dibuat.');
    }

    public function edit($id)
    {
        $vid  = $this->requireVendorId();
        $lead = (new LeadsModel())->where(['id' => (int) $id, 'vendor_id' => $vid])->first();
        if (! $lead) {
            return redirect()->to(site_url('vendor/leads'))->with('error', 'Lead tidak ditemukan.');
        }

        $svc = new ServicesModel();
        $services = $svc->where('vendor_id', $vid)->orderBy('name', 'ASC')->findAll();
        if (empty($services)) {
            $services = $svc->orderBy('name', 'ASC')->findAll();
        }

        return view('vendoruser/leads/edit', [
            'page'     => 'Leads',
            'lead'     => $lead,
            'services' => $services,
        ]);
    }

    public function update($id)
    {
        $vid  = $this->requireVendorId();
        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $vid])->first();

        if (! $lead) {
            return redirect()->to(site_url('vendor/leads'))->with('error', 'Lead tidak ditemukan.');
        }

        $rules = [
            'customer_name'  => 'required|min_length[2]',
            'customer_phone' => 'required|min_length[6]',
            'status'         => 'required|in_list[new,in_progress,closed,rejected]',
            'source'         => 'required|in_list[whatsapp,vendor_manual]',
            'service_id'     => 'permit_empty|integer',
            'summary'        => 'permit_empty',
            'contact_time'   => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('error', implode('<br>', $this->validator->getErrors()));
        }

        $m->update((int) $id, [
            'customer_name'  => trim((string) $this->request->getPost('customer_name')),
            'customer_phone' => trim((string) $this->request->getPost('customer_phone')),
            'service_id'     => (int) ($this->request->getPost('service_id') ?: 0) ?: null,
            'status'         => $this->request->getPost('status'),
            'source'         => $this->request->getPost('source'),
            'summary'        => $this->request->getPost('summary') ?: null,
            'contact_time'   => $this->request->getPost('contact_time') ?: null,
            'updated_at'     => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('vendor/leads'))->with('success', 'Lead diperbarui.');
    }

    public function delete($id)
    {
        $vid  = $this->requireVendorId();
        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $vid])->first();

        if (! $lead) {
            return redirect()->to(site_url('vendor/leads'))->with('error', 'Lead tidak ditemukan.');
        }

        $m->delete((int) $id);
        return redirect()->to(site_url('vendor/leads'))->with('success', 'Lead dihapus.');
    }
}
