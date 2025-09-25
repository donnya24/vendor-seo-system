<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\ActivityLogsModel;

class Dashboard extends BaseController
{
    private $activityLogsModel;
    private $vendorProfile;
    private $vendorId;
    private $authUser;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
    }

    /**
     * Inisialisasi vendor dari user login
     */
    private function initVendor(): bool
    {
        $this->authUser = service('auth')->user();

        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int) $this->authUser->id)
            ->first();

        if (! $this->vendorProfile || empty($this->vendorProfile['id'])) {
            redirect()
                ->to(site_url('vendoruser/profile'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dahulu.')
                ->send();
            exit;
        }

        $this->vendorId = (int) $this->vendorProfile['id'];
        return true;
    }

    public function index()
    {
        $this->initVendor();
        $db = db_connect();
        $today = date('Y-m-d');

        // Log activity akses dashboard
        $this->logActivity(
            $this->authUser->id,
            $this->vendorId,
            'view_dashboard',
            'success',
            'Mengakses dashboard vendor'
        );

        $leadsModel = new LeadsModel();

        // ===== Statistik Leads =====
        $leadsNew = (clone $leadsModel)
            ->selectSum('jumlah_leads_masuk')
            ->where('vendor_id', $this->vendorId)
            ->get()->getRow('jumlah_leads_masuk') ?? 0;

        $leadsClosing = (clone $leadsModel)
            ->selectSum('jumlah_leads_closing')
            ->where('vendor_id', $this->vendorId)
            ->get()->getRow('jumlah_leads_closing') ?? 0;

        $leadsToday = (clone $leadsModel)
            ->selectSum('jumlah_leads_masuk')
            ->where('vendor_id', $this->vendorId)
            ->where('DATE(tanggal)', $today)
            ->get()->getRow('jumlah_leads_masuk') ?? 0;

        $leadsClosingToday = (clone $leadsModel)
            ->selectSum('jumlah_leads_closing')
            ->where('vendor_id', $this->vendorId)
            ->where('DATE(tanggal)', $today)
            ->get()->getRow('jumlah_leads_closing') ?? 0;

        // ===== Leads terbaru =====
        $recentRows = (clone $leadsModel)
            ->where('vendor_id', $this->vendorId)
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->findAll();

        $recentLeads = array_map(function ($l) {
            return [
                'id'      => (int) ($l['id'] ?? 0),
                'project' => $l['project'] ?? '-',
                'masuk'   => (int) ($l['jumlah_leads_masuk'] ?? 0),
                'closing' => (int) ($l['jumlah_leads_closing'] ?? 0),
                'tanggal' => !empty($l['tanggal']) ? date('Y-m-d', strtotime($l['tanggal'])) : '-',
                'updated' => !empty($l['updated_at']) ? date('Y-m-d H:i', strtotime($l['updated_at'])) : '-',
            ];
        }, $recentRows ?? []);

        // ===== Jumlah keyword =====
        $keywordsTotal = 0;
        if ($db->tableExists('seo_keyword_targets')) {
            $keywordsTotal = (int) $db->table('seo_keyword_targets')
                ->where('vendor_id', $this->vendorId)
                ->countAllResults();
        }

        // ===== Top Keywords =====
        $topKeywords = [];
        if ($db->tableExists('seo_reports')) {
            $topKeywords = $db->table('seo_reports')
                ->select('id, keyword AS text, project, position, `change`')
                ->where('vendor_id', $this->vendorId)
                ->orderBy('position', 'ASC')
                ->limit(6)
                ->get()->getResultArray();
        }

        // ===== Activity Logs ringkas =====
        $activityLogs = [];
        if ($db->tableExists('activity_logs')) {
            $activityLogs = (new ActivityLogsModel())
                ->where('vendor_id', $this->vendorId)
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->findAll();
        }

        // render ke master layout
        return view('vendoruser/layouts/vendor_master', [
            'title'        => 'Dashboard',
            'vp'           => $this->vendorProfile,
            'isVerified'   => ($this->vendorProfile['status'] ?? '') === 'verified',
            'profileImage' => $this->vendorProfile['profile_image'] ?? '',

            'content_view' => 'vendoruser/dashboard',
            'content_data' => [
                'page'          => 'Dashboard',
                'stats'         => [
                    'leads_new'           => (int) $leadsNew,
                    'leads_closing'       => (int) $leadsClosing,
                    'leads_today'         => (int) $leadsToday,
                    'leads_closing_today' => (int) $leadsClosingToday,
                    'keywords_total'      => (int) $keywordsTotal,
                ],
                'recentLeads'   => $recentLeads,
                'topKeywords'   => $topKeywords,
                'activityLogs'  => $activityLogs,
            ],
        ]);
    }

    /**
     * Simpan activity log
     */
    private function logActivity($userId, $vendorId, $action, $status, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'vendor_id'   => $vendorId,
                'module'      => 'dashboard',
                'action'      => $action,
                'status'      => $status,
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
}