<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\CommissionsModel;
use App\Models\SeoReportsModel;
use App\Models\SeoProfilesModel;
use App\Models\ActivityLogsModel;

class Dashboard extends BaseAdminController
{
    protected ActivityLogsModel $activityLogsModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        // Log activity akses dashboard
        $this->logActivity(
            'view_dashboard',
            'Mengakses dashboard admin'
        );

        // Load common data for header
        $commonData = $this->loadCommonData();
        
        // Total vendor yang sudah verified
        $vendorModel = new VendorProfilesModel();
        $totalVendors = $vendorModel->where('status', 'verified')->countAllResults();

        // Total tim SEO yang aktif
        $seoModel = new SeoProfilesModel();
        $totalSeoTeam = $seoModel->where('status', 'active')->countAllResults();

        $leads     = new LeadsModel();
        $today     = date('Y-m-d');
        $monthFrom = date('Y-m-01');
        $monthTo   = date('Y-m-t');

        // === METRIK ===
        $todayLeads = $leads->where('tanggal_mulai <=', $today)
                            ->where('tanggal_selesai >=', $today)
                            ->selectSum('jumlah_leads_masuk', 'total_masuk')
                            ->first()['total_masuk'] ?? 0;

        $monthlyDeals = $leads->where('tanggal_mulai <=', $monthTo)
                              ->where('tanggal_selesai >=', $monthFrom)
                              ->selectSum('jumlah_leads_closing', 'total_close')
                              ->first()['total_close'] ?? 0;

        $commissionsModel = new CommissionsModel();
        $totalCommissionPaid = $commissionsModel
            ->where('status', 'paid')
            ->selectSum('amount', 'total_commission')
            ->first()['total_commission'] ?? 0;

        $monthlyCommissionPaid = $commissionsModel
            ->where('status', 'paid')
            ->where('paid_at >=', $monthFrom . ' 00:00:00')
            ->where('paid_at <=', $monthTo . ' 23:59:59')
            ->selectSum('amount', 'total_commission')
            ->first()['total_commission'] ?? 0;

        // Get top keywords - PERBAIKAN: Ambil dari seo_reports
        $seoReportsModel = new SeoReportsModel();
        
        // Coba dulu ambil berdasarkan perubahan terbesar
        $topKeywords = $seoReportsModel->getTopKeywordsByChange(3);
        
        // Jika tidak ada data, coba ambil berdasarkan posisi terbaik
        if (empty($topKeywords)) {
            $topKeywords = $seoReportsModel->getTopKeywordsByPosition(3);
        }
        
        // If still no data, set as empty array
        if (!$topKeywords) {
            $topKeywords = [];
        }

        $totalLeadsIn = $leads->selectSum('jumlah_leads_masuk', 'total_masuk')
                              ->first()['total_masuk'] ?? 0;

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

        $vpTableExists = (bool) $db->query("SHOW TABLES LIKE 'vendor_profiles'")->getRowArray();

