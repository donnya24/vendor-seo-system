<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\ServicesModel;

class Dashboard extends BaseController
{
    private function requireVendorId(): int
    {
        $auth = service('auth');
        $user = $auth->user();

        $vp = (new VendorProfilesModel())
            ->select('id')
            ->where('user_id', (int) $user->id)
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

        // ===== KARTU STAT =====
        $leadsModel = new LeadsModel();

        // total leads masuk
        $leadsNew = (clone $leadsModel)
            ->selectSum('jumlah_leads_masuk')
            ->where('vendor_id', $vendorId)
            ->get()->getRow('jumlah_leads_masuk') ?? 0;

        // total leads diproses
        $leadsInProgress = (clone $leadsModel)
            ->selectSum('jumlah_leads_diproses')
            ->where('vendor_id', $vendorId)
            ->get()->getRow('jumlah_leads_diproses') ?? 0;

        // Jumlah keyword: dari seo_keyword_targets
        $keywordsTotal = 0;
        if ($db->tableExists('seo_keyword_targets')) {
            $keywordsTotal = (int) $db->table('seo_keyword_targets')
                ->where('vendor_id', $vendorId)
                ->countAllResults();
        }

        // ===== TOP KEYWORDS =====
        $topKeywords = [];
        if ($db->tableExists('seo_reports')) {
            $topKeywords = $db->table('seo_reports')
                ->select('id, keyword AS text, project, position, `change`')
                ->where('vendor_id', $vendorId)
                ->orderBy('position', 'ASC')
                ->limit(6)
                ->get()->getResultArray();
        } elseif ($db->tableExists('seo_keyword_targets')) {
            $rows = $db->table('seo_keyword_targets')
                ->select('id, keyword AS text, project')
                ->where('vendor_id', $vendorId)
                ->orderBy('id', 'DESC')
                ->limit(6)
                ->get()->getResultArray();
            $topKeywords = array_map(fn($r)=> $r + ['position'=>null,'change'=>null], $rows);
        }

        // ===== LEADS TERBARU =====
        $recentRows = (clone $leadsModel)
            ->where(['vendor_id' => $vendorId, 'reported_by_vendor' => 1])
            ->orderBy('id', 'DESC')
            ->limit(10)
            ->findAll();

        // Map service name
        $serviceNames = [];
        if ($recentRows) {
            $serviceIds = array_unique(array_filter(array_column($recentRows, 'service_id')));
            if ($serviceIds) {
                $svcRows = (new ServicesModel())
                    ->select('id, name')
                    ->whereIn('id', $serviceIds)
                    ->findAll();
                foreach ($svcRows as $r) {
                    $serviceNames[(int) $r['id']] = $r['name'];
                }
            }
        }

        // Mapping leads agar sesuai dengan tabel leads
        $recentLeads = array_map(function ($l) use ($serviceNames) {
            return [
                'id'       => (int) ($l['id'] ?? 0),
                'project'  => $serviceNames[(int) ($l['service_id'] ?? 0)] ?? '-',
                'masuk'    => (int) ($l['jumlah_leads_masuk'] ?? 0),
                'diproses' => (int) ($l['jumlah_leads_diproses'] ?? 0),
                'ditolak'  => (int) ($l['jumlah_leads_ditolak'] ?? 0),
                'closing'  => (int) ($l['jumlah_leads_closing'] ?? 0),
                'tanggal'  => !empty($l['tanggal']) ? date('Y-m-d', strtotime($l['tanggal'])) : '-',
                'updated'  => !empty($l['updated_at']) ? date('Y-m-d H:i', strtotime($l['updated_at'])) : '-',
            ];
        }, $recentRows ?? []);

        // ===== NOTIFIKASI =====
        $notifications = [];
        $unreadNotif   = 0;
        if ($db->tableExists('notifications')) {
            $rows = $db->table('notifications')
                ->where('user_id', (int)$authUser->id)
                ->orderBy('created_at', 'DESC')
                ->limit(20)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $notifications[] = [
                    'id'      => (int)$r['id'],
                    'title'   => (string)$r['title'],
                    'message' => (string)$r['message'],
                    'is_read' => (bool)($r['is_read'] ?? 0),
                    'date'    => !empty($r['created_at']) ? date('Y-m-d H:i', strtotime($r['created_at'])) : '-',
                ];
            }

            $unreadNotif = (int)$db->table('notifications')
                ->where('user_id', (int)$authUser->id)
                ->where('is_read', 0)
                ->countAllResults();
        }

        // Profil vendor
        $vp = (new VendorProfilesModel())
            ->where('user_id', (int)$authUser->id)
            ->first();

        $profileImage = $vp['profile_image'] ?? '';

        return view('vendoruser/dashboard', [
            'page'          => 'Dashboard',
            'stats'         => [
                'leads_new'        => (int)$leadsNew,
                'leads_inprogress' => (int)$leadsInProgress,
                'keywords_total'   => (int)$keywordsTotal,
                'unread'           => (int)$unreadNotif,
            ],
            'recentLeads'   => $recentLeads,
            'topKeywords'   => $topKeywords,
            'notifications' => $notifications,
            'vp'            => $vp,
            'profileImage'  => $profileImage,
        ]);
    }
}
