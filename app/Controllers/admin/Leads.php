<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;

class Leads extends BaseController
{
    protected $leadsModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->leadsModel  = new LeadsModel();
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
        $leadsModel  = new \App\Models\LeadsModel();
        $vendorModel = new \App\Models\VendorProfilesModel();

        $leads = $leadsModel
            ->select('leads.*, vendor_profiles.business_name AS vendor_name') // ✅ alias vendor_name
            ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
            ->orderBy('leads.id', 'DESC')
            ->findAll();

        $vendors = $vendorModel
            ->select('id, business_name')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        return view('admin/leads/index', [
            'page'    => 'Leads',
            'leads'   => $leads,
            'vendors' => $vendors,
        ]);
    }

    public function create()
    {
        return view('admin/leads/create', [
            'vendors' => $this->vendorModel->findAll(),
        ]);
    }

    public function store()
    {
        $m = new LeadsModel();

        $m->insert([
            'vendor_id'            => $this->request->getPost('vendor_id'),
            'tanggal'              => $this->request->getPost('tanggal'),
            'jumlah_leads_masuk'   => $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_closing' => $this->request->getPost('jumlah_leads_closing'),
            'reported_by_vendor'   => $this->request->getPost('reported_by_vendor') ?? 0, // ✅ default 0
            'assigned_at'          => null,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('admin/leads')->with('success', 'Lead berhasil ditambahkan');
    }

    public function edit($id)
    {
        $m = new LeadsModel();
        $lead = $m->find($id);

        if (! $lead) {
            return redirect()->to('admin/leads')->with('error', 'Lead tidak ditemukan');
        }

        return view('admin/leads/edit', [
            'lead'    => $lead,
            'vendors' => (new VendorProfilesModel())->findAll(),
        ]);
    }

    public function update($id)
    {
        $m = new LeadsModel();

        $m->update($id, [
            'vendor_id'            => $this->request->getPost('vendor_id'),
            'tanggal'              => $this->request->getPost('tanggal'),
            'jumlah_leads_masuk'   => $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_closing' => $this->request->getPost('jumlah_leads_closing'),
            'reported_by_vendor'   => $this->request->getPost('reported_by_vendor') ?? 0,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('admin/leads')->with('success', 'Lead berhasil diperbarui');
    }

    public function delete($id)
    {
        $this->leadsModel->delete($id);
        return redirect()->to('admin/leads')->with('success', 'Lead berhasil dihapus');
    }

    public function show($id)
    {
        $leadsModel = new \App\Models\LeadsModel();
        $vendorsModel = new \App\Models\VendorProfilesModel();

        $lead = $leadsModel->select('leads.*, vendor_profiles.business_name as vendor_name')
                        ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
                        ->find($id);

        if (!$lead) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Lead dengan ID $id tidak ditemukan");
        }

        return view('admin/leads/show', [
            'lead' => $lead,
            'vendors' => $vendorsModel->findAll(),
        ]);
    }
}
