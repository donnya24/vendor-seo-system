<?php
namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\NotificationsModel;
use App\Models\VendorProfilesModel;

class Vendor_verify extends BaseController
{
    protected $vendorModel;
    protected $notificationsModel;

    public function __construct()
    {
        $this->vendorModel = new VendorProfilesModel();
        $this->notificationsModel = new NotificationsModel();
    }

    public function index()
    {
        $vendors = $this->vendorModel->findAll();
        
        // Log aktivitas view vendor list
        log_activity_auto('view', "Melihat daftar vendor untuk verifikasi", [
            'module' => 'vendor_verify',
            'vendors_count' => count($vendors)
        ]);

        return view('seo/vendor_verify/index', [
            'vendors'    => $vendors,
            'title'      => 'Daftar Vendor',
            'activeMenu' => 'vendor'
        ]);
    }

    public function approve($id)
    {
        try {
            // Validasi AJAX request
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Invalid request method.'
                ]);
            }

            $user = service('auth')->user();
            $vendor = $this->vendorModel->find($id);

            if (!$vendor) {
                // Log aktivitas gagal approve vendor
                log_activity_auto('approve', "Gagal menyetujui vendor - tidak ditemukan", [
                    'module' => 'vendor_verify',
                    'status' => 'failed',
                    'vendor_id' => $id
                ]);

                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Vendor tidak ditemukan.'
                ]);
            }

            // Update vendor status
            $this->vendorModel->update($id, [
                'status'      => 'verified',
                'approved_at' => date('Y-m-d H:i:s'),
                'action_by'   => $user->id
            ]);

            // 🔔 KIRIM NOTIFIKASI KE VENDOR
            $this->sendVendorStatusNotification($vendor, 'verified');

            // Log aktivitas berhasil approve vendor
            log_activity_auto('approve', "Menyetujui vendor: {$vendor['business_name']}", [
                'module' => 'vendor_verify',
                'status' => 'success',
                'vendor_id' => $id,
                'vendor_name' => $vendor['business_name'],
                'previous_status' => $vendor['status'] ?? 'unknown'
            ]);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Vendor berhasil disetujui.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in approve vendor: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function reject($id)
    {
        try {
            // Validasi AJAX request
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Invalid request method.'
                ]);
            }

            $user = service('auth')->user();
            $vendor = $this->vendorModel->find($id);

            if (!$vendor) {
                // Log aktivitas gagal reject vendor
                log_activity_auto('reject', "Gagal menolak vendor - tidak ditemukan", [
                    'module' => 'vendor_verify',
                    'status' => 'failed',
                    'vendor_id' => $id
                ]);

                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Vendor tidak ditemukan.'
                ]);
            }

            // Ambil alasan reject dari POST data
            $rejectReason = $this->request->getPost('reject_reason') ?? 'Tidak ada alasan yang diberikan';

            // Validasi alasan reject
            if (empty(trim($rejectReason))) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Alasan penolakan harus diisi.'
                ]);
            }

            $this->vendorModel->update($id, [
                'status'          => 'rejected',
                'rejection_reason' => $rejectReason,
                'rejected_at'     => date('Y-m-d H:i:s'),
                'action_by'       => $user->id
            ]);

            // 🔔 KIRIM NOTIFIKASI KE VENDOR
            $this->sendVendorStatusNotification($vendor, 'rejected', $rejectReason);

            // Log aktivitas berhasil reject vendor
            log_activity_auto('reject', "Menolak vendor: {$vendor['business_name']}", [
                'module' => 'vendor_verify',
                'status' => 'success',
                'vendor_id' => $id,
                'vendor_name' => $vendor['business_name'],
                'previous_status' => $vendor['status'] ?? 'unknown',
                'reject_reason' => $rejectReason
            ]);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Vendor berhasil ditolak.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in reject vendor: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function pending($id)
    {
        try {
            // Validasi AJAX request
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Invalid request method.'
                ]);
            }

            $user = service('auth')->user();
            $vendor = $this->vendorModel->find($id);

            if (!$vendor) {
                // Log aktivitas gagal set pending vendor
                log_activity_auto('pending', "Gagal mengembalikan vendor ke status pending - tidak ditemukan", [
                    'module' => 'vendor_verify',
                    'status' => 'failed',
                    'vendor_id' => $id
                ]);

                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Vendor tidak ditemukan.'
                ]);
            }

            $this->vendorModel->update($id, [
                'status'      => 'pending',
                'action_by'   => $user->id,
                'updated_at'  => date('Y-m-d H:i:s')
            ]);

            // Log aktivitas berhasil set pending vendor
            log_activity_auto('pending', "Mengembalikan vendor ke status pending: {$vendor['business_name']}", [
                'module' => 'vendor_verify',
                'status' => 'success',
                'vendor_id' => $id,
                'vendor_name' => $vendor['business_name'],
                'previous_status' => $vendor['status'] ?? 'unknown'
            ]);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Vendor berhasil dikembalikan ke status pending.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in pending vendor: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    public function detail($id)
    {
        try {
            $vendor = $this->vendorModel->find($id);

            if (!$vendor) {
                // Log aktivitas gagal view vendor detail
                log_activity_auto('view', "Gagal melihat detail vendor - tidak ditemukan", [
                    'module' => 'vendor_verify',
                    'status' => 'failed',
                    'vendor_id' => $id
                ]);

                return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
            }

            // Log aktivitas view vendor detail
            log_activity_auto('view', "Melihat detail vendor: {$vendor['business_name']}", [
                'module' => 'vendor_verify',
                'status' => 'success',
                'vendor_id' => $id,
                'vendor_name' => $vendor['business_name'],
                'vendor_status' => $vendor['status'] ?? 'unknown'
            ]);

            return view('seo/vendor_verify/view', [
                'vendor'     => $vendor,
                'title'      => 'Detail Vendor',
                'activeMenu' => 'vendor'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in vendor detail: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkAction()
    {
        try {
            // Validasi AJAX request
            if (!$this->request->isAJAX()) {
                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Invalid request method.'
                ]);
            }

            $user = service('auth')->user();
            $action = $this->request->getPost('action');
            $vendorIds = $this->request->getPost('vendor_ids');

            if (empty($vendorIds) || !is_array($vendorIds)) {
                // Log aktivitas bulk action gagal
                log_activity_auto('bulk_action', "Gagal melakukan aksi bulk - vendor tidak dipilih", [
                    'module' => 'vendor_verify',
                    'status' => 'failed',
                    'action' => $action
                ]);

                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Tidak ada vendor yang dipilih.'
                ]);
            }

            $successCount = 0;
            $validActions = ['approve', 'reject', 'pending'];

            if (!in_array($action, $validActions)) {
                // Log aktivitas bulk action gagal - aksi tidak valid
                log_activity_auto('bulk_action', "Gagal melakukan aksi bulk - aksi tidak valid", [
                    'module' => 'vendor_verify',
                    'status' => 'failed',
                    'action' => $action,
                    'vendor_count' => count($vendorIds)
                ]);

                return $this->response->setJSON([
                    'success' => false, 
                    'message' => 'Aksi tidak valid.'
                ]);
            }

            foreach ($vendorIds as $vendorId) {
                $vendor = $this->vendorModel->find($vendorId);
                if ($vendor) {
                    $updateData = [
                        'action_by'  => $user->id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    if ($action === 'approve') {
                        $updateData['status'] = 'verified';
                        $updateData['approved_at'] = date('Y-m-d H:i:s');
                        
                        // 🔔 KIRIM NOTIFIKASI APPROVE
                        $this->sendVendorStatusNotification($vendor, 'verified');
                        
                    } elseif ($action === 'reject') {
                        $updateData['status'] = 'rejected';
                        $updateData['rejected_at'] = date('Y-m-d H:i:s');
                        
                        // 🔔 KIRIM NOTIFIKASI REJECT
                        $rejectReason = $this->request->getPost('reject_reason') ?? 'Tidak ada alasan yang diberikan';
                        $this->sendVendorStatusNotification($vendor, 'rejected', $rejectReason);
                        
                    } elseif ($action === 'pending') {
                        $updateData['status'] = 'pending';
                        // Tidak kirim notifikasi untuk pending
                    }

                    $this->vendorModel->update($vendorId, $updateData);
                    $successCount++;

                    // Log individual vendor action
                    log_activity_auto($action, "Aksi bulk {$action} vendor: {$vendor['business_name']}", [
                        'module' => 'vendor_verify',
                        'status' => 'success',
                        'vendor_id' => $vendorId,
                        'vendor_name' => $vendor['business_name'],
                        'bulk_action' => true
                    ]);
                }
            }

            // Log summary bulk action
            log_activity_auto('bulk_action', "Berhasil melakukan aksi bulk {$action} pada {$successCount} vendor", [
                'module' => 'vendor_verify',
                'status' => 'success',
                'action' => $action,
                'total_vendors' => count($vendorIds),
                'success_count' => $successCount,
                'failed_count' => count($vendorIds) - $successCount
            ]);

            return $this->response->setJSON([
                'success' => true, 
                'message' => "Berhasil {$action} {$successCount} vendor.",
                'processed_count' => $successCount
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in bulk action: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Kirim notifikasi status vendor ke vendor (dari SEO) - FIXED: Ambil nama dari seo_profiles.name
     */
    private function sendVendorStatusNotification($vendorData, $status, $reason = null)
    {
        try {
            $vendorName = $vendorData['business_name'] ?? 'Vendor Tidak Dikenal';
            $vendorUserId = $vendorData['user_id'] ?? null;
            
            if (!$vendorUserId) {
                log_message('error', 'Vendor user_id tidak ditemukan untuk notifikasi');
                return false;
            }

            // Ambil data SEO yang melakukan aksi
            $currentUser = service('auth')->user();
            
            // Cari seo_id dan nama dari seo_profiles
            $db = \Config\Database::connect();
            $seoProfile = $db->table('seo_profiles')
                           ->where('user_id', $currentUser->id)
                           ->get()
                           ->getRowArray();
            
            $seoId = $seoProfile['id'] ?? null;
            $seoName = $seoProfile['name'] ?? $currentUser->username ?? 'Tim SEO'; // ✅ PERBAIKAN: Ambil dari seo_profiles.name

            // Tentukan pesan berdasarkan status (TANPA HTML TAGS)
            $title = '';
            $message = '';
            
            switch ($status) {
                case 'verified':
                    $title = '🎉 Akun Vendor Diverifikasi';
                    $message = "Selamat! Akun vendor {$vendorName} Anda telah diverifikasi oleh Tim SEO {$seoName} dan sekarang aktif. Anda dapat mulai menggunakan semua fitur sistem.";
                    break;
                    
                case 'rejected':
                    $title = '❌ Verifikasi Vendor Ditolak';
                    $message = "Pengajuan verifikasi vendor {$vendorName} Anda ditolak oleh Tim SEO {$seoName}.";
                    if ($reason) {
                        $message .= "\n\nAlasan penolakan:\n{$reason}";
                    }
                    $message .= "\n\nSilakan perbaiki data Anda dan ajukan ulang verifikasi.";
                    break;
                    
                default:
                    return false; // Tidak kirim notifikasi untuk status lain
            }

            // PERBAIKAN: Data notifikasi dengan type system
            $notificationData = [
                'user_id' => $vendorUserId,
                'vendor_id' => $vendorData['id'] ?? null,
                'seo_id' => $seoId,
                'type' => 'system', // PERUBAHAN: type menjadi 'system'
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Simpan notifikasi
            $result = $this->notificationsModel->insert($notificationData);
            
            if ($result) {
                log_message('info', "Notifikasi status vendor berhasil dikirim: {$vendorName} - {$status} oleh {$seoName}");
                
                // Juga kirim notifikasi ke Admin tentang perubahan status vendor
                $this->sendAdminNotification($vendorData, $status, $seoName);
                
                return true;
            } else {
                log_message('error', "Gagal menyimpan notifikasi untuk vendor: {$vendorName}");
                return false;
            }

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi status vendor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim notifikasi ke Admin tentang perubahan status vendor - FIXED: Ambil nama dari seo_profiles.name
     */
    private function sendAdminNotification($vendorData, $status, $seoName)
    {
        try {
            $vendorName = $vendorData['business_name'] ?? 'Vendor Tidak Dikenal';
            
            // Ambil semua admin
            $db = \Config\Database::connect();
            $adminUsers = $db->table('auth_groups_users agu')
                           ->select('agu.user_id, ap.id as admin_id')
                           ->join('admin_profiles ap', 'ap.user_id = agu.user_id')
                           ->where('agu.group', 'admin')
                           ->get()
                           ->getResultArray();

            if (empty($adminUsers)) {
                return false;
            }

            $title = '';
            $message = '';
            
            switch ($status) {
                case 'verified':
                    $title = '✅ Vendor Telah Diverifikasi';
                    $message = "Vendor {$vendorName} telah diverifikasi oleh Tim SEO {$seoName} dan sekarang aktif.";
                    break;
                    
                case 'rejected':
                    $title = '❌ Vendor Ditolak';
                    $message = "Vendor {$vendorName} telah ditolak oleh Tim SEO {$seoName}.";
                    break;
                    
                default:
                    return false;
            }

            $notifications = [];
            $now = date('Y-m-d H:i:s');

            foreach ($adminUsers as $admin) {
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'admin_id' => $admin['admin_id'],
                    'vendor_id' => $vendorData['id'] ?? null,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Simpan notifikasi batch untuk admin
            $result = $db->table('notifications')->insertBatch($notifications);
            
            if ($result) {
                log_message('info', "Notifikasi admin berhasil dikirim untuk vendor: {$vendorName} - {$status} oleh {$seoName}");
            }

            return $result;

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi ke admin: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Method untuk mendapatkan nama SEO dari profile
     */
    private function getSeoName($userId)
    {
        try {
            $db = \Config\Database::connect();
            $seoProfile = $db->table('seo_profiles')
                           ->where('user_id', $userId)
                           ->get()
                           ->getRowArray();
            
            // Prioritaskan nama dari seo_profiles, fallback ke username
            return $seoProfile['name'] ?? service('auth')->user()->username ?? 'Tim SEO';
        } catch (\Throwable $e) {
            log_message('error', 'Error getting SEO name: ' . $e->getMessage());
            return 'Tim SEO';
        }
    }

    /**
     * Method untuk testing notifikasi (bisa diakses via URL)
     */
    public function testNotification($vendorId, $status = 'verified')
    {
        if (!is_cli() && ENVIRONMENT !== 'development') {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya untuk development']);
        }

        $vendor = $this->vendorModel->find($vendorId);
        if (!$vendor) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vendor tidak ditemukan']);
        }

        $reason = $status === 'rejected' ? 'Testing alasan penolakan' : null;
        $result = $this->sendVendorStatusNotification($vendor, $status, $reason);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'Notifikasi test berhasil' : 'Notifikasi test gagal',
            'vendor' => $vendor['business_name'],
            'status' => $status,
            'seo_name' => $this->getSeoName(service('auth')->user()->id)
        ]);
    }

    /**
     * Method untuk melihat notifikasi yang sudah dikirim (debugging)
     */
    public function viewNotifications($vendorId = null)
    {
        if (!is_cli() && ENVIRONMENT !== 'development') {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya untuk development']);
        }

        $db = \Config\Database::connect();
        
        $query = $db->table('notifications n')
                   ->select('n.*, u.username, v.business_name as vendor_name, sp.name as seo_name')
                   ->join('users u', 'u.id = n.user_id', 'left')
                   ->join('vendor_profiles v', 'v.id = n.vendor_id', 'left')
                   ->join('seo_profiles sp', 'sp.id = n.seo_id', 'left')
                   ->orderBy('n.created_at', 'DESC')
                   ->limit(20);

        if ($vendorId) {
            $query->where('n.vendor_id', $vendorId);
        }

        $notifications = $query->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $notifications,
            'count' => count($notifications)
        ]);
    }

    /**
     * Method untuk melihat profile SEO current user (debugging)
     */
    public function viewSeoProfile()
    {
        if (!is_cli() && ENVIRONMENT !== 'development') {
            return $this->response->setJSON(['success' => false, 'message' => 'Hanya untuk development']);
        }

        $currentUser = service('auth')->user();
        $db = \Config\Database::connect();
        
        $seoProfile = $db->table('seo_profiles')
                       ->where('user_id', $currentUser->id)
                       ->get()
                       ->getRowArray();

        return $this->response->setJSON([
            'success' => true,
            'user' => [
                'id' => $currentUser->id,
                'username' => $currentUser->username,
                'email' => $currentUser->email
            ],
            'seo_profile' => $seoProfile,
            'seo_name' => $seoProfile['name'] ?? 'Tidak ada nama di profile'
        ]);
    }
}