<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;
use App\Models\ServicesModel;

class Leads extends BaseController
{
    public function index()
    {
        $m = new LeadsModel();

        // Filter GET
        $vendorId  = $this->request->getGet('vendor_id');
        $serviceId = $this->request->getGet('service_id');
        $from      = $this->request->getGet('from');
        $to        = $this->request->getGet('to');

        if ($vendorId)  $m->where('leads.vendor_id', $vendorId);
        if ($serviceId) $m->where('leads.service_id', $serviceId);
        if ($from)      $m->where('leads.tanggal >=', $from);
        if ($to)        $m->where('leads.tanggal <=', $to);

        // Join biar tampil vendor & service name
        $m->select('leads.*, vendor_profiles.business_name, services.name as service_name')
          ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
          ->join('services', 'services.id = leads.service_id', 'left')
          ->orderBy('leads.tanggal','DESC');

        $leads = $m->findAll();

        return view('admin/leads/index', [
            'page'     => 'Laporan Leads',
            'leads'    => $leads,
            'vendors'  => (new VendorProfilesModel())->findAll(),
            'services' => (new ServicesModel())->findAll(),
            'filters'  => compact('vendorId','serviceId','from','to')
        ]);
    }

    // Export CSV
    public function exportCsv()
    {
        $rows = (new LeadsModel())
            ->select('leads.*, vendor_profiles.business_name, services.name as service_name')
            ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
            ->join('services', 'services.id = leads.service_id', 'left')
            ->orderBy('leads.id','DESC')->findAll();

        $out  = fopen('php://output', 'w');
        $filename = 'leads_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        fputcsv($out, [
            'ID','Vendor','Tanggal','Service',
            'Leads Masuk','Diproses','Ditolak','Closing',
            'Reported By','Assigned At','Updated At'
        ]);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['business_name'] ?? '',
                $r['tanggal'],
                $r['service_name'] ?? '',
                $r['jumlah_leads_masuk'],
                $r['jumlah_leads_diproses'],
                $r['jumlah_leads_ditolak'],
                $r['jumlah_leads_closing'],
                $r['reported_by_vendor'],
                $r['assigned_at'],
                $r['updated_at'],
            ]);
        }
        fclose($out); exit;
    }

    // Export XLSX (masih CSV tapi header beda)
    public function exportXlsx()
    {
        $rows = (new LeadsModel())
            ->select('leads.*, vendor_profiles.business_name, services.name as service_name')
            ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
            ->join('services', 'services.id = leads.service_id', 'left')
            ->orderBy('leads.id','DESC')->findAll();

        $out  = fopen('php://output', 'w');
        $filename = 'leads_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        fputcsv($out, [
            'ID','Vendor','Tanggal','Service',
            'Leads Masuk','Diproses','Ditolak','Closing',
            'Reported By','Assigned At','Updated At'
        ]);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'],
                $r['business_name'] ?? '',
                $r['tanggal'],
                $r['service_name'] ?? '',
                $r['jumlah_leads_masuk'],
                $r['jumlah_leads_diproses'],
                $r['jumlah_leads_ditolak'],
                $r['jumlah_leads_closing'],
                $r['reported_by_vendor'],
                $r['assigned_at'],
                $r['updated_at'],
            ]);
        }
        fclose($out); exit;
    }
}
