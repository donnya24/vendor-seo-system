<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;

class Commissions extends BaseController
{
    protected $commissionModel;
    protected $vendorModel;
    protected $notificationsModel;
    protected $userModel;

    public function __construct()
    {
        $this->commissionModel = new CommissionsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $status = $this->request->getGet('status');

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        $query = $this->commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left')
            ->orderBy('commissions.period_start', 'DESC');

        // Filter berdasarkan vendor_id jika dipilih
        if (!empty($vendorId)) {
            $query->where('commissions.vendor_id', $vendorId);
        }

        // Filter berdasarkan status jika dipilih
        if (!empty($status) && in_array($status, ['paid', 'unpaid'])) {
            $query->where('commissions.status', $status);
        }

        $commissions = $query->paginate(20);

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
            'pager'       => $this->commissionModel->pager,
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

        $this->commissionModel->update($id, [
            'status'      => 'paid',
            'approved_at' => date('Y-m-d H:i:s'),
            'paid_at'     => date('Y-m-d H:i:s'),
        ]);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('approve', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah diverifikasi", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id']
        ]);

        // ===== BUAT NOTIFIKASI UNTUK VENDOR DAN ADMIN =====
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

        $this->commissionModel->update($id, [
            'status'      => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
        ]);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('reject', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah ditolak", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id']
        ]);

        // ===== BUAT NOTIFIKASI UNTUK VENDOR DAN ADMIN =====
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

        $this->commissionModel->update($id, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('mark_as_paid', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah ditandai sebagai dibayar", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id']
        ]);

        // ===== BUAT NOTIFIKASI UNTUK VENDOR DAN ADMIN =====
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

            // ===== BUAT NOTIFIKASI UNTUK VENDOR DAN ADMIN =====
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
                    $message = "✅ Komisi periode {$period} sebesar Rp {$amount} untuk vendor {$vendor['business_name']} telah diverifikasi dan dibayar oleh Tim SEO {$seoName}";
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