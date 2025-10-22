<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\AdminProfileModel;

class Notifications extends BaseController
{
    protected $db;
    protected string $table = 'notifications';
    protected ActivityLogsModel $activityLogsModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    /** ========== Helpers ========== */

    protected function currentUser(): ?\CodeIgniter\Shield\Entities\User
    {
        return service('auth')->user();
    }

    protected function currentAdminId(): int
    {
        $user = $this->currentUser();
        if (!$user) return 0;

        $ap = (new AdminProfileModel())
            ->where('user_id', (int) $user->id)
            ->first();

        return (int)($ap['id'] ?? 0);
    }

    /**
     * Scope notifikasi yg boleh dilihat admin user ini dan belum di-hide:
     *  - Private (n.user_id = uid)
     *  - Admin-wide (n.user_id IS NULL AND n.admin_id = adminId)
     *  - Global announcement (n.user_id IS NULL AND n.admin_id IS NULL AND n.type='announcement')
     *  - Exclude yg hidden (di notification_user_state)
     */
    private function scopedBuilder()
    {
        $uid = (int) ($this->currentUser()?->id ?? 0);
        $aid = $this->currentAdminId();

        $b = $this->db->table($this->table . ' n');
        $b->select("
            n.id, n.user_id, n.admin_id, n.seo_id, n.vendor_id, n.type, n.title, n.message,
            n.is_read AS n_is_read,
            n.created_at AS date,
            nus.is_read AS s_is_read,
            nus.hidden AS s_hidden
        ");
        $b->join('notification_user_state nus', 'nus.notification_id = n.id AND nus.user_id = '.$uid, 'left');

        $b->groupStart()
                ->where('n.user_id', $uid) // private
            ->orGroupStart()              // admin-wide
                ->where('n.user_id', null)
                ->where('n.admin_id', $aid)
            ->groupEnd()
            ->orGroupStart()              // global announcements
                ->where('n.user_id', null)
                ->where('n.admin_id', null)
                ->where('n.type', 'announcement')
            ->groupEnd()
        ->groupEnd();

        // Exclude hidden
        $b->groupStart()
            ->where('nus.hidden', 0)
            ->orWhere('nus.hidden IS NULL', null, false)
        ->groupEnd();

        return $b;
    }

    /** Normalisasi flag is_read (private pakai n.is_read; admin/global pakai s_is_read) */
    private function normalizeRows(array $rows): array
    {
        $uid = (int) ($this->currentUser()?->id ?? 0);

        return array_map(function ($r) use ($uid) {
            $isPrivate    = ((int)($r['user_id'] ?? 0) === $uid);
            $r['is_read'] = $isPrivate ? (int)($r['n_is_read'] ?? 0) : (int)($r['s_is_read'] ?? 0);
            $r['date']    = !empty($r['date']) ? date('Y-m-d H:i', strtotime($r['date'])) : '-';
            unset($r['n_is_read'], $r['s_is_read'], $r['s_hidden']);
            return $r;
        }, $rows);
    }

    public function index()
    {
        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $items = $this->scopedBuilder()
            ->orderBy('n.created_at', 'DESC')
            ->get()->getResultArray();

        $items  = $this->normalizeRows($items);
        $unread = 0;
        foreach ($items as $it) { if (empty($it['is_read'])) $unread++; }

        $this->logActivity($userId, null, 'view_notifications', 'success', 'Melihat daftar notifikasi', [
            'notifications_count' => count($items)
        ]);

        $ap = (new AdminProfileModel())->where('user_id', $userId)->first();

        return view('admin/layouts/admin_master', [
            'title'            => 'Notifikasi',
            'ap'               => $ap ?? [],
            'notifications'    => $items,
            'stats'            => ['unread' => $unread],
            'openNotifModal'   => true,
            'suppress_content' => true,
        ]);
    }

    /** Get modal data via AJAX */
    public function modalData()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $items = $this->scopedBuilder()
            ->orderBy('n.created_at', 'DESC')
            ->get()->getResultArray();

        $items = $this->normalizeRows($items);
        $unread = 0;
        foreach ($items as $it) { 
            if (empty($it['is_read'])) $unread++; 
        }

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $items,
            'unread' => $unread
        ]);
    }

    /** Get unread notification count */
    public function getUnreadCount()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $items = $this->scopedBuilder()
            ->orderBy('n.created_at', 'DESC')
            ->get()->getResultArray();

        $items  = $this->normalizeRows($items);
        $unread = 0;
        foreach ($items as $it) { 
            if (empty($it['is_read'])) $unread++; 
        }

        return $this->response->setJSON([
            'success' => true,
            'unread' => $unread
        ]);
    }

    /** Refresh CSRF token */
    public function refreshCSRF()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Generate new CSRF token
        $csrf = csrf_hash();
        
        return $this->response->setJSON([
            'success' => true,
            'token' => $csrf
        ]);
    }

    /** Tandai satu notifikasi dibaca */
    public function markRead($id)
    {
        $id     = (int) $id;
        $userId = (int) ($this->currentUser()?->id ?? 0);

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan']);
        }

        if ((int)($row['user_id'] ?? 0) === $userId) {
            // Private → update langsung di notifications
            $this->db->table($this->table)
                ->where('id', $id)
                ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        } else {
            // Admin/global → upsert status per-user
            $sql = "INSERT INTO notification_user_state (notification_id, user_id, is_read, read_at, hidden)
                    VALUES (?, ?, 1, NOW(), 0)
                    ON DUPLICATE KEY UPDATE is_read=VALUES(is_read), read_at=VALUES(read_at)";
            $this->db->query($sql, [$id, $userId]);
        }

        $this->logActivity($userId, null, 'mark_notification_read', 'success', 'Menandai notifikasi sebagai dibaca', [
            'notification_id'    => $id,
            'notification_title' => $row['title'] ?? 'Unknown',
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /** Tandai semua dibaca */
    public function markAllRead()
    {
        $userId = (int) ($this->currentUser()?->id ?? 0);

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $ids = $this->scopedBuilder()->select('n.id, n.user_id')->get()->getResultArray();
        $marked = 0;

        if (!empty($ids)) {
            // Private
            $privIds = array_column(array_filter($ids, fn($r) => (int)($r['user_id'] ?? 0) === $userId), 'id');
            if ($privIds) {
                $this->db->table($this->table)
                    ->whereIn('id', $privIds)
                    ->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
                $marked += count($privIds);
            }

            // Admin/global
            $vgIds = array_column(array_filter($ids, fn($r) => (int)($r['user_id'] ?? 0) !== $userId), 'id');
            if ($vgIds) {
                $values = [];
                foreach ($vgIds as $nid) {
                    $values[] = '(' . (int)$nid . ',' . $userId . ',1,NOW(),0)';
                }
                $sql = "INSERT INTO notification_user_state (notification_id, user_id, is_read, read_at, hidden)
                        VALUES " . implode(',', $values) . "
                        ON DUPLICATE KEY UPDATE is_read=VALUES(is_read), read_at=VALUES(read_at)";
                $this->db->query($sql);
                $marked += count($vgIds);
            }
        }

        $this->logActivity($userId, null, 'mark_all_notifications_read', 'success', 'Menandai semua notifikasi sebagai dibaca', [
            'notifications_marked' => $marked,
        ]);

        return $this->response->setJSON(['success' => true, 'marked_count' => $marked]);
    }

    /** Hapus satu notifikasi (permanen dari database) */
    public function delete($id)
    {
        $id     = (int) $id;
        $userId = (int) ($this->currentUser()?->id ?? 0);

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan']);
        }

        // Hapus permanen dari database
        $this->db->table($this->table)->where('id', $id)->delete();
        
        // Hapus juga dari notification_user_state jika ada
        $this->db->table('notification_user_state')->where('notification_id', $id)->delete();

        $this->logActivity($userId, null, 'delete_notification', 'success', 'Menghapus notifikasi', [
            'notification_id'    => $id,
            'notification_title' => $row['title'] ?? 'Unknown',
        ]);

        return $this->response->setJSON(['success' => true]);
    }

    /** Hapus semua notifikasi (permanen dari database) */
    public function deleteAll()
    {
        $userId = (int) ($this->currentUser()?->id ?? 0);

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        $ids   = $this->scopedBuilder()->select('n.id')->get()->getResultArray();
        $count = count($ids);

        if ($count > 0) {
            $notificationIds = array_column($ids, 'id');
            
            // Hapus permanen dari database
            $this->db->table($this->table)->whereIn('id', $notificationIds)->delete();
            
            // Hapus juga dari notification_user_state
            $this->db->table('notification_user_state')->whereIn('notification_id', $notificationIds)->delete();
        }

        $this->logActivity($userId, null, 'delete_all_notifications', 'success', 'Menghapus semua notifikasi', [
            'deleted_count' => $count,
        ]);

        return $this->response->setJSON(['success' => true, 'deleted_count' => $count]);
    }

    /**
     * Kirim notifikasi komisi dari vendor ke admin & SEO
     */
    public function sendCommissionNotification($vendorData, $action = 'insert')
    {
        $db = db_connect();
        
        // Dapatkan admin yang sedang login
        $currentUserId = (int) ($this->currentUser()?->id ?? 0);
        
        // 1. Ambil SEMUA user dengan group 'admin' dan 'seoteam' KECUALI admin yang sedang login
        $targetUsers = $db->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('group', ['admin', 'seoteam'])
            ->where('user_id !=', $currentUserId)  // Kecualikan admin yang sedang login
            ->get()
            ->getResultArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUsers)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $commissionText = '';
        if ($vendorData['commission_type'] === 'percent') {
            $commissionText = $vendorData['requested_commission'] . '%';
        } else {
            $commissionText = 'Rp ' . number_format($vendorData['requested_commission_nominal'], 0, ',', '.');
        }
        
        $actionText = '';
        if ($action === 'insert') {
            $actionText = 'mengajukan komisi';
        } elseif ($action === 'edit') {
            $actionText = 'mengubah pengajuan komisi';
        }
        
        $title = 'Pengajuan/Perubahan Komisi Vendor';
        $message = 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                    ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                    ') ' . $actionText . ' ' . $commissionText . '.';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan array data untuk di-insert secara batch
        $notificationsToInsert = [];
        foreach ($targetUsers as $targetUser) {
            $notification = [
                'user_id'    => $targetUser['user_id'],
                'title'      => $title,
                'message'    => $message,
                'type'       => 'commission_request',
                'is_read'    => 0,
                'created_at' => $now,
            ];
            
            // Set vendor_id untuk semua notifikasi
            $notification['vendor_id'] = $vendorData['user_id'];
            
            // Set admin_id jika user adalah admin
            if ($targetUser['group'] === 'admin') {
                $notification['admin_id'] = $targetUser['user_id'];
            }
            
            // Set seo_id jika user adalah seoteam
            if ($targetUser['group'] === 'seoteam') {
                $notification['seo_id'] = $targetUser['user_id'];
            }
            
            $notificationsToInsert[] = $notification;
        }

        // 4. Insert semua notifikasi dalam satu kali query
        try {
            $db->table('notifications')->insertBatch($notificationsToInsert);
            log_message('info', 'SUKSES: Berhasil mengirim ' . count($notificationsToInsert) . ' notifikasi komisi.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi laporan leads dari vendor ke admin & SEO
     */
    public function sendLeadsReportNotification($vendorData, $leadsData)
    {
        $db = db_connect();
        
        // Dapatkan admin yang sedang login
        $currentUserId = (int) ($this->currentUser()?->id ?? 0);
        
        // 1. Ambil SEMUA user dengan group 'admin' dan 'seoteam' KECUALI admin yang sedang login
        $targetUsers = $db->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('group', ['admin', 'seoteam'])
            ->where('user_id !=', $currentUserId)  // Kecualikan admin yang sedang login
            ->get()
            ->getResultArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUsers)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $title = 'Laporan Leads Vendor';
        $message = 'Vendor ' . ($vendorData['business_name'] ?? '-') .
                    ' (Pemilik: ' . ($vendorData['owner_name'] ?? '-') .
                    ') mengirim laporan leads dengan ' . (count($leadsData) ?? 0) . ' data leads.';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan array data untuk di-insert secara batch
        $notificationsToInsert = [];
        foreach ($targetUsers as $targetUser) {
            $notification = [
                'user_id'    => $targetUser['user_id'],
                'title'      => $title,
                'message'    => $message,
                'type'       => 'leads_report',
                'is_read'    => 0,
                'created_at' => $now,
            ];
            
            // Set vendor_id untuk semua notifikasi
            $notification['vendor_id'] = $vendorData['user_id'];
            
            // Set admin_id jika user adalah admin
            if ($targetUser['group'] === 'admin') {
                $notification['admin_id'] = $targetUser['user_id'];
            }
            
            // Set seo_id jika user adalah seoteam
            if ($targetUser['group'] === 'seoteam') {
                $notification['seo_id'] = $targetUser['user_id'];
            }
            
            $notificationsToInsert[] = $notification;
        }

        // 4. Insert semua notifikasi dalam satu kali query
        try {
            $db->table('notifications')->insertBatch($notificationsToInsert);
            log_message('info', 'SUKSES: Berhasil mengirim ' . count($notificationsToInsert) . ' notifikasi laporan leads.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi pengumuman umum dari admin ke vendor & SEO
     */
    public function sendAnnouncementNotification($announcementData)
    {
        $db = db_connect();
        
        // Dapatkan admin yang sedang login
        $currentUserId = (int) ($this->currentUser()?->id ?? 0);
        
        // 1. Ambil SEMUA user dengan group 'vendor' dan 'seoteam' KECUALI admin yang sedang login
        $targetUsers = $db->table('auth_groups_users')
            ->select('user_id, group')
            ->whereIn('group', ['vendor', 'seoteam'])
            ->where('user_id !=', $currentUserId)  // Kecualikan admin yang sedang login
            ->get()
            ->getResultArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUsers)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $title = $announcementData['title'] ?? 'Pengumuman Umum';
        $message = $announcementData['message'] ?? '';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan array data untuk di-insert secara batch
        $notificationsToInsert = [];
        foreach ($targetUsers as $targetUser) {
            $notification = [
                'user_id'    => $targetUser['user_id'],
                'title'      => $title,
                'message'    => $message,
                'type'       => 'announcement',
                'is_read'    => 0,
                'created_at' => $now,
            ];
            
            // Set admin_id untuk semua notifikasi
            $notification['admin_id'] = $this->currentAdminId();
            
            // Set seo_id jika user adalah seoteam
            if ($targetUser['group'] === 'seoteam') {
                $notification['seo_id'] = $targetUser['user_id'];
            }
            
            // Set vendor_id jika user adalah vendor
            if ($targetUser['group'] === 'vendor') {
                $notification['vendor_id'] = $targetUser['user_id'];
            }
            
            $notificationsToInsert[] = $notification;
        }

        // 4. Insert semua notifikasi dalam satu kali query
        try {
            $db->table('notifications')->insertBatch($notificationsToInsert);
            log_message('info', 'SUKSES: Berhasil mengirim ' . count($notificationsToInsert) . ' notifikasi pengumuman.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi inactive/active tim SEO dari admin ke SEO
     */
    public function sendSeoStatusNotification($seoData, $status)
    {
        $db = db_connect();
        
        // Dapatkan admin yang sedang login
        $currentUserId = (int) ($this->currentUser()?->id ?? 0);
        
        // 1. Ambil SEMUA user dengan group 'seoteam' KECUALI admin yang sedang login
        $targetUsers = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'seoteam')
            ->where('user_id !=', $currentUserId)  // Kecualikan admin yang sedang login
            ->get()
            ->getResultArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUsers)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $statusText = $status === 'active' ? 'diaktifkan' : 'dinonaktifkan';
        $title = 'Status Tim SEO';
        $message = 'Tim SEO ' . ($seoData['name'] ?? '-') . ' telah ' . $statusText . ' oleh admin.';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan array data untuk di-insert secara batch
        $notificationsToInsert = [];
        foreach ($targetUsers as $targetUser) {
            $notification = [
                'user_id'    => $targetUser['user_id'],
                'title'      => $title,
                'message'    => $message,
                'type'       => 'seo_status',
                'is_read'    => 0,
                'created_at' => $now,
            ];
            
            // Set admin_id dan seo_id untuk semua notifikasi
            $notification['admin_id'] = $this->currentAdminId();
            $notification['seo_id'] = $targetUser['user_id'];
            
            $notificationsToInsert[] = $notification;
        }

        // 4. Insert semua notifikasi dalam satu kali query
        try {
            $db->table('notifications')->insertBatch($notificationsToInsert);
            log_message('info', 'SUKSES: Berhasil mengirim ' . count($notificationsToInsert) . ' notifikasi status SEO.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi status pengajuan komisi dari admin ke vendor
     */
    public function sendCommissionStatusNotification($vendorData, $status)
    {
        $db = db_connect();
        
        // 1. Ambil user vendor
        $targetUser = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'vendor')
            ->where('user_id', $vendorData['user_id'])
            ->get()
            ->getRowArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUser)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $statusText = '';
        if ($status === 'accepted') {
            $statusText = 'diterima';
        } elseif ($status === 'rejected') {
            $statusText = 'ditolak';
        } elseif ($status === 'inactive') {
            $statusText = 'dinonaktifkan';
        }
        
        $title = 'Status Pengajuan Komisi';
        $message = 'Pengajuan komisi Anda telah ' . $statusText . ' oleh admin.';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan data notifikasi
        $notification = [
            'user_id'    => $targetUser['user_id'],
            'title'      => $title,
            'message'    => $message,
            'type'       => 'commission_status',
            'is_read'    => 0,
            'created_at' => $now,
        ];
        
        // Set admin_id dan vendor_id untuk notifikasi
        $notification['admin_id'] = $this->currentAdminId();
        $notification['vendor_id'] = $vendorData['user_id'];

        // 4. Insert notifikasi
        try {
            $db->table('notifications')->insert($notification);
            log_message('info', 'SUKSES: Berhasil mengirim notifikasi status komisi.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi komisi "paid" dari admin ke vendor
     */
    public function sendCommissionPaidNotification($vendorData, $commissionData)
    {
        $db = db_connect();
        
        // 1. Ambil user vendor
        $targetUser = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'vendor')
            ->where('user_id', $vendorData['user_id'])
            ->get()
            ->getRowArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUser)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $commissionText = '';
        if ($commissionData['commission_type'] === 'percent') {
            $commissionText = $commissionData['commission'] . '%';
        } else {
            $commissionText = 'Rp ' . number_format($commissionData['commission_nominal'], 0, ',', '.');
        }
        
        $title = 'Pembayaran Komisi';
        $message = 'Komisi sebesar ' . $commissionText . ' telah dibayarkan. Terima kasih atas kerjasamanya.';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan data notifikasi
        $notification = [
            'user_id'    => $targetUser['user_id'],
            'title'      => $title,
            'message'    => $message,
            'type'       => 'commission_paid',
            'is_read'    => 0,
            'created_at' => $now,
        ];
        
        // Set admin_id dan vendor_id untuk notifikasi
        $notification['admin_id'] = $this->currentAdminId();
        $notification['vendor_id'] = $vendorData['user_id'];

        // 4. Insert notifikasi
        try {
            $db->table('notifications')->insert($notification);
            log_message('info', 'SUKSES: Berhasil mengirim notifikasi pembayaran komisi.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi status pengajuan komisi dari SEO ke vendor
     */
    public function sendSeoCommissionStatusNotification($vendorData, $status, $seoData)
    {
        $db = db_connect();
        
        // 1. Ambil user vendor
        $targetUser = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'vendor')
            ->where('user_id', $vendorData['user_id'])
            ->get()
            ->getRowArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUser)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $statusText = '';
        if ($status === 'accepted') {
            $statusText = 'diterima';
        } elseif ($status === 'rejected') {
            $statusText = 'ditolak';
        } elseif ($status === 'inactive') {
            $statusText = 'dinonaktifkan';
        }
        
        $title = 'Status Pengajuan Komisi';
        $message = 'Pengajuan komisi Anda telah ' . $statusText . ' oleh tim SEO (' . ($seoData['name'] ?? '-') . ').';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan data notifikasi
        $notification = [
            'user_id'    => $targetUser['user_id'],
            'title'      => $title,
            'message'    => $message,
            'type'       => 'commission_status',
            'is_read'    => 0,
            'created_at' => $now,
        ];
        
        // Set seo_id dan vendor_id untuk notifikasi
        $notification['seo_id'] = $seoData['user_id'];
        $notification['vendor_id'] = $vendorData['user_id'];

        // 4. Insert notifikasi
        try {
            $db->table('notifications')->insert($notification);
            log_message('info', 'SUKSES: Berhasil mengirim notifikasi status komisi dari SEO.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Kirim notifikasi keyword proses/targets dari SEO ke vendor
     */
    public function sendKeywordNotification($vendorData, $keywordData, $seoData)
    {
        $db = db_connect();
        
        // 1. Ambil user vendor
        $targetUser = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'vendor')
            ->where('user_id', $vendorData['user_id'])
            ->get()
            ->getRowArray();
        
        // Jika tidak ada user yang ditemukan, hentikan proses
        if (empty($targetUser)) {
            return;
        }
        
        // 2. Siapkan data notifikasi
        $statusText = '';
        if ($keywordData['status'] === 'process') {
            $statusText = 'dalam proses';
        } elseif ($keywordData['status'] === 'target') {
            $statusText = 'ditetapkan sebagai target';
        }
        
        $title = 'Status Keyword';
        $message = 'Keyword "' . ($keywordData['keyword'] ?? '-') . '" telah ' . $statusText . ' oleh tim SEO (' . ($seoData['name'] ?? '-') . ').';
        
        $now = date('Y-m-d H:i:s');

        // 3. Siapkan data notifikasi
        $notification = [
            'user_id'    => $targetUser['user_id'],
            'title'      => $title,
            'message'    => $message,
            'type'       => 'keyword_status',
            'is_read'    => 0,
            'created_at' => $now,
        ];
        
        // Set seo_id dan vendor_id untuk notifikasi
        $notification['seo_id'] = $seoData['user_id'];
        $notification['vendor_id'] = $vendorData['user_id'];

        // 4. Insert notifikasi
        try {
            $db->table('notifications')->insert($notification);
            log_message('info', 'SUKSES: Berhasil mengirim notifikasi status keyword.');
        } catch (\Throwable $e) {
            log_message('error', 'GAGAL INSERT NOTIFIKASI: ' . $e->getMessage());
            throw $e;
        }
    }

    /** Activity log helper */
    private function logActivity($userId = null, $adminId = null, $action = null, $status = null, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'admin_id'    => $adminId,
                'module'      => 'notifications',
                'action'      => $action,
                'status'      => $status,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }
            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Admin Notifications: ' . $e->getMessage());
        }
    }
}