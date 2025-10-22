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

        // Build query dengan join ke seo_profiles
        $query = $this->commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name, vendor_profiles.owner_name, 
                     COALESCE(admin_profiles.name, seo_profiles.name) as action_by_name,
                     COALESCE(admin_profiles.user_id, seo_profiles.user_id) as action_by_user_id')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left')
            ->join('admin_profiles', 'admin_profiles.user_id = commissions.action_by', 'left')
            ->join('seo_profiles', 'seo_profiles.user_id = commissions.action_by', 'left');

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

        $commission = $this->commissionModel->where('id', $id)->first();

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        $commissionStatus = $commission['status'] ?? '';

        // Validasi status sebelum verifikasi
        if ($commissionStatus === 'paid') {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Komisi ini sudah dalam status Paid dan tidak dapat diverifikasi ulang.'
            ]);
        }

        try {
            $currentUser = service('auth')->user();
            if (!$currentUser) {
                return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan atau session expired.']);
            }
            
            // Update data dengan semua field yang diperlukan
            $updateData = [
                'status' => 'paid',
                'action_by' => $currentUser->id
            ];

            // Tambahkan paid_at jika ada di allowedFields
            if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                $updateData['paid_at'] = date('Y-m-d H:i:s');
            }

            // Update status menjadi 'paid'
            $updateResult = $this->commissionModel->update($id, $updateData);
            
            // Debug: Log hasil update
            log_message('debug', 'Update result: ' . json_encode([
                'success' => $updateResult,
                'data' => $updateData
            ]));

            // Pastikan createCommissionNotification dipanggil
            if ($updateResult) {
                // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
                $notificationResult = $this->createCommissionNotification($id, $commission['vendor_id'], 'verify', $commission, $currentUser->id);
                
                // Debug: Log hasil notifikasi
                log_message('debug', 'Notification result: ' . json_encode([
                    'success' => $notificationResult
                ]));
                
                if (!$notificationResult) {
                    log_message('error', 'Failed to create commission notification');
                }
            }

            // Log activity
            $vendorId = $commission['vendor_id'] ?? 0;
            $this->logActivity(
                'verify_commission',
                "Komisi #{$id} telah diverifikasi dan ditandai sebagai dibayar.",
                [
                    'commission_id' => $id,
                    'vendor_id' => $vendorId,
                    'amount' => $commission['amount'] ?? 0,
                    'action_by' => $currentUser->id
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
     * Method untuk menandai komisi sebagai unpaid
     */
    public function unpaid($id)
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $commission = $this->commissionModel->where('id', $id)->first();

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        $commissionStatus = $commission['status'] ?? '';

        // Validasi status sebelum unpaid
        if ($commissionStatus === 'unpaid') {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Komisi ini sudah dalam status Unpaid.'
            ]);
        }

        try {
            $currentUser = service('auth')->user();
            if (!$currentUser) {
                return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan atau session expired.']);
            }
            
            $updateData = [
                'status' => 'unpaid',
                'action_by' => $currentUser->id
            ];

            // Reset paid_at jika ada
            if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                $updateData['paid_at'] = null;
            }

            // Update status menjadi 'unpaid'
            $updateResult = $this->commissionModel->update($id, $updateData);
            
            // Debug: Log hasil update
            log_message('debug', 'Update result for unpaid: ' . json_encode([
                'success' => $updateResult,
                'data' => $updateData
            ]));

            // PERBAIKAN: Pastikan createCommissionNotification dipanggil dan diperiksa hasilnya
            if ($updateResult) {
                // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
                $notificationResult = $this->createCommissionNotification($id, $commission['vendor_id'], 'unpaid', $commission, $currentUser->id);
                
                // Debug: Log hasil notifikasi
                log_message('debug', 'Notification result for unpaid: ' . json_encode([
                    'success' => $notificationResult
                ]));
                
                if (!$notificationResult) {
                    log_message('error', 'Failed to create commission notification for unpaid status');
                }
            }

            // Log activity
            $vendorId = $commission['vendor_id'] ?? 0;
            $this->logActivity(
                'unpaid_commission',
                "Komisi #{$id} telah ditandai sebagai belum dibayar.",
                [
                    'commission_id' => $id,
                    'vendor_id' => $vendorId,
                    'amount' => $commission['amount'] ?? 0,
                    'action_by' => $currentUser->id
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

        $commission = $this->commissionModel->where('id', $id)->first();

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        try {
            $currentUser = service('auth')->user();
            if (!$currentUser) {
                return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan atau session expired.']);
            }
            
            $vendorId = $commission['vendor_id'] ?? 0;
            
            // Hapus komisi
            $this->commissionModel->delete($id);

            // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
            $this->createCommissionNotification($id, $vendorId, 'delete', $commission, $currentUser->id);

            // Log activity
            $this->logActivity(
                'delete_commission',
                "Komisi #{$id} telah dihapus.",
                [
                    'commission_id' => $id,
                    'vendor_id' => $vendorId,
                    'amount' => $commission['amount'] ?? 0,
                    'action_by' => $currentUser->id
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
            ->select('commissions.*, vendor_profiles.business_name as vendor_name, vendor_profiles.owner_name, vendor_profiles.phone, 
                     COALESCE(admin_profiles.name, seo_profiles.name) as action_by_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left')
            ->join('admin_profiles', 'admin_profiles.user_id = commissions.action_by', 'left')
            ->join('seo_profiles', 'seo_profiles.user_id = commissions.action_by', 'left');

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

        // PERBAIKAN: Header CSV tanpa kolom tanggal
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
            'Diproses Oleh'
        ];
        fputcsv($output, $headers);

        // Data rows
        $no = 1;
        foreach ($commissions as $commission) {
            $period = ($commission['period_start'] ?? '-') . ' s/d ' . ($commission['period_end'] ?? '-');
            
            // PERBAIKAN: Data rows tanpa kolom tanggal
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
                $commission['action_by_name'] ?? '-'
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
     * PERBAIKAN: Memperbaiki metode createCommissionNotification untuk mengikuti pola dari SEO controller
     * dan menggunakan type 'system' seperti yang diminta
     */
    private function createCommissionNotification($commissionId, $vendorId, $actionType, $commissionData, $currentAdminId = null)
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

            // PERBAIKAN: Dapatkan semua admin users menggunakan auth_groups_users seperti di SEO controller
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();
            
            // PERBAIKAN: Dapatkan admin yang sedang login
            $adminUserId = $currentAdminId ?: session()->get('user_id');
            $adminProfile = $db->table('admin_profiles')
                ->where('user_id', $adminUserId)
                ->get()
                ->getRowArray();
            $adminName = $adminProfile['name'] ?? 'Admin';

            // PERBAIKAN: Dapatkan semua SEO users
            $seoUsers = $db->table('seo_profiles sp')
                ->select('sp.user_id')
                ->join('users u', 'u.id = sp.user_id')
                ->where('sp.status', 'active')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();

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
                    $message = "✅ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah diverifikasi oleh {$adminName}";
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
            $message .= "\n• Diproses oleh: {$adminName}";

            // PERBAIKAN: Notifikasi untuk VENDOR dengan type 'system'
            if ($vendorUserId) {
                $notifications[] = [
                    'user_id' => $vendorUserId,
                    'vendor_id' => $vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
                log_message('info', "Added notification for vendor user ID: {$vendorUserId}");
            }

            // PERBAIKAN: Notifikasi untuk semua ADMIN KECUALI admin yang sedang login dengan type 'system'
            foreach ($adminUsers as $admin) {
                // Skip admin yang sedang login
                if ($admin['user_id'] == $adminUserId) {
                    continue;
                }
                
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            log_message('info', "Added notifications for " . (count($adminUsers) - 1) . " admin users (excluding current admin)");

            // PERBAIKAN: Notifikasi untuk semua SEO users dengan type 'system'
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => $seo['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }
            log_message('info', "Added notifications for " . count($seoUsers) . " SEO users");

            // PERBAIKAN: Insert semua notifikasi dengan error handling
            try {
                if (!empty($notifications)) {
                    // Debug: Log data notifikasi sebelum insert
                    log_message('debug', 'Notifications to insert: ' . json_encode($notifications));
                    
                    $insertResult = $this->notificationsModel->insertBatch($notifications);
                    
                    if ($insertResult) {
                        // Log untuk debugging
                        log_message('info', "Created commission {$actionType} notifications for commission {$commissionId}: " . count($notifications) . " notifications sent");
                        
                        return true;
                    } else {
                        log_message('error', "Failed to insert batch notifications");
                        log_message('error', "Notification Model Errors: " . json_encode($this->notificationsModel->errors()));
                        return false;
                    }
                } else {
                    log_message('warning', "No notifications to insert for commission {$commissionId}");
                    return false;
                }
            } catch (\Exception $e) {
                log_message('error', "Error inserting notifications: " . $e->getMessage());
                log_message('error', $e->getTraceAsString());
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', "Error creating commission notification: " . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Helper method untuk mendapatkan role user
     */
    private function getUserRole($userId)
    {
        // Cek di tabel auth_groups_users
        $db = \Config\Database::connect();
        $adminGroup = $db->table('auth_groups_users')
            ->where('user_id', $userId)
            ->where('group', 'admin')
            ->get()
            ->getRowArray();
        
        if ($adminGroup) {
            return 'admin';
        }
        
        // Cek di tabel seo_profiles
        $seoProfile = $db->table('seo_profiles')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();
        
        if ($seoProfile) {
            return 'seo';
        }
        
        return 'unknown';
    }
}