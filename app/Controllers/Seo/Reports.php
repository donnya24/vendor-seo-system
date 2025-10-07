<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\VendorProfilesModel;

class Reports extends BaseController
{
    protected $targetModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->targetModel = new SeoKeywordTargetsModel();
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $position = $this->request->getGet('position');

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        $builder = $this->targetModel
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left')
            ->where('seo_keyword_targets.status', 'completed');

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('seo_keyword_targets.vendor_id', $vendorId);
        }

        // Filter posisi jika dipilih
        if (!empty($position)) {
            if ($position === 'top3') {
                $builder->where('seo_keyword_targets.current_position <=', 3);
            } elseif ($position === 'top10') {
                $builder->where('seo_keyword_targets.current_position <=', 10)
                        ->where('seo_keyword_targets.current_position >', 3);
            } elseif ($position === 'top20') {
                $builder->where('seo_keyword_targets.current_position <=', 20)
                        ->where('seo_keyword_targets.current_position >', 10);
            } elseif ($position === 'below20') {
                $builder->where('seo_keyword_targets.current_position >', 20);
            }
        }

        $targets = $builder->orderBy('seo_keyword_targets.updated_at', 'DESC')
            ->findAll();

        // Hitung perubahan otomatis
        foreach ($targets as &$t) {
            $cur = (int)($t['current_position'] ?? 0);
            $tar = (int)($t['target_position'] ?? 0);

            if ($cur && $tar) {
                $t['change'] = $cur - $tar; // current - target
                // Tentukan trend
                if ($t['change'] > 0) {
                    $t['trend'] = 'up';
                } elseif ($t['change'] < 0) {
                    $t['trend'] = 'down';
                } else {
                    $t['trend'] = 'stable';
                }
            } else {
                $t['change'] = null;
                $t['trend'] = 'unknown';
            }
        }

        // Log aktivitas view reports
        $logDescription = "Melihat laporan SEO completed";
        $extraData = ['module' => 'reports'];
        
        if (!empty($vendorId)) {
            $vendorName = $this->getVendorName($vendorId, $vendors);
            $logDescription .= " untuk vendor {$vendorName}";
            $extraData['vendor_id'] = $vendorId;
        } else {
            $logDescription .= " semua vendor";
        }
        
        if (!empty($position)) {
            $positionLabel = $this->getPositionLabel($position);
            $logDescription .= " dengan posisi {$positionLabel}";
            $extraData['position_filter'] = $position;
        }

        log_activity_auto('view', $logDescription, $extraData);

        return view('seo/reports/index', [
            'title'      => 'Laporan SEO',
            'activeMenu' => 'reports',
            'reports'    => $targets,
            'vendorId'   => $vendorId,
            'position'   => $position,
            'vendors'    => $vendors,
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

    /**
     * Helper untuk mendapatkan label posisi
     */
    private function getPositionLabel(string $position): string
    {
        $labels = [
            'top3' => 'Top 3',
            'top10' => 'Top 10',
            'top20' => 'Top 20',
            'below20' => 'Dibawah 20'
        ];
        return $labels[$position] ?? $position;
    }
}