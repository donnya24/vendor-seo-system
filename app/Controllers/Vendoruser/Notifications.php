<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\VendorProfilesModel;

class Notifications extends BaseController
{
    protected $db;
    protected $table = 'notifications';
    protected $activityLogsModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    /** ===== Helpers ===== */
    private function currentUser()
    {
        return service('auth')->user();
    }

    private function currentVendorId(): int
    {
        $vp = (new VendorProfilesModel())
                ->where('user_id', (int) $this->currentUser()->id)
                ->first();
        return (int)($vp['id'] ?? 0);
    }

    /**
     * Builder notifikasi yang boleh dilihat vendor user ini, dan belum di-hide oleh user tsb.
     * Termasuk:
     *  - Private (n.user_id = uid)
     *  - Vendor-wide (n.user_id IS NULL AND n.vendor_id = vendorId)
     *  - Global announcement (n.user_id IS NULL AND n.vendor_id IS NULL AND n.type='announcement')
     * Mengecualikan yang hidden di notification_user_state.
     */
    private function scopedBuilder()
    {
        $uid = (int) $this->currentUser()->id;
        $vid = $this->currentVendorId();

        $b = $this->db->table($this->table.' n');
        $b->select("
            n.id, n.user_id, n.vendor_id, n.type, n.title, n.message,
            n.is_read AS n_is_read,
            n.created_at AS date,
            nus.is_read AS s_is_read,
            nus.hidden AS s_hidden
        ");
        $b->join('notification_user_state nus', 'nus.notification_id = n.id AND nus.user_id = '.$uid, 'left');

        $b->groupStart()
            ->where('n.user_id', $uid)
            ->orGroupStart()
                ->where('n.user_id', null)
                ->where('n.vendor_id', $vid)
            ->groupEnd()
            ->orGroupStart()
                ->where('n.user_id', null)
                ->where('n.vendor_id', null)
                ->where('n.type', 'announcement')
            ->groupEnd()
        ->groupEnd();

        $b->groupStart()
            ->where('nus.hidden', 0)
            ->orWhere('nus.hidden IS NULL', null, false)
        ->groupEnd();

        return $b;
    }

    /** Normalisasi flag is_read untuk tiap baris (private = pakai n.is_read, vendor/global = pakai s_is_read/null->0) */
    private function normalizeRows(array $rows): array
    {
        $uid = (int) $this->currentUser()->id;

        return array_map(function($r) use ($uid) {
            $isPrivate = ((int)($r['user_id'] ?? 0) === $uid);
            $r['is_read'] = $isPrivate ? (int)($r['n_is_read'] ?? 0) : (int)($r['s_is_read'] ?? 0);
            unset($r['n_is_read'], $r['s_is_read'], $r['s_hidden']);
            return $r;
        }, $rows);
    }

    /** ===== Index (list) ===== */
    public function index()
    {
        $userId = (int) $this->currentUser()->id;

        $items = $this->scopedBuilder()
            ->orderBy('n.created_at', 'DESC')
            ->get()->getResultArray();

        $items = $this->normalizeRows($items);

        $this->logActivity($userId, null, 'view_notifications', 'success', 'Melihat daftar notifikasi', [
            'notifications_count' => count($items)
        ]);

        return view('vendoruser/notifications/index', [
            'page'  => 'Notifikasi',
            'items' => $items
        ]);
    }

    /** ===== Tandai satu notifikasi dibaca ===== */
    public function markRead($id)
    {
        $id    = (int) $id;
        $userId = (int) $this->currentUser()->id;

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Notifikasi tidak ditemukan.');
        }

        // Private → update langsung di notifications
        if ((int)($row['user_id'] ?? 0) === $userId) {
            $this->db->table($this->table)
                ->where('id', $id)
                ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        } else {
            // Vendor/global → upsert status per-user
            $sql = "INSERT INTO notification_user_state (notification_id, user_id, is_read, read_at, hidden)
                    VALUES (?, ?, 1, NOW(), 0)
                    ON DUPLICATE KEY UPDATE is_read=VALUES(is_read), read_at=VALUES(read_at)";
            $this->db->query($sql, [$id, $userId]);
        }

        $this->logActivity($userId, null, 'mark_notification_read', 'success', 'Menandai notifikasi sebagai dibaca', [
            'notification_id' => $id,
            'notification_title' => $row['title'] ?? 'Unknown'
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Notifikasi sudah ditandai dibaca.');
    }

    /** ===== Tandai semua notifikasi dibaca ===== */
    public function markAllRead()
    {
        $userId = (int) $this->currentUser()->id;

        // Ambil semua ID dalam scope (yang belum di-hide)
        $ids = $this->scopedBuilder()->select('n.id, n.user_id')->get()->getResultArray();

        $marked = 0;

        if (!empty($ids)) {
            // 1) Private → update di notifications
            $privIds = array_column(array_filter($ids, fn($r) => (int)($r['user_id'] ?? 0) === $userId), 'id');
            if (!empty($privIds)) {
                $this->db->table($this->table)
                    ->whereIn('id', $privIds)
                    ->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
                $marked += count($privIds);
            }

            // 2) Vendor/global → upsert per-user
            $vgIds   = array_column(array_filter($ids, fn($r) => (int)($r['user_id'] ?? 0) !== $userId), 'id');
            if (!empty($vgIds)) {
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
            'notifications_marked' => $marked
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'marked_count' => $marked]);
        }
        return redirect()->back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    /** ===== “Hapus” satu (hide per-user) ===== */
    public function delete($id)
    {
        $id    = (int) $id;
        $userId = (int) $this->currentUser()->id;

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan']);
            }
            return redirect()->back()->with('error', 'Notifikasi tidak ditemukan.');
        }

        // Hide per-user (baik private maupun vendor/global)
        $sql = "INSERT INTO notification_user_state (notification_id, user_id, hidden, hidden_at)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE hidden=VALUES(hidden), hidden_at=VALUES(hidden_at)";
        $this->db->query($sql, [$id, $userId]);

        $this->logActivity($userId, null, 'hide_notification', 'success', 'Menyembunyikan notifikasi', [
            'notification_id' => $id,
            'notification_title' => $row['title'] ?? 'Unknown'
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Notifikasi disembunyikan.');
    }

    /** ===== “Hapus semua” (hide semua per-user) ===== */
    public function deleteAll()
    {
        $userId = (int) $this->currentUser()->id;

        $ids = $this->scopedBuilder()->select('n.id')->get()->getResultArray();
        $count = count($ids);

        if ($count > 0) {
            $values = [];
            foreach ($ids as $r) {
                $values[] = '(' . (int)$r['id'] . ',' . $userId . ',1,NOW())';
            }
            $sql = "INSERT INTO notification_user_state (notification_id, user_id, hidden, hidden_at)
                    VALUES " . implode(',', $values) . "
                    ON DUPLICATE KEY UPDATE hidden=VALUES(hidden), hidden_at=VALUES(hidden_at)";
            $this->db->query($sql);
        }

        $this->logActivity($userId, null, 'hide_all_notifications', 'success', 'Menyembunyikan semua notifikasi', [
            'hidden_count' => $count
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'hidden_count' => $count]);
        }
        return redirect()->back()->with('success', 'Semua notifikasi disembunyikan.');
    }

    /** ===== Activity log ===== */
    private function logActivity($userId = null, $vendorId = null, $action, $status, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'vendor_id'   => $vendorId,
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
            log_message('error', 'Failed to log activity in Notifications: ' . $e->getMessage());
        }
    }
}
