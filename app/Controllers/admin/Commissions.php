<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController; // Perbaikan: Extend BaseAdminController
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Commissions extends BaseAdminController // Perbaikan: Extend BaseAdminController
{
    protected $commissionModel;
    protected $vendorModel;
    protected $activityLogsModel;

    public function __construct()
    {
        // Hapus parent::__construct() karena BaseController tidak memiliki constructor
        $this->commissionModel = new CommissionsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        // Log activity akses halaman commissions
        $this->logActivity(
            'view_commissions',
            'Mengakses halaman manajemen komisi vendor'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();
        
        // Filter by vendor jika ada
        $vendorId = $this->request->getGet('vendor_id');
        $status = $this->request->getGet('status');

        // Build query
        $query = $this->commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name, vendor_profiles.owner_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left');

        if ($vendorId && $vendorId !== 'all') {
            $query->where('commissions.vendor_id', $vendorId);
        }

        if ($status && $status !== 'all') {
            $query->where('commissions.status', $status);
        }

        $commissions = $query->orderBy('commissions.created_at', 'DESC')
            ->paginate(20);

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel
            ->select('id, business_name')
            ->where('status', 'verified')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/commissions/index', array_merge([
            'title'       => 'Manajemen Komisi Vendor',
            'activeMenu'  => 'commissions',
            'commissions' => $commissions,
            'pager'       => $this->commissionModel->pager,
            'vendors'     => $vendors,
            'vendor_id'   => $vendorId,
            'status'      => $status
        ], $commonData));
    }

    public function verify($id)
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        // PERBAIKAN: Akses array dengan benar
        $commissionStatus = $commission['status'] ?? '';

        // Validasi status sebelum verifikasi
        if ($commissionStatus === 'paid') {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Komisi ini sudah dalam status Paid dan tidak dapat diverifikasi ulang.'
            ]);
        }

        try {
            // PERBAIKAN: Hanya update kolom yang ada di tabel
            $updateData = [
                'status' => 'paid'
            ];

            // Tambahkan paid_at hanya jika kolomnya ada di allowedFields
            if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                $updateData['paid_at'] = date('Y-m-d H:i:s');
            }

            // Update status menjadi 'paid'
            $this->commissionModel->update($id, $updateData);

            // 🔔 KIRIM NOTIFIKASI KE VENDOR
            $this->sendCommissionPaidNotification($commission);

            // Log activity
            $vendorId = $commission['vendor_id'] ?? 0;
            $this->logActivity(
                'verify_commission',
                "Komisi #{$id} telah diverifikasi dan ditandai sebagai dibayar.",
                [
                    'commission_id' => $id,
                    'vendor_id' => $vendorId,
                    'amount' => $commission['amount'] ?? 0
                ]
            );

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Komisi berhasil diverifikasi dan ditandai sebagai dibayar.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error verifying commission: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        try {
            // PERBAIKAN: Akses vendor_id sebagai array
            $vendorId = $commission['vendor_id'] ?? 0;
            
            // Hapus komisi
            $this->commissionModel->delete($id);

            // Log activity
            $this->logActivity(
                'delete_commission',
                "Komisi #{$id} telah dihapus.",
                [
                    'commission_id' => $id,
                    'vendor_id' => $vendorId,
                    'amount' => $commission['amount'] ?? 0
                ]
            );

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Komisi berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting commission: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

public function exportCsv()
{
    // Log activity export
    $this->logActivity(
        'export_commissions_csv',
        'Mengekspor data komisi ke CSV'
    );

    // Get filter parameters
    $vendorId = $this->request->getGet('vendor_id');
    $status = $this->request->getGet('status');

    // Build query sederhana tanpa join ke users
    $query = $this->commissionModel
        ->select('commissions.*, vendor_profiles.business_name as vendor_name, vendor_profiles.owner_name, vendor_profiles.phone')
        ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left');

    if ($vendorId && $vendorId !== 'all') {
        $query->where('commissions.vendor_id', $vendorId);
    }

    if ($status && $status !== 'all') {
        $query->where('commissions.status', $status);
    }

    $commissions = $query->orderBy('commissions.created_at', 'DESC')
        ->findAll();

    // Set headers untuk download CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="komisi_vendor_' . date('Y-m-d_H-i-s') . '.csv"');

    $output = fopen('php://output', 'w');

    // Add BOM untuk UTF-8
    fputs($output, "\xEF\xBB\xBF");

    // Header CSV tanpa email
    $headers = [
        'No',
        'ID Komisi',
        'Vendor',
        'Pemilik',
        'Telepon',
        'Periode',
        'Pendapatan Kotor',
        'Komisi',
        'Status',
        'Tgl Dibayar',
        'Tgl Dibuat'
    ];
    fputcsv($output, $headers);

    // Data rows
    $no = 1;
    foreach ($commissions as $commission) {
        $period = ($commission['period_start'] ?? '-') . ' s/d ' . ($commission['period_end'] ?? '-');
        
        $row = [
            $no++,
            $commission['id'] ?? '-',
            $commission['vendor_name'] ?? '-',
            $commission['owner_name'] ?? '-',
            $commission['phone'] ?? '-',
            $period,
            $commission['earning'] ?? 0,
            $commission['amount'] ?? 0,
            $this->getStatusLabel($commission['status'] ?? ''),
            $commission['paid_at'] ? date('d/m/Y', strtotime($commission['paid_at'])) : '-',
            $commission['created_at'] ? date('d/m/Y', strtotime($commission['created_at'])) : '-'
        ];
        fputcsv($output, $row);
    }

    fclose($output);
    exit;
}

    /**
     * Helper method untuk label status
     */
    private function getStatusLabel($status)
    {
        $statusMap = [
            'unpaid' => 'Belum Dibayar',
            'paid' => 'Sudah Dibayar'
        ];
        
        return $statusMap[strtolower($status)] ?? 'Unknown';
    }

    public function bulkAction()
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $action = $this->request->getPost('action');
        $commissionIds = $this->request->getPost('commission_ids');

        if (empty($commissionIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada komisi yang dipilih.']);
        }

        // Validasi action
        if (!in_array($action, ['verify', 'delete'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        // Pastikan commission_ids adalah array
        if (!is_array($commissionIds)) {
            $commissionIds = [$commissionIds];
        }

        $successCount = 0;
        $errorCount = 0;
        $errorMessages = [];
        $successIds = [];
        $errorIds = [];

        foreach ($commissionIds as $id) {
            try {
                $commission = $this->commissionModel->find($id);
                if (!$commission) {
                    $errorCount++;
                    $errorMessages[] = "Komisi #{$id} tidak ditemukan";
                    $errorIds[] = $id;
                    continue;
                }

                switch ($action) {
                    case 'verify':
                        // PERBAIKAN: Akses status sebagai array
                        $commissionStatus = $commission['status'] ?? '';
                        
                        // Validasi status sebelum verifikasi
                        if ($commissionStatus === 'paid') {
                            $errorCount++;
                            $errorMessages[] = "Komisi #{$id} sudah dalam status Paid dan tidak dapat diverifikasi ulang";
                            $errorIds[] = $id;
                            continue 2; // Skip ke komisi berikutnya
                        }

                        // PERBAIKAN: Hanya update kolom yang ada di tabel
                        $updateData = [
                            'status' => 'paid'
                        ];

                        // Tambahkan paid_at hanya jika kolomnya ada di allowedFields
                        if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                            $updateData['paid_at'] = date('Y-m-d H:i:s');
                        }

                        // Update status menjadi 'paid'
                        $this->commissionModel->update($id, $updateData);

                        // 🔔 KIRIM NOTIFIKASI KE VENDOR
                        $this->sendCommissionPaidNotification($commission);
                        break;

                    case 'delete':
                        $this->commissionModel->delete($id);
                        break;

                    default:
                        $errorCount++;
                        $errorMessages[] = "Aksi '{$action}' tidak valid untuk komisi #{$id}";
                        $errorIds[] = $id;
                        continue 2;
                }

                // PERBAIKAN: Akses vendor_id sebagai array
                $vendorId = $commission['vendor_id'] ?? 0;
                
                // Log activity
                $this->logActivity(
                    'bulk_' . $action . '_commission',
                    "Komisi #{$id} telah diproses dengan aksi: {$action}",
                    [
                        'commission_id' => $id,
                        'vendor_id' => $vendorId,
                        'amount' => $commission['amount'] ?? 0,
                        'action' => $action
                    ]
                );

                $successCount++;
                $successIds[] = $id;

            } catch (\Exception $e) {
                log_message('error', "Error processing commission #{$id}: " . $e->getMessage());
                $errorCount++;
                $errorMessages[] = "Komisi #{$id}: " . $e->getMessage();
                $errorIds[] = $id;
            }
        }

        // Log bulk activity
        $this->logActivity(
            'bulk_action_commission',
            "Melakukan aksi bulk {$action} untuk " . count($commissionIds) . " komisi",
            [
                'action' => $action,
                'total_count' => count($commissionIds),
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'success_ids' => $successIds,
                'error_ids' => $errorIds
            ]
        );

        // Response yang lebih detail
        $response = [
            'success' => $successCount > 0,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'success_ids' => $successIds,
            'error_ids' => $errorIds,
            'errors' => $errorMessages
        ];

        if ($successCount > 0 && $errorCount === 0) {
            // Semua berhasil
            $response['message'] = "Berhasil memproses {$successCount} komisi.";
        } elseif ($successCount > 0 && $errorCount > 0) {
            // Sebagian berhasil, sebagian gagal
            $response['message'] = "Berhasil memproses {$successCount} komisi. {$errorCount} komisi gagal diproses.";
        } else {
            // Semua gagal
            $response['message'] = "Gagal memproses komisi yang dipilih.";
        }

        return $this->response->setJSON($response);
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
                'module'      => 'admin_commissions',
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
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi komisi paid ke vendor
     */
    private function sendCommissionPaidNotification($commissionData)
    {
        try {
            $db = \Config\Database::connect();
            
            $vendorId = $commissionData['vendor_id'] ?? null;
            $commissionId = $commissionData['id'] ?? null;
            $amount = $commissionData['amount'] ?? 0;
            
            if (!$vendorId) {
                log_message('error', 'Vendor ID tidak ditemukan untuk notifikasi komisi paid');
                return;
            }

            // Ambil data vendor
            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                log_message('error', 'Vendor tidak ditemukan untuk notifikasi komisi paid - ID: ' . $vendorId);
                return;
            }

            $vendorName = $vendor['business_name'] ?? 'Vendor Tidak Dikenal';
            $vendorUserId = $vendor['user_id'] ?? null;
            
            if (!$vendorUserId) {
                log_message('error', 'Vendor user_id tidak ditemukan untuk notifikasi komisi paid');
                return;
            }

            // Format amount
            $amountFormatted = 'Rp ' . number_format($amount, 0, ',', '.');
            
            // Periode komisi
            $periodStart = $commissionData['period_start'] ?? '';
            $periodEnd = $commissionData['period_end'] ?? '';
            $periodText = '';
            
            if ($periodStart && $periodEnd) {
                $periodText = " untuk periode " . date('d/m/Y', strtotime($periodStart)) . " - " . date('d/m/Y', strtotime($periodEnd));
            }

            // Kirim notifikasi ke vendor
            $db->table('notifications')->insert([
                'user_id' => $vendorUserId,
                'vendor_id' => $vendorId,
                'seo_id' => null,
                'type' => 'system',
                'title' => 'Komisi Telah Dibayar',
                'message' => "Komisi sebesar {$amountFormatted}{$periodText} telah dibayar dan masuk ke saldo Admin. Terima kasih atas kerjasamanya!",
                'is_read' => 0,
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            log_message('info', "Notifikasi komisi paid berhasil dikirim: Vendor {$vendorName} - Amount {$amountFormatted}");

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi komisi paid: ' . $e->getMessage());
        }
    }
}