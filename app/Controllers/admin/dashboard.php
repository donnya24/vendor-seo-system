<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vendors   = (new VendorProfilesModel())->countAllResults();

        $leads     = new LeadsModel();
        $today     = date('Y-m-d');   // hindari DATE(tanggal) di WHERE; kolom tanggal = tipe DATE
        $monthFrom = date('Y-m-01');
        $monthTo   = date('Y-m-t');

        // === METRIK ===
        // 1) Total leads MASUK hari ini (jumlah, bukan jumlah baris)
        $todayLeads = $leads->where('tanggal', $today)
                            ->selectSum('jumlah_leads_masuk', 'total_masuk')
                            ->first()['total_masuk'] ?? 0;

        // 2) Total CLOSING bulan berjalan (jumlah, bukan jumlah baris)
        $monthlyDeals = $leads->where('tanggal >=', $monthFrom)
                              ->where('tanggal <=', $monthTo)
                              ->selectSum('jumlah_leads_closing', 'total_close')
                              ->first()['total_close'] ?? 0;

        // 3) Placeholder top keywords (belum ada tabel keywords di dump)
        $topKeywords = 0;

        return view('admin/dashboard', [
            'page'  => 'Dashboard',
            'stats' => [
                'totalVendors' => (int) $vendors,
                'todayLeads'   => (int) $todayLeads,
                'monthlyDeals' => (int) $monthlyDeals,
                'topKeywords'  => (int) $topKeywords,
            ],
            // Jika mau aktifkan data asli untuk "Leads Terbaru" di view, tinggal buka baris di bawah:
            // 'recentLeads' => $this->fetchRecentLeads(),
        ]);
    }

    public function stats()
    {
        $vendors   = (new VendorProfilesModel())->countAllResults();

        $leads     = new LeadsModel();
        $today     = date('Y-m-d');
        $monthFrom = date('Y-m-01');
        $monthTo   = date('Y-m-t');

        $todayLeads = $leads->where('tanggal', $today)
                            ->selectSum('jumlah_leads_masuk', 'total_masuk')
                            ->first()['total_masuk'] ?? 0;

        $monthlyDeals = $leads->where('tanggal >=', $monthFrom)
                              ->where('tanggal <=', $monthTo)
                              ->selectSum('jumlah_leads_closing', 'total_close')
                              ->first()['total_close'] ?? 0;

        return $this->response->setJSON([
            'totalVendors' => (int) $vendors,
            'todayLeads'   => (int) $todayLeads,
            'monthlyDeals' => (int) $monthlyDeals,
            'topKeywords'  => 0,
        ]);
    }

    /**
     * Opsional: Isi "Leads Terbaru" dari DB dan mapping ke struktur yang dipakai view.
     * Aktifkan di index() jika ingin dipakai.
     */
    private function fetchRecentLeads(): array
    {
        $rows = (new LeadsModel())
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->find();

        return array_map(static function($r){
            return [
                'id_leads'   => (string)($r['id'] ?? '-'),
                'layanan'    => '-', // tidak ada kolom layanan di tabel leads pada dump
                'masuk'      => (int)($r['jumlah_leads_masuk'] ?? 0),
                'diproses'   => 0,    // kolom ini tidak ada di dump
                'ditolak'    => 0,    // kolom ini tidak ada di dump
                'closing'    => (int)($r['jumlah_leads_closing'] ?? 0),
                'tanggal'    => $r['tanggal'] ?? '-',
                'updated_at' => $r['updated_at'] ?? '-',
                'detail_url' => site_url('admin/leads/'.($r['id'] ?? '')),
            ];
        }, $rows ?? []);
    }
}
