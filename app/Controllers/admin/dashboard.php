<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Dashboard extends BaseController
{
    public function index()
    {
        $leadsModel = new LeadsModel();

        // === Stats ringkas ===
        $vendors = (new VendorProfilesModel())->countAllResults();

        $todayLeadsRow = $leadsModel
            ->selectSum('jumlah_leads_masuk')
            ->where('tanggal', date('Y-m-d'))
            ->first();
        $todayLeads = $todayLeadsRow['jumlah_leads_masuk'] ?? 0;

        $monthlyDealsRow = $leadsModel
            ->selectSum('jumlah_leads_closing')
            ->where('MONTH(tanggal)', date('m'))
            ->where('YEAR(tanggal)', date('Y'))
            ->first();
        $monthlyDeals = $monthlyDealsRow['jumlah_leads_closing'] ?? 0;

        // === Ambil 5 leads terakhir dengan join vendor & service ===
        try {
            $db = db_connect();
            $recentLeads = $db->table('leads l')
                ->select('l.*, v.business_name AS vendor_name, s.name AS service_name')
                ->join('vendor_profiles v', 'v.id = l.vendor_id', 'left')
                ->join('services s', 's.id = l.service_id', 'left')
                ->orderBy('l.tanggal', 'DESC')
                ->limit(5)
                ->get()
                ->getResultArray();
        } catch (DatabaseException $e) {
            $recentLeads = [];
        }

        return view('admin/dashboard', [
            'page'        => 'Dashboard',
            'stats'       => [
                'totalVendors' => (int) $vendors,
                'todayLeads'   => (int) $todayLeads,
                'monthlyDeals' => (int) $monthlyDeals,
                'topKeywords'  => 0,
            ],
            'recentLeads' => $recentLeads,
        ]);
    }

    public function stats()
    {
        $leadsModel = new LeadsModel();

        $todayLeadsRow = $leadsModel
            ->selectSum('jumlah_leads_masuk')
            ->where('tanggal', date('Y-m-d'))
            ->first();
        $todayLeads = $todayLeadsRow['jumlah_leads_masuk'] ?? 0;

        $monthlyDealsRow = $leadsModel
            ->selectSum('jumlah_leads_closing')
            ->where('MONTH(tanggal)', date('m'))
            ->where('YEAR(tanggal)', date('Y'))
            ->first();
        $monthlyDeals = $monthlyDealsRow['jumlah_leads_closing'] ?? 0;

        return $this->response->setJSON([
            'totalVendors' => (new VendorProfilesModel())->countAllResults(),
            'todayLeads'   => (int) $todayLeads,
            'monthlyDeals' => (int) $monthlyDeals,
            'topKeywords'  => 0,
        ]);
    }
}
