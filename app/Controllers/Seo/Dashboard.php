<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\LeadsModel;
use App\Models\CommissionsModel;
use App\Models\ActivityLogsModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $vendorId = $this->request->getGet('vendor_id') 
            ?? session()->get('vendor_id') 
            ?? 1;

        // Simpan vendor_id di session supaya konsisten
        session()->set('vendor_id', $vendorId);

        $start = $this->request->getGet('start') ?? date('Y-m-01');
        $end   = $this->request->getGet('end')   ?? date('Y-m-t');

<<<<<<< HEAD
        // Ambil keyword targets + latest report
        $targets = (new SeoKeywordTargetsModel())
            ->withLatestReport() // tanpa argumen, cukup latest saja
            ->where('seo_keyword_targets.vendor_id', $vendorId)
            ->findAll();

        // Hitung perubahan posisi terhadap target
=======
        $targets = (new SeoKeywordTargetsModel())
            ->withLatestReport($start, $end)
            ->where('seo_keyword_targets.vendor_id', $vendorId)
            ->findAll();

        // hitung perubahan
>>>>>>> 869b4bc627c145c1f2490a07683852c604bf0f32
        foreach ($targets as &$t) {
            $current = (int)($t['current_position'] ?? 0);
            $target  = (int)($t['target_position'] ?? 0);
            $status  = $t['status'] ?? 'pending';

<<<<<<< HEAD
            $t['last_change'] = ($status === 'completed' && $current && $target)
                ? $current - $target
                : null;
        }

        // Statistik leads (gunakan periode filter)
=======
            if ($status === 'completed' && $current && $target) {
                $t['last_change'] = $current - $target;
            } else {
                $t['last_change'] = null;
            }
        }

        // Statistik leads
>>>>>>> 869b4bc627c145c1f2490a07683852c604bf0f32
        $leadStats = (new LeadsModel())->getDashboardStats(
            $vendorId,
            "{$start} 00:00:00",
            "{$end} 23:59:59"
        ) ?? ['total' => 0, 'closed' => 0];

        // Ambil total komisi untuk periode filter
        $commission = (new CommissionsModel())
            ->select('COALESCE(SUM(amount),0) as total_amount')
            ->where('vendor_id', $vendorId)
            ->where('period_start >=', $start)
            ->where('period_end <=', $end)
            ->first();

<<<<<<< HEAD
        // Ambil status komisi terbaru
=======
>>>>>>> 869b4bc627c145c1f2490a07683852c604bf0f32
        $status = (new CommissionsModel())
            ->select('status')
            ->where('vendor_id', $vendorId)
            ->where('period_start >=', $start)
            ->where('period_end <=', $end)
            ->orderBy('updated_at', 'DESC')
            ->get()
            ->getRow('status');

        // Catat log activity
        $this->logActivity(
            $vendorId,
            'dashboard',
            'view',
            "Membuka dashboard periode {$start} - {$end}"
        );

        return view('seo/dashboard', [
            'title'      => 'SEO Dashboard',
            'activeMenu' => 'dashboard',
            'vendorId'   => $vendorId,
            'targets'    => $targets,
            'leadStats'  => $leadStats,
            'commission' => $commission,
            'status'     => $status,
            'start'      => $start,
            'end'        => $end,
        ]);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        (new ActivityLogsModel())->insert([
            'user_id'    => session()->get('user_id'),
            'vendor_id'  => $vendorId,
            'module'     => $module,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
