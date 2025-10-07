<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;

class Leads extends BaseController
{
    protected $leadModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->leadModel = new LeadsModel();
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $start = $this->request->getGet('start');
        $end   = $this->request->getGet('end');

        // Validasi tanggal hanya jika kedua tanggal diisi
        if (!empty($start) && !empty($end) && !$this->leadModel->validateDateRange($start, $end)) {
            return redirect()->back()->with('error', 'Tanggal mulai tidak boleh lebih besar dari tanggal selesai');
        }

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        // Ambil data leads dengan filter (bisa vendor saja, periode saja, atau keduanya)
        $leads = $this->leadModel->getLeadsWithVendor($vendorId, $start, $end);

        // Ambil statistik summary berdasarkan filter yang sama
        $summary = $this->leadModel->getLeadsSummary($vendorId, $start, $end);

        // Log aktivitas view leads
        $logDescription = "Melihat daftar leads";
        $extraData = ['module' => 'leads'];
        
        if (!empty($vendorId)) {
            $vendorName = $this->getVendorName($vendorId, $vendors);
            $logDescription .= " untuk vendor {$vendorName}";
            $extraData['vendor_id'] = $vendorId;
        } else {
            $logDescription .= " semua vendor";
        }
        
        if (!empty($start) && !empty($end)) {
            $logDescription .= " periode {$start} s/d {$end}";
            $extraData['period_start'] = $start;
            $extraData['period_end'] = $end;
        } elseif (!empty($start)) {
            $logDescription .= " mulai dari {$start}";
            $extraData['period_start'] = $start;
        } elseif (!empty($end)) {
            $logDescription .= " sampai dengan {$end}";
            $extraData['period_end'] = $end;
        }

        log_activity_auto('view', $logDescription, $extraData);

        return view('seo/leads/index', [
            'title'      => 'Pantau Leads',
            'activeMenu' => 'leads',
            'leads'      => $leads,
            'pager'      => $this->leadModel->pager,
            'vendorId'   => $vendorId,
            'vendors'    => $vendors,
            'start'      => $start,
            'end'        => $end,
            'summary'    => $summary
        ]);
    }

    /**
     * Helper untuk mendapatkan nama vendor
     */
    private function getVendorName(int $vendorId, array $vendors): string
    {
        foreach ($vendors as $vendor) {
            if ($vendor['id'] == $vendorId) {
                return $vendor['business_name'];
            }
        }
        return 'Unknown Vendor';
    }
}