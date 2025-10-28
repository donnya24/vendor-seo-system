<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\SeoReportsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Reports extends BaseAdminController
{
    protected $reportsModel;
    protected $vendorModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->reportsModel = new SeoReportsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        // Log activity akses halaman reports
        $this->logActivity(
            'view_reports',
            'Mengakses halaman laporan SEO'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();
        
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $position = $this->request->getGet('position');
        $changeFilter = $this->request->getGet('change_filter'); // Tambahkan filter perubahan

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        // Get reports from the reports table, not targets table
        $reports = $this->reportsModel->getWithVendor($vendorId, $position, $changeFilter);

        // Log aktivitas view reports dengan filter
        $logDescription = "Melihat laporan SEO completed (Admin)";
        $extraData = [
            'module' => 'admin_reports',
            'role' => 'admin'
        ];
        
        if (!empty($vendorId)) {
            $vendorName = $this->getVendorName($vendorId, $vendors);
            $logDescription .= " untuk vendor {$vendorName}";
            $extraData['vendor_id'] = $vendorId;
            $extraData['vendor_name'] = $vendorName;
        } else {
            $logDescription .= " semua vendor";
        }
        
        if (!empty($position)) {
            $positionLabel = $this->getPositionLabel($position);
            $logDescription .= " dengan posisi {$positionLabel}";
            $extraData['position_filter'] = $position;
            $extraData['position_label'] = $positionLabel;
        }
        
        if (!empty($changeFilter)) {
            $changeLabel = $this->getChangeFilterLabel($changeFilter);
            $logDescription .= " dengan perubahan {$changeLabel}";
            $extraData['change_filter'] = $changeFilter;
            $extraData['change_label'] = $changeLabel;
        }

        // Log activity
        $this->logActivity(
            'view_reports_with_filter',
            $logDescription,
            $extraData
        );

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/reports/index', array_merge([
            'title'      => 'Laporan SEO - Admin',
            'reports'    => $reports,
            'vendorId'   => $vendorId,
            'position'   => $position,
            'changeFilter' => $changeFilter, // Tambahkan ke view
            'vendors'    => $vendors,
        ], $commonData));
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

    /**
     * Log activity untuk admin
     */
    private function logActivity($action, $description, $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            
            $data = [
                'user_id'     => $user ? $user->id : null,
                'module'      => 'admin_reports',
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
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity in Reports: ' . $e->getMessage());
        }
    }

    public function exportCsv()
    {
        // Log activity export
        $this->logActivity(
            'export_reports_csv',
            'Mengekspor data laporan SEO ke CSV'
        );

        // Get filter parameters
        $vendorId = $this->request->getGet('vendor_id');
        $position = $this->request->getGet('position');
        $changeFilter = $this->request->getGet('change_filter'); // Tambahkan filter perubahan

        // Get reports from the reports table, not targets table
        $reports = $this->reportsModel->getWithVendor($vendorId, $position, $changeFilter);

        if (empty($reports)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        // Set headers untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="seo_reports_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM untuk UTF-8
        fputs($output, "\xEF\xBB\xBF");

        // Header CSV - PERBAIKAN: Urutannya menjadi Posisi | Target | Perubahan
        $headers = [
            'No',
            'Vendor',
            'Project',
            'Keyword',
            'Posisi',
            'Target',
            'Perubahan',
            'Trend',
            'Tanggal'
        ];
        fputcsv($output, $headers);

        // Data rows - PERBAIKAN: Urutannya menjadi Posisi | Target | Perubahan
        $no = 1;
        foreach ($reports as $report) {
            $trendText = '';
            if ($report['trend'] === 'up') {
                $trendText = 'Naik';
            } elseif ($report['trend'] === 'down') {
                $trendText = 'Turun';
            } elseif ($report['trend'] === 'stable') {
                $trendText = 'Stabil';
            } else {
                $trendText = 'Tidak Diketahui';
            }

            $row = [
                $no++,
                $report['vendor_name'] ?? '-',
                $report['project'] ?? '-',
                $report['keyword'] ?? '-',
                $report['position'] ?? '-',        // Posisi
                $report['target_position'] ?? '-', // Target
                $report['change'] !== null ? $report['change'] : '-', // Perubahan
                $trendText,
                $report['created_at'] ? date('d/m/Y', strtotime($report['created_at'])) : '-'
            ];
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}