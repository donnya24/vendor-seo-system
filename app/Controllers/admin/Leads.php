<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\LeadEvidencesModel;
use App\Models\VendorProfilesModel;
use App\Models\ServicesModel;
use App\Models\AreasModel;

class Leads extends BaseController
{
    public function index()
    {
        $m = new LeadsModel();

        // Filter (GET)
        $status   = $this->request->getGet('status');
        $vendorId = $this->request->getGet('vendor_id');
        $serviceId= $this->request->getGet('service_id');
        $areaId   = $this->request->getGet('area_id');
        $source   = $this->request->getGet('source');
        $from     = $this->request->getGet('from');
        $to       = $this->request->getGet('to');

        if ($status)    $m->where('status', $status);
        if ($vendorId)  $m->where('vendor_id', $vendorId);
        if ($serviceId) $m->where('service_id', $serviceId);
        if ($areaId)    $m->where('area_id', $areaId);
        if ($source)    $m->where('source', $source);
        if ($from)      $m->where('DATE(created_at) >=', $from);
        if ($to)        $m->where('DATE(created_at) <=', $to);

        $leads = $m->orderBy('id','DESC')->findAll();

        return view('admin/leads/index', [
            'page'     => 'Leads',
            'leads'    => $leads,
            'vendors'  => (new VendorProfilesModel())->findAll(),
            'services' => (new ServicesModel())->findAll(),
            'areas'    => (new AreasModel())->findAll(),
            'filters'  => compact('status','vendorId','serviceId','areaId','source','from','to')
        ]);
    }



    // Export dummy (CSV)
    public function exportCsv()
    {
        $rows = (new LeadsModel())->orderBy('id','DESC')->findAll();
        $out  = fopen('php://output', 'w');
        $filename = 'leads_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        fputcsv($out, ['ID','Customer','Vendor','Service','Area','Status','Source','Created At']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'] ?? $r->id ?? '',
                $r['customer_name'] ?? '',
                $r['vendor_id'] ?? '',
                $r['service_id'] ?? '',
                $r['area_id'] ?? '',
                $r['status'] ?? '',
                $r['source'] ?? '',
                $r['created_at'] ?? '',
            ]);
        }
        fclose($out); exit;
    }

    // Export dummy (XLSX) → untuk cepat, kirim CSV juga tapi ubah header
    public function exportXlsx()
    {
        $rows = (new LeadsModel())->orderBy('id','DESC')->findAll();
        $out  = fopen('php://output', 'w');
        $filename = 'leads_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');

        fputcsv($out, ['ID','Customer','Vendor','Service','Area','Status','Source','Created At']);
        foreach ($rows as $r) {
            fputcsv($out, [
                $r['id'] ?? '',
                $r['customer_name'] ?? '',
                $r['vendor_id'] ?? '',
                $r['service_id'] ?? '',
                $r['area_id'] ?? '',
                $r['status'] ?? '',
                $r['source'] ?? '',
                $r['created_at'] ?? '',
            ]);
        }
        fclose($out); exit;
    }
}
