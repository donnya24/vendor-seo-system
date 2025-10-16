<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use App\Models\SeoProfilesModel;

class Commissions extends BaseAdminController
{
    protected $commissionModel;
    protected $vendorModel;
    protected $activityLogsModel;
    protected $notificationsModel;
    protected $userModel;
    protected $seoProfilesModel;

    public function __construct()
    {
        $this->commissionModel = new CommissionsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
        $this->seoProfilesModel = new SeoProfilesModel();
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

        // PERBAIKAN: Gunakan where()->first() untuk memastikan hasilnya array
        $commission = $this->commissionModel->where('id', $id)->first();

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

            // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
            $this->createCommissionNotification($id, $commission['vendor_id'], 'verify', $commission);

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

    /**
     * Method baru untuk menandai komisi sebagai unpaid
     */
    public function unpaid($id)
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        // PERBAIKAN: Gunakan where()->first() untuk memastikan hasilnya array
        $commission = $this->commissionModel->where('id', $id)->first();

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        // PERBAIKAN: Akses array dengan benar
        $commissionStatus = $commission['status'] ?? '';

        // Validasi status sebelum unpaid
        if ($commissionStatus === 'unpaid') {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Komisi ini sudah dalam status Unpaid.'
            ]);
        }

        try {
            // PERBAIKAN: Hanya update kolom yang ada di tabel
            $updateData = [
                'status' => 'unpaid'
            ];

            // Reset paid_at jika ada
            if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                $updateData['paid_at'] = null;
            }

            // Update status menjadi 'unpaid'
            $this->commissionModel->update($id, $updateData);

            // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
            $this->createCommissionNotification($id, $commission['vendor_id'], 'unpaid', $commission);

            // Log activity
            $vendorId = $commission['vendor_id'] ?? 0;
            $this->logActivity(
                'unpaid_commission',
                "Komisi #{$id} telah ditandai sebagai belum dibayar.",
                [
                    'commission_id' => $id,
                    'vendor_id' => $vendorId,
                    'amount' => $commission['amount'] ?? 0
                ]
            );

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Komisi berhasil ditandai sebagai belum dibayar.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error marking commission as unpaid: ' . $e->getMessage());
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

        // PERBAIKAN: Gunakan where()->first() untuk memastikan hasilnya array
        $commission = $this->commissionModel->where('id', $id)->first();

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        try {
            // PERBAIKAN: Akses array dengan benar
            $vendorId = $commission['vendor_id'] ?? 0;
            
            // Hapus komisi
            $this->commissionModel->delete($id);

            // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
            $this->createCommissionNotification($id, $vendorId, 'delete', $commission);

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

        // PERBAIKAN: Tambahkan 'unpaid' ke validasi action
        if (!in_array($action, ['verify', 'unpaid', 'delete'])) {
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
                // PERBAIKAN: Gunakan where()->first() untuk memastikan hasilnya array
                $commission = $this->commissionModel->where('id', $id)->first();
                
                if (!$commission) {
                    $errorCount++;
                    $errorMessages[] = "Komisi #{$id} tidak ditemukan";
                    $errorIds[] = $id;
                    continue;
                }

                switch ($action) {
                    case 'verify':
                        // PERBAIKAN: Akses array dengan benar
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

                        // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
                        $this->createCommissionNotification($id, $commission['vendor_id'], 'verify', $commission);
                        break;

                    case 'unpaid':
                        // PERBAIKAN: Akses array dengan benar
                        $commissionStatus = $commission['status'] ?? '';
                        
                        // Validasi status sebelum unpaid
                        if ($commissionStatus === 'unpaid') {
                            $errorCount++;
                            $errorMessages[] = "Komisi #{$id} sudah dalam status Unpaid";
                            $errorIds[] = $id;
                            continue 2; // Skip ke komisi berikutnya
                        }

                        // PERBAIKAN: Hanya update kolom yang ada di tabel
                        $updateData = [
                            'status' => 'unpaid'
                        ];

                        // Reset paid_at jika ada
                        if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                            $updateData['paid_at'] = null;
                        }

                        // Update status menjadi 'unpaid'
                        $this->commissionModel->update($id, $updateData);

                        // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
                        $this->createCommissionNotification($id, $commission['vendor_id'], 'unpaid', $commission);
                        break;

                    case 'delete':
                        $this->commissionModel->delete($id);
                        
                        // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
                        $this->createCommissionNotification($id, $commission['vendor_id'], 'delete', $commission);
                        break;

                    default:
                        $errorCount++;
                        $errorMessages[] = "Aksi '{$action}' tidak valid untuk komisi #{$id}";
                        $errorIds[] = $id;
                        continue 2;
                }

                // PERBAIKAN: Akses array dengan benar
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
     * Buat notifikasi untuk vendor dan admin terkait komisi
     */
    private function createCommissionNotification($commissionId, $vendorId, $actionType, $commissionData)
    {
        try {
            $db = \Config\Database::connect();
            
            // Dapatkan informasi vendor
            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                log_message('error', "Notification failed: Vendor with ID {$vendorId} not found");
                return false;
            }

            // Dapatkan user_id dari vendor
            $vendorUserId = $vendor['user_id'] ?? null;
            if (!$vendorUserId) {
                log_message('error', "Notification failed: Vendor {$vendorId} has no user_id");
                return false;
            }

            // Dapatkan semua admin users yang aktif
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1) // Pastikan user aktif
                ->get()
                ->getResultArray();
            
            log_message('info', "Found " . count($adminUsers) . " active admin users for notification");

            // PERBAIKAN: Dapatkan semua SEO users yang aktif
            // Metode 1: Melalui auth_groups_users (jika SEO users memiliki grup 'seo')
            $seoUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'seo')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();
            
            // Jika tidak ada SEO users melalui auth_groups, coba metode 2
            if (empty($seoUsers)) {
                log_message('info', "No SEO users found through auth_groups, trying seo_profiles table");
                
                // Metode 2: Melalui seo_profiles table
                $seoProfiles = $this->seoProfilesModel
                    ->select('user_id')
                    ->where('status', 'active') // Asumsi status aktif
                    ->findAll();
                
                // Konversi ke format yang sama dengan metode 1
                $seoUsers = array_map(function($profile) {
                    return ['user_id' => $profile['user_id']];
                }, $seoProfiles);
                
                // Filter hanya user yang aktif di tabel users
                $activeSeoUserIds = $db->table('users')
                    ->select('id')
                    ->whereIn('id', array_column($seoUsers, 'user_id'))
                    ->where('active', 1)
                    ->get()
                    ->getResultArray();
                
                // Filter seoUsers untuk hanya menyertakan user yang aktif
                $activeSeoUserIds = array_column($activeSeoUserIds, 'id');
                $seoUsers = array_filter($seoUsers, function($user) use ($activeSeoUserIds) {
                    return in_array($user['user_id'], $activeSeoUserIds);
                });
                
                // Re-index array
                $seoUsers = array_values($seoUsers);
            }
            
            log_message('info', "Found " . count($seoUsers) . " active SEO users for notification");
            if (empty($seoUsers)) {
                log_message('warning', "No active SEO users found. Check if group name 'seo' is correct and users are active.");
            }

            // Dapatkan admin yang sedang login
            $adminUserId = session()->get('user_id');
            $adminProfile = $db->table('admin_profiles')
                ->where('user_id', $adminUserId)
                ->get()
                ->getRowArray();
            $adminName = $adminProfile['name'] ?? 'Admin';

            // Format jumlah komisi
            $amount = number_format($commissionData['amount'] ?? 0, 0, ',', '.');
            
            // Format periode
            $periodStart = $commissionData['period_start'] ? date('d M Y', strtotime($commissionData['period_start'])) : '-';
            $periodEnd = $commissionData['period_end'] ? date('d M Y', strtotime($commissionData['period_end'])) : '-';
            $period = "{$periodStart} - {$periodEnd}";

            // Siapkan data notifikasi berdasarkan action type
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            switch ($actionType) {
                case 'verify':
                    $title = 'Komisi Telah Diverifikasi & Dibayar';
                    $message = "✅ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah diverifikasi dan dibayar oleh {$adminName}";
                    break;

                case 'unpaid':
                    $title = 'Komisi Ditandai Belum Dibayar';
                    $message = "⏰ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah ditandai sebagai belum dibayar oleh {$adminName}";
                    break;

                case 'delete':
                    $title = 'Komisi Dihapus';
                    $message = "🗑️ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah dihapus oleh {$adminName}";
                    break;

                default:
                    return false;
            }

            // Tambahkan detail komisi
            $message .= "\n\nDetail Komisi:";
            $message .= "\n• Vendor: {$vendor['business_name']}";
            $message .= "\n• Periode: {$period}";
            $message .= "\n• Jumlah: Rp {$amount}";
            $message .= "\n• Status: " . ucfirst($actionType);

            // Notifikasi untuk VENDOR
            if ($vendorUserId) {
                $notifications[] = [
                    'user_id' => $vendorUserId,
                    'vendor_id' => $vendorId,
                    'type' => 'commission_' . $actionType,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                log_message('info', "Added notification for vendor user ID: {$vendorUserId}");
            }

            // Notifikasi untuk semua ADMIN
            foreach ($adminUsers as $admin) {
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'commission_' . $actionType,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            log_message('info', "Added notifications for " . count($adminUsers) . " admin users");

            // Notifikasi untuk semua SEO
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => $seo['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'commission_' . $actionType,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            log_message('info', "Added notifications for " . count($seoUsers) . " SEO users");

            // Insert semua notifikasi
            if (!empty($notifications)) {
                $this->notificationsModel->insertBatch($notifications);
                
                // Log untuk debugging
                log_message('info', "Created commission {$actionType} notifications for commission {$commissionId}: " . count($notifications) . " notifications sent");
                
                return true;
            }

            log_message('warning', "No notifications were created for commission {$commissionId}");
            return false;

        } catch (\Exception $e) {
            log_message('error', "Error creating commission notification: " . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            return false;
        }
    }
}