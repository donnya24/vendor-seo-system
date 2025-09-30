<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\CommissionsModel;
use App\Models\SeoKeywordTargetsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vendors   = (new VendorProfilesModel())->countAllResults();

        $leads     = new LeadsModel();
        $today     = date('Y-m-d');
        $monthFrom = date('Y-m-01');
        $monthTo   = date('Y-m-t');

        // === METRIK ===
        // For today's leads, check if today's date falls within the date range
        $todayLeads = $leads->where('tanggal_mulai <=', $today)
                            ->where('tanggal_selesai >=', $today)
                            ->selectSum('jumlah_leads_masuk', 'total_masuk')
                            ->first()['total_masuk'] ?? 0;

        // For monthly deals, check for overlapping periods
        $monthlyDeals = $leads->where('tanggal_mulai <=', $monthTo)
                              ->where('tanggal_selesai >=', $monthFrom)
                              ->selectSum('jumlah_leads_closing', 'total_close')
                              ->first()['total_close'] ?? 0;

        // Hitung komisi bulan ini (status paid)
        $commissionsModel = new CommissionsModel();
        $monthlyCommissionPaid = $commissionsModel
            ->where('status', 'paid')
            ->where('created_at >=', $monthFrom)
            ->where('created_at <=', $monthTo)
            ->selectSum('amount', 'total_commission')
            ->first()['total_commission'] ?? 0;

        // Ambil top keyword
        $seoKeywordModel = new SeoKeywordTargetsModel();
        $topKeyword = $seoKeywordModel
        ->orderBy('current_position', 'ASC')  // posisi terbaik = 1 paling atas
        ->first();
        $topKeyword = $topKeyword ? $topKeyword['keyword'] : '-';

        // Total leads masuk (keseluruhan)
        $totalLeadsIn = $leads->selectSum('jumlah_leads_masuk', 'total_masuk')
                              ->first()['total_masuk'] ?? 0;

        // Total leads closing (keseluruhan)
        $totalLeadsClosing = $leads->selectSum('jumlah_leads_closing', 'total_close')
                                   ->first()['total_close'] ?? 0;

        // === PENGAJUAN VENDOR TERBARU (maks 3) ===
        $db = db_connect();
        $commissionRequests = [];

        // helper kecil untuk membangun COALESCE dinamis
        $buildCoalesce = static function(array $candidates, array $available, string $tableAlias, string $asAlias): string {
            $parts = [];
            foreach ($candidates as $c) {
                if (in_array($c, $available, true)) {
                    $parts[] = "NULLIF({$tableAlias}.{$c}, '')";
                }
            }
            if (empty($parts)) {
                return "'' AS {$asAlias}";
            }
            return 'COALESCE(' . implode(', ', $parts) . ", '') AS {$asAlias}";
        };

        // Cek keberadaan tabel vendor_profiles
        $vpTableExists = (bool) $db->query("SHOW TABLES LIKE 'vendor_profiles'")->getRowArray();

        if ($vpTableExists) {
            $vpFields = $db->getFieldNames('vendor_profiles');

            $hasStatus   = in_array('status',               $vpFields, true);
            $hasIsVerif  = in_array('is_verified',          $vpFields, true);
            $hasReqCom   = in_array('requested_commission', $vpFields, true);
            $hasCreated  = in_array('created_at',           $vpFields, true);

            // kandidat kolom untuk kontak
            $phoneCandidates = ['phone','telepon','telp','no_telp','nohp','hp','kontak','contact','phone_number','telpon'];
            $waCandidates    = ['whatsapp_number','whatsapp','wa','no_wa','nowa','whatsappno','wa_number'];

            $phoneExpr = $buildCoalesce($phoneCandidates, $vpFields, 'vp', 'phone');
            $waExpr    = $buildCoalesce($waCandidates,    $vpFields, 'vp', 'wa');

            $orderCol = $hasCreated ? 'vp.created_at' : 'vp.id';

            $qb = $db->table('vendor_profiles vp')
                ->select("
                    vp.id,
                    COALESCE(vp.business_name, '-') AS usaha,
                    COALESCE(vp.owner_name,   '-')  AS pemilik,
                    {$phoneExpr},
                    {$waExpr},
                    ".($hasReqCom? "COALESCE(vp.requested_commission,0)" : "0")." AS komisi,
                    ".($hasStatus? "COALESCE(vp.status,'pending')" : "'pending'")." AS status
                ");

            // ==== Hanya ambil vendor dengan status 'pending' ====
            if ($hasStatus) {
                $qb->where('vp.status', 'pending');
            } else {
                // Jika kolom status tidak ada, asumsikan semua vendor adalah pending
                $qb->where('1=1');
            }

            $commissionRequests = $qb->orderBy($orderCol, 'DESC')
                                     ->limit(3)
                                     ->get()->getResultArray();
        }

        // === Fallback: dari commissions bila masih kosong ===
        if (empty($commissionRequests)) {
            $cmTableExists = (bool) $db->query("SHOW TABLES LIKE 'commissions'")->getRowArray();
            if ($cmTableExists) {
                $cmFields    = $db->getFieldNames('commissions');
                $hasCMCreate = in_array('created_at', $cmFields, true);
                $orderCol2   = $hasCMCreate ? 'c.created_at' : 'c.id';

                // kita butuh lagi field vendor_profiles untuk menyusun kontak
                $vpFields = $db->getFieldNames('vendor_profiles');
                $phoneCandidates = ['phone','telepon','telp','no_telp','nohp','hp','kontak','contact','phone_number','telpon'];
                $waCandidates    = ['whatsapp_number','whatsapp','wa','no_wa','nowa','whatsappno','wa_number'];
                $phoneExpr = $buildCoalesce($phoneCandidates, $vpFields, 'vp', 'phone');
                $waExpr    = $buildCoalesce($waCandidates,    $vpFields, 'vp', 'wa');
                
                // Cek apakah kolom requested_commission ada di tabel commissions
                $hasRequestedCommission = in_array('requested_commission', $cmFields, true);
                $commissionExpr = $hasRequestedCommission ? "COALESCE(c.requested_commission, 0)" : "0";

                $commissionRequests = $db->table('commissions c')
                    ->select("
                        c.id,
                        COALESCE(vp.business_name, '-') AS usaha,
                        COALESCE(vp.owner_name,   '-')  AS pemilik,
                        {$phoneExpr},
                        {$waExpr},
                        {$commissionExpr} AS komisi,
                        COALESCE(c.status,'pending') AS status
                    ")
                    ->join('vendor_profiles vp', 'vp.id = c.vendor_id', 'left')
                    // Hanya ambil yang statusnya pending
                    ->where('c.status', 'pending')
                    ->orderBy($orderCol2, 'DESC')
                    ->limit(3)
                    ->get()->getResultArray();
            }
        }

        return view('admin/dashboard', [
            'page'  => 'Dashboard',
            'stats' => [
                'totalVendors' => (int) $vendors,
                'todayLeads'   => (int) $todayLeads,
                'monthlyDeals' => (int) $monthlyDeals,
                'monthlyCommissionPaid' => (float) $monthlyCommissionPaid,
                'topKeyword'   => $topKeyword,
                'totalLeadsIn' => (int) $totalLeadsIn,
                'totalLeadsClosing' => (int) $totalLeadsClosing,
                'leadsToday'   => (int) $todayLeads,
            ],
            'commissionRequests' => $commissionRequests,
            'recentLeads' => $this->fetchRecentLeads(),
        ]);
    }

    public function stats()
    {
        $vendors   = (new VendorProfilesModel())->countAllResults();

        $leads     = new LeadsModel();
        $today     = date('Y-m-d');
        $monthFrom = date('Y-m-01');
        $monthTo   = date('Y-m-t');

        // For today's leads, check if today's date falls within the date range
        $todayLeads = $leads->where('tanggal_mulai <=', $today)
                            ->where('tanggal_selesai >=', $today)
                            ->selectSum('jumlah_leads_masuk', 'total_masuk')
                            ->first()['total_masuk'] ?? 0;

        // For monthly deals, check for overlapping periods
        $monthlyDeals = $leads->where('tanggal_mulai <=', $monthTo)
                              ->where('tanggal_selesai >=', $monthFrom)
                              ->selectSum('jumlah_leads_closing', 'total_close')
                              ->first()['total_close'] ?? 0;

        // Hitung komisi bulan ini (status paid)
        $commissionsModel = new CommissionsModel();
        $monthlyCommissionPaid = $commissionsModel
            ->where('status', 'paid')
            ->where('created_at >=', $monthFrom)
            ->where('created_at <=', $monthTo)
            ->selectSum('amount', 'total_commission')
            ->first()['total_commission'] ?? 0;

        // Ambil top keyword
        $seoKeywordModel = new SeoKeywordTargetsModel();
        $topKeyword = $seoKeywordModel
            ->select('keyword')
            ->orderBy('search_volume', 'DESC')
            ->first();
        $topKeyword = $topKeyword ? $topKeyword['keyword'] : '-';

        return $this->response->setJSON([
            'totalVendors' => (int) $vendors,
            'todayLeads'   => (int) $todayLeads,
            'monthlyDeals' => (int) $monthlyDeals,
            'monthlyCommissionPaid' => (float) $monthlyCommissionPaid,
            'topKeyword'   => $topKeyword,
        ]);
    }

    private function fetchRecentLeads(): array
    {
        $leadsModel = new LeadsModel();

        // Ambil 10 leads terbaru beserta business_name dari vendor_profiles
        $rows = $leadsModel
            ->select('leads.id, leads.vendor_id, leads.jumlah_leads_masuk, leads.jumlah_leads_closing, leads.tanggal_mulai, leads.tanggal_selesai, leads.updated_at, COALESCE(vendor_profiles.business_name, "-") AS business_name')
            ->join('vendor_profiles', 'leads.vendor_id = vendor_profiles.id', 'left')
            ->orderBy('leads.id', 'DESC')
            ->limit(10)
            ->findAll();

        // Normalisasi data untuk view
        return array_map(static function ($r) {
            // Use tanggal_mulai as the primary date for display
            $tanggal = $r['tanggal_mulai'] ?? '-';
            
            return [
                'id_leads'      => isset($r['id']) ? (string)$r['id'] : '-',               // ID leads
                'vendor_id'     => isset($r['vendor_id']) ? (string)$r['vendor_id'] : '-', // ID vendor
                'business_name' => !empty($r['business_name']) ? $r['business_name'] : '-',// Nama usaha vendor
                'layanan'       => '-',                                                     // Placeholder layanan
                'masuk'         => isset($r['jumlah_leads_masuk']) ? (int)$r['jumlah_leads_masuk'] : 0,
                'diproses'      => 0,                                                      // Placeholder diproses
                'ditolak'       => 0,                                                      // Placeholder ditolak
                'closing'       => isset($r['jumlah_leads_closing']) ? (int)$r['jumlah_leads_closing'] : 0,
                'tanggal'       => $tanggal,                                                // Tanggal leads (use tanggal_mulai)
                'updated_at'    => $r['updated_at'] ?? '-',                                 // Update terakhir
                'detail_url'    => isset($r['id']) ? site_url('admin/leads/'.$r['id']) : '#', // Link detail
            ];
        }, $rows ?? []);
    }
}