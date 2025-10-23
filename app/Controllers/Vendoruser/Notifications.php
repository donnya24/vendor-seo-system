<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\VendorProfilesModel;

class Notifications extends BaseController
{
    protected $db;
    protected string $table = 'notifications';
    protected ActivityLogsModel $activityLogsModel;
    protected VendorProfilesModel $vendorProfilesModel;

    private $vendorProfile;
    private $vendorId;
    private $isVerified;

    public function __construct()
    {
        $this->db = db_connect();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->initVendor();
    }

    /** ========== Helpers ========== */

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = $this->vendorProfilesModel
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? 0;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorId;
    }

    private function withVendorData(array $data = []): array
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    protected function currentUser(): ?\CodeIgniter\Shield\Entities\User
    {
        return service('auth')->user();
    }

    protected function currentVendorId(): int
    {
        return $this->vendorId;
    }

    /**
     * Scope notifikasi yg boleh dilihat vendor user ini dan belum di-hide:
     *  - Private (n.user_id = uid)
     *  - Vendor-wide (n.user_id IS NULL AND n.vendor_id = vendorId)
     *  - Global announcement (n.user_id IS NULL AND n.vendor_id IS NULL AND n.type='announcement')
     *  - Exclude yg hidden (di notification_user_state)
     */
    private function scopedBuilder()
    {
        $uid = (int) ($this->currentUser()?->id ?? 0);
        $vid = $this->currentVendorId();

        $b = $this->db->table($this->table . ' n');
        $b->select("
            n.id, n.user_id, n.vendor_id, n.type, n.title, n.message,
            n.is_read AS n_is_read,
            n.created_at AS date,
            nus.is_read AS s_is_read,
            nus.hidden AS s_hidden
        ");
        $b->join('notification_user_state nus', 'nus.notification_id = n.id AND nus.user_id = '.$uid, 'left');

        $b->groupStart()
                ->where('n.user_id', $uid) // private
            ->orGroupStart()              // vendor-wide
                ->where('n.user_id', null)
                ->where('n.vendor_id', $vid)
            ->groupEnd()
            ->orGroupStart()              // global announcements
                ->where('n.user_id', null)
                ->where('n.vendor_id', null)
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

    /** Normalisasi flag is_read (private pakai n.is_read; vendor/global pakai s_is_read) */
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
        if (! $this->vendorId) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dahulu.');
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $items = $this->scopedBuilder()
            ->orderBy('n.created_at', 'DESC')
            ->get()->getResultArray();

        $items  = $this->normalizeRows($items);
        $unread = 0;
        foreach ($items as $it) { if (empty($it['is_read'])) $unread++; }

        // Log aktivitas view notifications
        if (function_exists('log_activity_auto')) {
            log_activity_auto('view', 'Melihat daftar notifikasi (' . count($items) . ' item)', [
                'module' => 'vendor_notifications',
                'vendor_id' => $this->vendorId
            ]);
        }

        // 👉 BUKA POPUP: openNotifModal = true, dan suppress_content = true
        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'            => 'Notifikasi',
            'notifications'    => $items,
            'stats'            => ['unread' => $unread],
            'openNotifModal'   => true,       // <-- auto-check modal
            'suppress_content' => true,       // <-- jangan render placeholder konten
            // TIDAK perlu content_view/content_data di sini
        ]));
    }

    // Tambahkan method ini di Notifications controller
    public function getCsrfToken()
    {
        return $this->response->setJSON([
            'success' => true,
            'csrf_token' => csrf_hash(),
            'csrf_header' => csrf_header()
        ]);
    }

    /** Tandai satu notifikasi dibaca */
    public function markRead($id)
    {
        if (! $this->vendorId) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])
                : redirect()->back()->with('error', 'Unauthorized.');
        }

        $id     = (int) $id;
        $userId = (int) ($this->currentUser()?->id ?? 0);

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan'])
                : redirect()->back()->with('error', 'Notifikasi tidak ditemukan.');
        }

        if ((int)($row['user_id'] ?? 0) === $userId) {
            // Private → update langsung di notifications
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

        // Log aktivitas mark read
        if (function_exists('log_activity_auto')) {
            log_activity_auto('update', 'Menandai notifikasi sebagai dibaca: ' . ($row['title'] ?? 'Unknown'), [
                'module' => 'vendor_notifications',
                'vendor_id' => $this->vendorId,
                'notification_id' => $id
            ]);
        }

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true])
            : redirect()->back()->with('success', 'Notifikasi sudah ditandai dibaca.');
    }

    /** Tandai semua dibaca */
    public function markAllRead()
    {
        if (! $this->vendorId) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])
                : redirect()->back()->with('error', 'Unauthorized.');
        }

        $userId = (int) ($this->currentUser()?->id ?? 0);

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

            // Vendor/global
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

        // Log aktivitas mark all read
        if (function_exists('log_activity_auto')) {
            log_activity_auto('update', 'Menandai semua notifikasi sebagai dibaca (' . $marked . ' item)', [
                'module' => 'vendor_notifications',
                'vendor_id' => $this->vendorId
            ]);
        }

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'marked_count' => $marked])
            : redirect()->back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    /** Hide satu (per-user) */
    public function delete($id)
    {
        if (! $this->vendorId) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])
                : redirect()->back()->with('error', 'Unauthorized.');
        }

        $id     = (int) $id;
        $userId = (int) ($this->currentUser()?->id ?? 0);

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan'])
                : redirect()->back()->with('error', 'Notifikasi tidak ditemukan.');
        }

        $sql = "INSERT INTO notification_user_state (notification_id, user_id, hidden, hidden_at)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE hidden=VALUES(hidden), hidden_at=VALUES(hidden_at)";
        $this->db->query($sql, [$id, $userId]);

        // Log aktivitas delete/hide
        if (function_exists('log_activity_auto')) {
            log_activity_auto('delete', 'Menyembunyikan notifikasi: ' . ($row['title'] ?? 'Unknown'), [
                'module' => 'vendor_notifications',
                'vendor_id' => $this->vendorId,
                'notification_id' => $id
            ]);
        }

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true])
            : redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    /** Hide semua (per-user) */
    public function deleteAll()
    {
        if (! $this->vendorId) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])
                : redirect()->back()->with('error', 'Unauthorized.');
        }

        $userId = (int) ($this->currentUser()?->id ?? 0);

        $ids   = $this->scopedBuilder()->select('n.id')->get()->getResultArray();
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

        // Log aktivitas delete all
        if (function_exists('log_activity_auto')) {
            log_activity_auto('delete', 'Menyembunyikan semua notifikasi (' . $count . ' item)', [
                'module' => 'vendor_notifications',
                'vendor_id' => $this->vendorId
            ]);
        }

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'hidden_count' => $count])
            : redirect()->back()->with('success', 'Semua notifikasi disembunyikan.');
    }

    public function getNotificationsAjax()
    {
        if (!$this->vendorId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        $items = $this->scopedBuilder()
            ->orderBy('n.created_at', 'DESC')
            ->get()->getResultArray();

        $items = $this->normalizeRows($items);
        $unread = 0;
        foreach ($items as $it) { if (empty($it['is_read'])) $unread++; }

        return $this->response->setJSON([
            'success' => true,
            'notifications' => $items,
            'unread' => $unread
        ]);
    }
}