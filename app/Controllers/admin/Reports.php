<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController; // Perbaikan: Extend BaseAdminController
use App\Models\SeoKeywordTargetsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel; // Tambahkan model ActivityLogs

class Reports extends BaseAdminController // Perbaikan: Extend BaseAdminController
{
    protected $targetModel;
    protected $vendorModel;
    protected $activityLogsModel; // Tambahkan property

    public function __construct()
    {
        // Hapus parent::__construct() karena BaseController tidak memiliki constructor
        $this->targetModel = new SeoKeywordTargetsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel(); // Inisialisasi model
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

        $reports = $builder->orderBy('seo_keyword_targets.updated_at', 'DESC')
            ->findAll();

        // Hitung perubahan otomatis
        foreach ($reports as &$r) {
            $cur = (int)($r['current_position'] ?? 0);
            $tar = (int)($r['target_position'] ?? 0);

            if ($cur && $tar) {
                $r['change'] = $cur - $tar; // current - target
                // Tentukan trend
                if ($r['change'] > 0) {
                    $r['trend'] = 'up';
                } elseif ($r['change'] < 0) {
                    $r['trend'] = 'down';
                } else {
                    $r['trend'] = 'stable';
                }
            } else {
                $r['change'] = null;
                $r['trend'] = 'unknown';
            }
        }

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

        // Build query
        $builder = $this->targetModel
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left')
            ->where('seo_keyword_targets.status', 'completed');

        if (!empty($vendorId)) {
            $builder->where('seo_keyword_targets.vendor_id', $vendorId);
        }

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

        $reports = $builder->orderBy('seo_keyword_targets.updated_at', 'DESC')
            ->findAll();

        if (empty($reports)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        // Hitung perubahan otomatis
        foreach ($reports as &$r) {
            $cur = (int)($r['current_position'] ?? 0);
            $tar = (int)($r['target_position'] ?? 0);

            if ($cur && $tar) {
                $r['change'] = $cur - $tar; // current - target
                // Tentukan trend
                if ($r['change'] > 0) {
                    $r['trend'] = 'up';
                } elseif ($r['change'] < 0) {
                    $r['trend'] = 'down';
                } else {
                    $r['trend'] = 'stable';
                }
            } else {
                $r['change'] = null;
                $r['trend'] = 'unknown';
            }
        }

        // Set headers untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="seo_reports_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM untuk UTF-8
        fputs($output, "\xEF\xBB\xBF");

        // Header CSV
        $headers = [
            'No',
            'Vendor',
            'Project',
            'Keyword',
            'Target Posisi',
            'Posisi Saat Ini',
            'Perubahan',
            'Trend',
            'Tanggal Selesai'
        ];
        fputcsv($output, $headers);

        // Data rows
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
                $report['project_name'] ?? '-',
                $report['keyword'] ?? '-',
                $report['target_position'] ?? '-',
                $report['current_position'] ?? '-',
                $report['change'] !== null ? $report['change'] : '-',
                $trendText,
                $report['updated_at'] ? date('d/m/Y', strtotime($report['updated_at'])) : '-'
            ];
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}