        if ($vpTableExists) {
            $vpFields = $db->getFieldNames('vendor_profiles');

            $hasStatus   = in_array('status',               $vpFields, true);
            $hasIsVerif  = in_array('is_verified',          $vpFields, true);
            $hasReqCom   = in_array('requested_commission', $vpFields, true);
            $hasReqComNom = in_array('requested_commission_nominal', $vpFields, true);
            $hasCommType = in_array('commission_type', $vpFields, true);
            $hasCreated  = in_array('created_at',           $vpFields, true);

            $phoneCandidates = ['phone','telepon','telp','no_telp','nohp','hp','kontak','contact','phone_number','telpon'];
            $waCandidates    = ['whatsapp_number','whatsapp','wa','no_wa','nowa','whatsappno','wa_number'];

            $phoneExpr = $buildCoalesce($phoneCandidates, $vpFields, 'vp', 'phone');
            $waExpr    = $buildCoalesce($waCandidates,    $vpFields, 'vp', 'wa');

            $orderCol = $hasCreated ? 'vp.created_at' : 'vp.id';

            $qb = $db->table('vendor_profiles vp')
                ->select("
                    vp.id,
                    vp.user_id,
                    COALESCE(vp.business_name, '-') AS usaha,
                    COALESCE(vp.owner_name,   '-')  AS pemilik,
                    {$phoneExpr},
                    {$waExpr},
                    ".($hasReqCom? "COALESCE(vp.requested_commission,0)" : "0")." AS komisi,
                    ".($hasReqComNom? "COALESCE(vp.requested_commission_nominal,0)" : "0")." AS komisi_nominal,
                    ".($hasCommType? "COALESCE(vp.commission_type,'percent')" : "'percent'")." AS commission_type,
                    ".($hasStatus? "COALESCE(vp.status,'pending')" : "'pending'")." AS status
                ");

            if ($hasStatus) {
                $qb->where('vp.status', 'pending');
            } else {
                $qb->where('1=1');
            }

            $commissionRequests = $qb->orderBy($orderCol, 'DESC')
                                     ->limit(3)
                                     ->get()->getResultArray();
        }

        if (empty($commissionRequests)) {
            $cmTableExists = (bool) $db->query("SHOW TABLES LIKE 'commissions'")->getRowArray();
            if ($cmTableExists) {
                $cmFields    = $db->getFieldNames('commissions');
                $hasCMCreate = in_array('created_at', $cmFields, true);
                $orderCol2   = $hasCMCreate ? 'c.created_at' : 'c.id';

                $vpFields = $db->getFieldNames('vendor_profiles');
                $phoneCandidates = ['phone','telepon','telp','no_telp','nohp','hp','kontak','contact','phone_number','telpon'];
                $waCandidates    = ['whatsapp_number','whatsapp','wa','no_wa','nowa','whatsappno','wa_number'];
                $phoneExpr = $buildCoalesce($phoneCandidates, $vpFields, 'vp', 'phone');
                $waExpr    = $buildCoalesce($waCandidates,    $vpFields, 'vp', 'wa');
                
                $hasRequestedCommission = in_array('requested_commission', $cmFields, true);
                $commissionExpr = $hasRequestedCommission ? "COALESCE(c.requested_commission, 0)" : "0";

                $commissionRequests = $db->table('commissions c')
                    ->select("
                        c.id,
                        vp.user_id,
                        COALESCE(vp.business_name, '-') AS usaha,
                        COALESCE(vp.owner_name,   '-')  AS pemilik,
                        {$phoneExpr},
                        {$waExpr},
                        {$commissionExpr} AS komisi,
                        0 AS komisi_nominal,
                        'percent' AS commission_type,
                        COALESCE(c.status,'pending') AS status
                    ")
                    ->join('vendor_profiles vp', 'vp.id = c.vendor_id', 'left')
                    ->where('c.status', 'pending')
                    ->orderBy($orderCol2, 'DESC')
                    ->limit(3)
                    ->get()->getResultArray();
            }
        }

        return view('admin/dashboard', array_merge([
            'page'  => 'Dashboard',
            'stats' => [
                'totalVendors' => (int) $totalVendors,
                'todayLeads'   => (int) $todayLeads,
                'monthlyDeals' => (int) $monthlyDeals,
                'totalCommissionPaid' => (float) $totalCommissionPaid,
                'monthlyCommissionPaid' => (float) $monthlyCommissionPaid,
                'topKeywords'  => $topKeywords,
                'totalLeadsIn' => (int) $totalLeadsIn,
                'totalLeadsClosing' => (int) $totalLeadsClosing,
                'leadsToday'   => (int) $todayLeads,
                'totalSeoTeam' => (int) $totalSeoTeam,
            ],
            'commissionRequests' => $commissionRequests,
            'recentLeads' => $this->fetchRecentLeads(),
        ], $commonData));
    }

    public function approveVendorRequest()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID vendor tidak ditemukan'
            ]);
        }

        $vendorModel = new VendorProfilesModel();
        /** @var array|null $vendor */
        $vendor = $vendorModel->find($id);
        
        if (!$vendor) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data vendor tidak ditemukan'
            ]);
        }

        // Update status vendor
        $vendorModel->update($id, ['status' => 'verified']);
        
        // Log activity approve vendor
        $this->logActivity(
            'approve_vendor',
            'Menyetujui pengajuan vendor: ' . $vendor['business_name'],
            ['vendor_id' => $id, 'vendor_name' => $vendor['business_name']]
        );
        
        // Kirim notifikasi ke vendor
        $this->sendVendorStatusNotification($vendor, 'verified');

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Vendor berhasil disetujui'
        ]);
    }

    public function rejectVendorRequest()
    {
        $id = $this->request->getPost('id');
        $reason = $this->request->getPost('reason');
        
        if (!$id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID vendor tidak ditemukan'
            ]);
        }

        if (!$reason) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Alasan penolakan harus diisi'
            ]);
        }

        $vendorModel = new VendorProfilesModel();
        /** @var array|null $vendor */
        $vendor = $vendorModel->find($id);
        
        if (!$vendor) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data vendor tidak ditemukan'
            ]);
        }

        // Update status vendor
        $vendorModel->update($id, ['status' => 'rejected', 'rejection_reason' => $reason]);
        
        // Log activity reject vendor
        $this->logActivity(
            'reject_vendor',
            'Menolak pengajuan vendor: ' . $vendor['business_name'] . ' dengan alasan: ' . $reason,
            ['vendor_id' => $id, 'vendor_name' => $vendor['business_name'], 'reason' => $reason]
        );
        
        // Kirim notifikasi ke vendor
        $this->sendVendorStatusNotification($vendor, 'rejected', $reason);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Vendor berhasil ditolak'
        ]);
    }

    public function stats()
    {
        // Log activity akses stats API
        $this->logActivity(
            'view_stats',
            'Mengakses data statistik dashboard'
        );

        $vendorModel = new VendorProfilesModel();
        $totalVendors = $vendorModel->where('status', 'verified')->countAllResults();

        $seoModel = new SeoProfilesModel();
        $totalSeoTeam = $seoModel->where('status', 'active')->countAllResults();

        $leads     = new LeadsModel();
        $today     = date('Y-m-d');
        $monthFrom = date('Y-m-01');
        $monthTo   = date('Y-m-t');

        $todayLeads = $leads->where('tanggal_mulai <=', $today)
                            ->where('tanggal_selesai >=', $today)
                            ->selectSum('jumlah_leads_masuk', 'total_masuk')
                            ->first()['total_masuk'] ?? 0;

        $monthlyDeals = $leads->where('tanggal_mulai <=', $monthTo)
                              ->where('tanggal_selesai >=', $monthFrom)
                              ->selectSum('jumlah_leads_closing', 'total_close')
                              ->first()['total_close'] ?? 0;

        $commissionsModel = new CommissionsModel();
        $totalCommissionPaid = $commissionsModel
            ->where('status', 'paid')
            ->selectSum('amount', 'total_commission')
            ->first()['total_commission'] ?? 0;

        $monthlyCommissionPaid = $commissionsModel
            ->where('status', 'paid')
            ->where('paid_at >=', $monthFrom . ' 00:00:00')
            ->where('paid_at <=', $monthTo . ' 23:59:59')
            ->selectSum('amount', 'total_commission')
            ->first()['total_commission'] ?? 0;

        $seoReportsModel = new SeoReportsModel();
        
        // Coba dulu ambil berdasarkan perubahan terbesar
        $topKeywords = $seoReportsModel->getTopKeywordsByChange(3);
        
        // Jika tidak ada data, coba ambil berdasarkan posisi terbaik
        if (empty($topKeywords)) {
            $topKeywords = $seoReportsModel->getTopKeywordsByPosition(3);
        }
        
        // If still no data, set as empty array
        if (!$topKeywords) {
            $topKeywords = [];
        }

        return $this->response->setJSON([
            'totalVendors' => (int) $totalVendors,
            'todayLeads'   => (int) $todayLeads,
            'monthlyDeals' => (int) $monthlyDeals,
            'totalCommissionPaid' => (float) $totalCommissionPaid,
            'monthlyCommissionPaid' => (float) $monthlyCommissionPaid,
            'topKeywords'  => $topKeywords,
            'totalSeoTeam' => (int) $totalSeoTeam,
        ]);
    }

    public function getLeadDetail($id)
    {
        // Log activity view detail leads
        $this->logActivity(
            'view_lead_detail',
            'Melihat detail leads dengan ID: ' . $id,
            ['lead_id' => $id]
        );

        $leadsModel = new LeadsModel();
        
        /** @var array|null $lead */
        $lead = $leadsModel
            ->select('leads.*, vendor_profiles.business_name')
            ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
            ->find($id);
            
        if (!$lead) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data leads tidak ditemukan'
            ]);
        }

        $data = [
            'id' => $lead['id'],
            'vendor' => $lead['business_name'] ?? '-',
            'periode' => date('d M Y', strtotime($lead['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($lead['tanggal_selesai'])),
            'leads_masuk' => $lead['jumlah_leads_masuk'],
            'leads_closing' => $lead['jumlah_leads_closing'],
            'reported_by' => $lead['reported_by_vendor'] ?? '-',
            'updated_at' => date('d M Y H:i', strtotime($lead['updated_at'])),
        ];
        
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $data
        ]);
    }

    private function fetchRecentLeads(): array
    {
        // Log activity fetch recent leads
        $this->logActivity(
            'view_recent_leads',
            'Mengambil data leads terbaru'
        );

        $leadsModel = new LeadsModel();

        $rows = $leadsModel
            ->select('leads.id, leads.vendor_id, leads.jumlah_leads_masuk, leads.jumlah_leads_closing, leads.tanggal_mulai, leads.tanggal_selesai, leads.updated_at, COALESCE(vendor_profiles.business_name, "-") AS business_name')
            ->join('vendor_profiles', 'leads.vendor_id = vendor_profiles.id', 'left')
            ->orderBy('leads.id', 'DESC')
            ->limit(10)
            ->findAll();

        return array_map(static function ($r) {
            return [
                'id_leads'      => isset($r['id']) ? (string)$r['id'] : '-',
                'vendor_id'     => isset($r['vendor_id']) ? (string)$r['vendor_id'] : '-',
                'business_name' => !empty($r['business_name']) ? $r['business_name'] : '-',
                'layanan'       => '-',
                'masuk'         => isset($r['jumlah_leads_masuk']) ? (int)$r['jumlah_leads_masuk'] : 0,
                'diproses'      => 0,
                'ditolak'       => 0,
                'closing'       => isset($r['jumlah_leads_closing']) ? (int)$r['jumlah_leads_closing'] : 0,
                'tanggal_mulai' => $r['tanggal_mulai'] ?? '-',
                'tanggal_selesai' => $r['tanggal_selesai'] ?? '-',
                'updated_at'    => $r['updated_at'] ?? '-',
                'detail_url'    => isset($r['id']) ? site_url('admin/leads/'.$r['id']) : '#',
            ];
        }, $rows ?? []);
    }

    /**
     * Simpan activity log
     */
    private function logActivity($action, $description, $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            
            $data = [
                'user_id'     => $user ? $user->id : null,
                'module'      => 'dashboard',
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => (string) $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Dashboard: ' . $e->getMessage());
        }
    }
    
    /**
     * Kirim notifikasi status vendor ke vendor
     */
    private function sendVendorStatusNotification($vendorData, $status, $reason = null)
    {
        try {
            $db = \Config\Database::connect();
            
            $vendorName = $vendorData['business_name'] ?? 'Vendor Tidak Dikenal';
            $vendorUserId = $vendorData['user_id'] ?? null;
            
            if (!$vendorUserId) {
                log_message('error', 'Vendor user_id tidak ditemukan untuk notifikasi');
                return;
            }

            // Tentukan pesan berdasarkan status
            $title = '';
            $message = '';
            
            switch ($status) {
                case 'verified':
                    $title = 'Verifikasi Vendor Diterima';
                    $message = "Selamat! Vendor {$vendorName} telah diverifikasi dan aktif.";
                    break;
                    
                case 'rejected':
                    $title = 'Verifikasi Vendor Ditolak';
                    $message = "Maaf, vendor {$vendorName} ditolak.";
                    if ($reason) {
                        $message .= " Alasan: {$reason}";
                    }
                    break;
                    
                default:
                    return; // Tidak kirim notifikasi untuk status lain
            }

            // Kirim notifikasi ke vendor
            $db->table('notifications')->insert([
                'user_id' => $vendorUserId,
                'vendor_id' => $vendorData['id'] ?? null, // vendor_profiles.id
                'seo_id' => null,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            log_message('info', "Notifikasi status vendor berhasil dikirim: {$vendorName} - {$status}");

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi status vendor: ' . $e->getMessage());
        }
    }
}