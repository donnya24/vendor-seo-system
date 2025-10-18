<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use App\Models\SeoProfilesModel;

class Commissions extends BaseController
{
    protected $commissionModel;
    protected $vendorModel;
    protected $notificationsModel;
    protected $userModel;
    protected $seoProfilesModel;

    public function __construct()
    {
        $this->commissionModel = new CommissionsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
        $this->seoProfilesModel = new SeoProfilesModel();
    }

    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $status = $this->request->getGet('status');

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        // PERBAIKAN: Gunakan model untuk pagination dengan join yang benar
        $query = $this->commissionModel
            ->select('
                commissions.*,
                vendor_profiles.business_name as vendor_name,
                vendor_profiles.owner_name,
                COALESCE(seo_profiles.name, admin_profiles.name) as action_by_name,
                COALESCE(seo_profiles.user_id, admin_profiles.user_id) as action_by_user_id
            ')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left')
            // PERBAIKAN: Gunakan LEFT JOIN untuk seo_profiles dan admin_profiles
            ->join('seo_profiles', 'seo_profiles.user_id = commissions.action_by', 'left')
            ->join('admin_profiles', 'admin_profiles.user_id = commissions.action_by', 'left');

        // Filter berdasarkan vendor_id jika dipilih
        if (!empty($vendorId)) {
            $query->where('commissions.vendor_id', $vendorId);
        }

        // Filter berdasarkan status jika dipilih
        if (!empty($status) && in_array($status, ['paid', 'unpaid'])) {
            $query->where('commissions.status', $status);
        }

        $query->orderBy('commissions.period_start', 'DESC');

        // Gunakan pagination bawaan CodeIgniter
        $commissions = $query->paginate(20, 'group1');
        $pager = $this->commissionModel->pager;

        // Debug: Tampilkan hasil query untuk memeriksa data
        // echo '<pre>';
        // print_r($commissions);
        // echo '</pre>';

        // Log aktivitas view commissions
        if (!empty($vendorId)) {
            $logMessage = !empty($status) 
                ? "Melihat daftar komisi vendor {$vendorId} dengan status {$status}"
                : "Melihat daftar komisi vendor {$vendorId}";
            
            log_crud_activity('read', 'komisi vendor', $vendorId, [
                'module' => 'commissions',
                'vendor_id' => $vendorId,
                'status_filter' => $status
            ]);
        } else {
            $logMessage = !empty($status) 
                ? "Melihat daftar komisi semua vendor dengan status {$status}"
                : "Melihat daftar komisi semua vendor";
            
            log_activity_auto('view', $logMessage, [
                'module' => 'commissions',
                'status_filter' => $status
            ]);
        }

