<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;
use App\Models\LeadsModel;
use App\Models\ServicesModel;
use App\Models\NotificationsModel;

class Dashboard extends BaseController
{
    private function requireVendorId(): ?int
    {
        $auth = service('auth');
        $user = $auth->user();

        $vp = (new VendorProfilesModel())
            ->select('id')
            ->where('user_id', (int) $user->id)
            ->first();

        if (! $vp || empty($vp['id'])) {
            // hentikan langsung, kirim redirect
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
        if (! $vendorId) {
            return; // redirect sudah dikirim
        }

        $db = db_connect();

        // ===== KARTU STAT =====
        $leadsModel = new LeadsModel();

        $leadsNew = (clone $leadsModel)
            ->where(['vendor_id' => $vendorId, 'status' => 'new'])
            ->countAllResults();

        $leadsInProgress = (clone $leadsModel)
            ->where(['vendor_id' => $vendorId, 'status' => 'in_progress'])
            ->countAllResults();

        // ===== JUMLAH KEYWORD + TOP KEYWORDS =====
        $keywordsTotal = 0;
        $topKeywords   = [];

        if ($db->tableExists('seo_keyword_targets')) {
            try {
                $keywordsTotal = (int) $db->table('seo_keyword_targets')
                    ->where('vendor_id', $vendorId)
                    ->countAllResults();
            } catch (\Throwable $e) {
                $keywordsTotal = 0;
            }

            if ($db->tableExists('seo_reports')) {
                try {
                    $topKeywords = $db->table('seo_reports r')
                        ->select('t.id AS id, t.keyword AS text, r.position, r.change')
                        ->join('seo_keyword_targets t', 't.id = r.keyword_id', 'left')
                        ->where('r.vendor_id', $vendorId)
                        ->orderBy('r.position', 'ASC')
                        ->limit(6)
                        ->get()->getResultArray();
                } catch (\Throwable $e) {
                    $topKeywords = [];
                }
            } else {
                // fallback kalau table reports belum ada
                try {
                    $rows = $db->table('seo_keyword_targets')
                        ->select('id, keyword AS text')
                        ->where('vendor_id', $vendorId)
                        ->orderBy('id', 'DESC')
                        ->limit(6)
                        ->get()->getResultArray();

                    $topKeywords = array_map(fn ($r) => $r + [
                        'position' => null,
                        'change'   => null
                    ], $rows);
                } catch (\Throwable $e) {
                    $topKeywords = [];
                }
            }
        }

        // ===== LEADS TERBARU =====
        $recentRows = (clone $leadsModel)
            ->where(['vendor_id' => $vendorId, 'reported_by_vendor' => 1])
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->findAll();

        // Ambil nama service untuk leads
        $serviceNames = [];
        if ($recentRows) {
            $serviceIds = array_unique(array_filter(array_column($recentRows, 'service_id')));
            if (! empty($serviceIds)) {
                $svcRows = (new ServicesModel())
                    ->select('id,name')
                    ->whereIn('id', $serviceIds)
                    ->findAll();
                foreach ($svcRows as $r) {
                    $serviceNames[(int) $r['id']] = $r['name'];
                }
            }
        }

        $recentLeads = array_map(function ($l) use ($serviceNames) {
            $dt = $l['updated_at'] ?? $l['assigned_at'] ?? null;
            return [
                'id'       => (int) ($l['id'] ?? 0),
                'customer' => (string) ($l['customer_name'] ?? ''),
                'phone'    => (string) ($l['customer_phone'] ?? ''),
                'source'   => (string) ($l['source'] ?? ''),
                'project'  => $serviceNames[(int) ($l['service_id'] ?? 0)] ?? '-',
                'date'     => $dt ? date('Y-m-d H:i', strtotime($dt)) : '-',
                'status'   => (string) ($l['status'] ?? 'new'),
            ];
        }, $recentRows ?? []);

        // ===== NOTIFIKASI =====
        $notifications = [];
        $unreadNotif   = 0;
        $userId        = service('auth')->user()->id;

        if ($db->tableExists('notifications')) {
            try {
                $rows = $db->table('notifications')
                    ->where('user_id', $userId) // pastikan notifikasi untuk user
                    ->orderBy('created_at', 'DESC')
                    ->limit(10)
                    ->get()->getResultArray();

                foreach ($rows as $r) {
                    $notifications[] = [
                        'id'      => $r['id'],
                        'title'   => $r['title'],
                        'message' => $r['message'],
                        'is_read' => (bool) ($r['is_read'] ?? 0),
                        'date'    => !empty($r['created_at']) ? date('Y-m-d H:i', strtotime($r['created_at'])) : '-',
                    ];
                }

                $unreadNotif = (int) $db->table('notifications')
                    ->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
            } catch (\Throwable $e) {
                $notifications = [];
                $unreadNotif = 0;
            }
        }

        // Render view
        return view('vendoruser/dashboard', [
            'page'         => 'Dashboard',
            'stats'        => [
                'leads_new'        => (int) $leadsNew,
                'leads_inprogress' => (int) $leadsInProgress,
                'keywords_total'   => (int) $keywordsTotal,
                'unread'           => (int) $unreadNotif,
            ],
            'recentLeads'   => $recentLeads,
            'topKeywords'   => $topKeywords,
            'notifications' => $notifications,
        ]);
    }
}
