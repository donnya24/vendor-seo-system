<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\ServicesModel;
use App\Models\ActivityLogsModel;

class Dashboard extends BaseController
{
    private $activityLogsModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
    }

    private function requireVendorId(): int
    {
        $auth = service('auth');
        $user = $auth->user();

        $vp = (new VendorProfilesModel())
            ->where('user_id', (int)$user->id)
            ->first();

        if (! $vp || empty($vp['id'])) {
            redirect()
                ->to(site_url('vendoruser/profile'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.')
                ->send();
            exit;
        }

        return (int) $vp['id'];
    }

    public function index()
    {
        $vendorId = $this->requireVendorId();
        $authUser = service('auth')->user();
        $db       = db_connect();

        // Log activity untuk mengakses dashboard
        $this->logActivity($authUser->id, $vendorId, 'view_dashboard', 'success', 'Mengakses dashboard vendor');

        $leadsModel = new LeadsModel();
        $today = date('Y-m-d');

        // Statistik Leads
        $leadsNew = (clone $leadsModel)
            ->selectSum('jumlah_leads_masuk')
            ->where('vendor_id', $vendorId)
            ->get()->getRow('jumlah_leads_masuk') ?? 0;

        $leadsClosing = (clone $leadsModel)
            ->selectSum('jumlah_leads_closing')
            ->where('vendor_id', $vendorId)
            ->get()->getRow('jumlah_leads_closing') ?? 0;

        $leadsToday = (clone $leadsModel)
            ->selectSum('jumlah_leads_masuk')
            ->where('vendor_id', $vendorId)
            ->where('DATE(tanggal)', $today)
            ->get()->getRow('jumlah_leads_masuk') ?? 0;

        $leadsClosingToday = (clone $leadsModel)
            ->selectSum('jumlah_leads_closing')
            ->where('vendor_id', $vendorId)
            ->where('DATE(tanggal)', $today)
            ->get()->getRow('jumlah_leads_closing') ?? 0;

        // Jumlah keyword
        $keywordsTotal = 0;
        if ($db->tableExists('seo_keyword_targets')) {
            $keywordsTotal = (int) $db->table('seo_keyword_targets')
                ->where('vendor_id', $vendorId)
                ->countAllResults();
        }

        // Top Keywords
        $topKeywords = [];
        if ($db->tableExists('seo_reports')) {
            $topKeywords = $db->table('seo_reports')
                ->select('id, keyword AS text, project, position, `change`')
                ->where('vendor_id', $vendorId)
                ->orderBy('position', 'ASC')
                ->limit(6)
                ->get()->getResultArray();
        }

        // Leads terbaru
        $recentRows = (clone $leadsModel)
            ->where(['vendor_id' => $vendorId, 'reported_by_vendor' => 1])
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->findAll();

        $recentLeads = array_map(function ($l) {
            return [
                'id'      => (int) ($l['id'] ?? 0),
                'project' => '-',
                'masuk'   => (int) ($l['jumlah_leads_masuk'] ?? 0),
                'closing' => (int) ($l['jumlah_leads_closing'] ?? 0),
                'tanggal' => !empty($l['tanggal']) ? date('Y-m-d', strtotime($l['tanggal'])) : '-',
                'updated' => !empty($l['updated_at']) ? date('Y-m-d H:i', strtotime($l['updated_at'])) : '-',
            ];
        }, $recentRows ?? []);

        // Profil vendor (untuk header/sidebar)
        $vp = (new VendorProfilesModel())
            ->where('user_id', (int)$authUser->id)
            ->first();

        $profileImage = $vp['profile_image'] ?? '';
        $isVerified   = ($vp['status'] ?? '') === 'verified';

        // Activity logs ringkas
        $activityLogs = [];
        if ($db->tableExists('activity_logs')) {
            $activityLogs = (new ActivityLogsModel())
                ->where('vendor_id', $vendorId)
                ->orderBy('created_at', 'DESC')
                ->limit(10)
                ->findAll();
        }

        // ⬇️ Render via layout master. Tidak kirim notifikasi/modals di sini.
        return view('vendoruser/layouts/vendor_master', [
            'title'        => 'Dashboard',
            'vp'           => $vp,
            'isVerified'   => $isVerified,
            'profileImage' => $profileImage,

            'content_view' => 'vendoruser/dashboard',
            'content_data' => [
                'page'          => 'Dashboard',
                'stats'         => [
                    'leads_new'           => (int)$leadsNew,
                    'leads_closing'       => (int)$leadsClosing,
                    'leads_today'         => (int)$leadsToday,
                    'leads_closing_today' => (int)$leadsClosingToday,
                    'keywords_total'      => (int)$keywordsTotal,
                ],
                'recentLeads'   => $recentLeads,
                'topKeywords'   => $topKeywords,
                'activityLogs'  => $activityLogs,
            ],
        ]);
    }

    // ===== LOG ACTIVITY METHOD =====
    private function logActivity($userId = null, $vendorId = null, $action, $status, $description = null, $additionalData = [])
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
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            
            // Gabungkan dengan data tambahan jika ada
            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }
            
            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Dashboard: ' . $e->getMessage());
        }
    }
}