        return view('seo/commissions/index', [
            'title'       => 'Komisi Vendor',
            'activeMenu'  => 'commissions',
            'commissions' => $commissions,
            'pager'       => $pager,
            'vendorId'    => $vendorId,
            'status'      => $status,
            'vendors'     => $vendors,
        ]);
    }
    
    public function approve($id)
    {
        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        // Dapatkan user yang sedang login
        $currentUser = service('auth')->user();
        if (!$currentUser) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan atau session expired.']);
        }

        // Debug: Tampilkan data user yang sedang login
        log_message('debug', 'Current User Data: ' . json_encode([
            'id' => $currentUser->id,
            'username' => $currentUser->username
        ]));

        // Update dengan action_by
        $updateData = [
            'status'      => 'paid',
            'approved_at' => date('Y-m-d H:i:s'),
            'paid_at'     => date('Y-m-d H:i:s'),
            'action_by'   => $currentUser->id
        ];

        // Debug: Tampilkan data yang akan diupdate
        log_message('debug', 'Update Data: ' . json_encode($updateData));

        $this->commissionModel->update($id, $updateData);

        // Debug: Verifikasi data setelah update
        $updatedCommission = $this->commissionModel->find($id);
        log_message('debug', 'Updated Commission: ' . json_encode($updatedCommission));

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('approve', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah diverifikasi", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id'],
            'action_by' => $currentUser->id
        ]);

        // Buat notifikasi untuk vendor dan admin
        $this->createCommissionNotification($id, $commission['vendor_id'], 'approve', $commission);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('msg', 'Komisi telah diverifikasi');
    }

    public function reject($id)
    {
        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        // Dapatkan user yang sedang login
        $currentUser = service('auth')->user();
        if (!$currentUser) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan atau session expired.']);
        }

        // Update dengan action_by
        $updateData = [
            'status'      => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'action_by'   => $currentUser->id
        ];

        $this->commissionModel->update($id, $updateData);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('reject', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah ditolak", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id'],
            'action_by' => $currentUser->id
        ]);

        // Buat notifikasi untuk vendor dan admin
        $this->createCommissionNotification($id, $commission['vendor_id'], 'reject', $commission);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('msg', 'Komisi telah ditolak.');
    }

    public function markAsPaid($id)
    {
        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        // Dapatkan user yang sedang login
        $currentUser = service('auth')->user();
        if (!$currentUser) {
            return $this->response->setJSON(['success' => false, 'message' => 'User tidak ditemukan atau session expired.']);
        }

        // Update dengan action_by
        $updateData = [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'action_by' => $currentUser->id
        ];

        $this->commissionModel->update($id, $updateData);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('mark_as_paid', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah ditandai sebagai dibayar", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id'],
            'action_by' => $currentUser->id
        ]);

        // Buat notifikasi untuk vendor dan admin
        $this->createCommissionNotification($id, $commission['vendor_id'], 'mark_paid', $commission);

        return redirect()->back()->with('msg', 'Komisi telah dibayar.');
    }

    public function delete($id)
    {
        $commission = $this->commissionModel->find($id);

        if ($commission) {
            $vendorId = $commission['vendor_id'];
            $this->commissionModel->delete($id);

            // Gunakan helper CRUD untuk delete
            log_crud_activity('delete', 'komisi', $id, [
                'module' => 'commissions',
                'vendor_id' => $vendorId
            ]);

            // Buat notifikasi untuk vendor dan admin
            $this->createCommissionNotification($id, $vendorId, 'delete', $commission);

            if (!$this->request->isAJAX()) {
                return redirect()->back()->with('msg', 'Komisi telah dihapus.');
            }
        }

        return $this->response->setJSON(['ok' => true]);
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
                return false;
            }

            // Dapatkan user_id dari vendor
            $vendorUserId = $vendor['user_id'] ?? null;
            if (!$vendorUserId) {
                return false;
            }

            // Dapatkan semua admin users
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->get()
                ->getResultArray();

            // Dapatkan tim SEO yang sedang login
            $seoUserId = session()->get('user_id');
            $seoProfile = $db->table('seo_profiles')
                ->where('user_id', $seoUserId)
                ->get()
                ->getRowArray();
            $seoName = $seoProfile['name'] ?? 'Tim SEO';

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
                case 'approve':
                    $title = 'Komisi Telah Diverifikasi';
                    $message = "✅ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah diverifikasi oleh Tim SEO {$seoName}";
                    break;

                case 'reject':
                    $title = 'Komisi Ditolak';
                    $message = "❌ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah ditolak oleh Tim SEO {$seoName}";
                    break;

                case 'mark_paid':
                    $title = 'Komisi Telah Dibayar';
                    $message = "💰 Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah ditandai sebagai dibayar oleh Tim SEO {$seoName}";
                    break;

                case 'delete':
                    $title = 'Komisi Dihapus';
                    $message = "🗑️ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah dihapus oleh Tim SEO {$seoName}";
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
            $message .= "\n• Diproses oleh: {$seoName}";

            // PERBAIKAN: Notifikasi untuk VENDOR dengan type system
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
            }

            // PERBAIKAN: Notifikasi untuk semua ADMIN dengan type system
            foreach ($adminUsers as $admin) {
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

            // Insert semua notifikasi
            if (!empty($notifications)) {
                $this->notificationsModel->insertBatch($notifications);
                
                // Log untuk debugging
                log_message('info', "Created commission {$actionType} notifications for commission {$commissionId}: " . count($notifications) . " notifications sent");
                
                return true;
            }

            return false;

        } catch (\Exception $e) {
            log_message('error', "Error creating commission notification: " . $e->getMessage());
            return false;
        }
    }
}