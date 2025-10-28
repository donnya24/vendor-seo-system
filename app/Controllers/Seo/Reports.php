<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoReportsModel;
use App\Models\VendorProfilesModel;

class Reports extends BaseController
{
    protected $reportsModel;
    protected $vendorModel;

    public function __construct()
    {
        $this->reportsModel = new SeoReportsModel();
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $position = $this->request->getGet('position');
        $changeFilter = $this->request->getGet('change_filter'); // Tambahkan filter perubahan

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        // Get reports from the reports table, not targets table
        $reports = $this->reportsModel->getWithVendor($vendorId, $position, $changeFilter);

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
        
        if (!empty($changeFilter)) {
            $changeLabel = $this->getChangeFilterLabel($changeFilter);
            $logDescription .= " dengan perubahan {$changeLabel}";
            $extraData['change_filter'] = $changeFilter;
        }

        log_activity_auto('view', $logDescription, $extraData);

        return view('seo/reports/index', [
            'title'      => 'Laporan SEO',
            'activeMenu' => 'reports',
            'reports'    => $reports,
            'vendorId'   => $vendorId,
            'position'   => $position,
            'changeFilter' => $changeFilter, // Tambahkan ke view
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

    /**
     * Helper untuk mendapatkan label filter perubahan
     */
    private function getChangeFilterLabel(string $changeFilter): string
    {
        $labels = [
            'high_improvement' => 'Peningkatan Tinggi (+10 atau lebih)',
            'moderate_improvement' => 'Peningkatan Sedang (+5 hingga +9)',
            'low_improvement' => 'Peningkatan Rendah (+1 hingga +4)',
            'no_change' => 'Tidak Ada Perubahan',
            'decline' => 'Penurunan'
        ];
        return $labels[$changeFilter] ?? $changeFilter;
    }
}