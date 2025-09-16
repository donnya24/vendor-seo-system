<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\SeoProfilesModel;

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

    protected function currentUser(): ?\CodeIgniter\Shield\Entities\User
    {
        return service('auth')->user();
    }

    protected function currentSeoId(): int
    {
        $user = $this->currentUser();
        if (!$user) return 0;

        $sp = (new SeoProfilesModel())
            ->where('user_id', (int)$user->id)
            ->first();

        return (int)($sp['id'] ?? 0);
    }

    /** Scope notifikasi utk SEO user ini (private, seo-wide, announcement) & exclude hidden */
    private function scopedBuilder()
    {
        $uid = (int) ($this->currentUser()?->id ?? 0);
        $sid = $this->currentSeoId();

        $b = $this->db->table($this->table . ' n');
        $b->select("
            n.id, n.user_id, n.seo_id, n.type, n.title, n.message,
            n.is_read AS n_is_read,
            n.created_at AS date,
            nus.is_read AS s_is_read,
            nus.hidden AS s_hidden
        ");
        $b->join('notification_user_state nus', 'nus.notification_id = n.id AND nus.user_id = '.$uid, 'left');

        $b->groupStart()
                ->where('n.user_id', $uid) // private
            ->orGroupStart()              // seo-wide
                ->where('n.user_id', null)
                ->where('n.seo_id', $sid)
            ->groupEnd()
            ->orGroupStart()              // global announcements
                ->where('n.user_id', null)
                ->where('n.seo_id', null)
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

    /** Normalisasi flag is_read */
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

        $sp = (new SeoProfilesModel())->where('user_id', $userId)->first();

        $this->logActivity($userId, (int)($sp['id'] ?? 0), 'view_notifications', 'success', 'Melihat daftar notifikasi', [
            'notifications_count' => count($items)
        ]);

        // render layout + auto-open modal notif
        return view('Seo/layouts/seo_master', [
            'title'            => 'Notifikasi',
            'profile'          => $sp ?? [],
            'notifications'    => $items,
            'stats'            => ['unread' => $unread],
            'openNotifModal'   => true,
            'suppress_content' => true,
        ]);
    }

    /** Tandai satu dibaca */
    public function markRead($id)
    {
        $id     = (int)$id;
        $userId = (int)($this->currentUser()?->id ?? 0);
        $sp     = (new SeoProfilesModel())->where('user_id', $userId)->first();

        $row = $this->scopedBuilder()->where('n.id', $id)->get()->getRowArray();
        if (!$row) {
            return $this->request->isAJAX()
                ? $this->response->setJSON(['success' => false, 'message' => 'Notifikasi tidak ditemukan'])
                : redirect()->back()->with('error', 'Notifikasi tidak ditemukan.');
        }

        if ((int)($row['user_id'] ?? 0) === $userId) {
            $this->db->table($this->table)
                ->where('id', $id)
                ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
        } else {
            $sql = "INSERT INTO notification_user_state (notification_id, user_id, is_read, read_at, hidden)
                    VALUES (?, ?, 1, NOW(), 0)
                    ON DUPLICATE KEY UPDATE is_read=VALUES(is_read), read_at=VALUES(read_at)";
            $this->db->query($sql, [$id, $userId]);
        }

        $this->logActivity($userId, (int)($sp['id'] ?? 0), 'mark_notification_read', 'success', 'Menandai notifikasi sebagai dibaca', [
            'notification_id'    => $id,
            'notification_title' => $row['title'] ?? 'Unknown',
        ]);

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true])
            : redirect()->back()->with('success', 'Notifikasi sudah ditandai dibaca.');
    }

    /** Tandai semua dibaca */
    public function markAllRead()
    {
        $userId = (int)($this->currentUser()?->id ?? 0);
        $sp     = (new SeoProfilesModel())->where('user_id', $userId)->first();

        $ids    = $this->scopedBuilder()->select('n.id, n.user_id')->get()->getResultArray();
        $marked = 0;

        if (!empty($ids)) {
            $privIds = array_column(array_filter($ids, fn($r) => (int)($r['user_id'] ?? 0) === $userId), 'id');
            if ($privIds) {
                $this->db->table($this->table)
                    ->whereIn('id', $privIds)
                    ->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1, 'read_at' => date('Y-m-d H:i:s')]);
                $marked += count($privIds);
            }

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

        $this->logActivity($userId, (int)($sp['id'] ?? 0), 'mark_all_notifications_read', 'success', 'Menandai semua notifikasi sebagai dibaca', [
            'notifications_marked' => $marked,
        ]);

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'marked_count' => $marked])
            : redirect()->back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    /** Hide satu (per-user) */
    public function delete($id)
    {
        $id     = (int)$id;
        $userId = (int)($this->currentUser()?->id ?? 0);
        $sp     = (new SeoProfilesModel())->where('user_id', $userId)->first();

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

        $this->logActivity($userId, (int)($sp['id'] ?? 0), 'hide_notification', 'success', 'Menyembunyikan notifikasi', [
            'notification_id'    => $id,
            'notification_title' => $row['title'] ?? 'Unknown',
        ]);

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true])
            : redirect()->back()->with('success', 'Notifikasi disembunyikan.');
    }

    /** Hide semua (per-user) */
    public function deleteAll()
    {
        $userId = (int)($this->currentUser()?->id ?? 0);
        $sp     = (new SeoProfilesModel())->where('user_id', $userId)->first();

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

        $this->logActivity($userId, (int)($sp['id'] ?? 0), 'hide_all_notifications', 'success', 'Menyembunyikan semua notifikasi', [
            'hidden_count' => $count,
        ]);

        return $this->request->isAJAX()
            ? $this->response->setJSON(['success' => true, 'hidden_count' => $count])
            : redirect()->back()->with('success', 'Semua notifikasi disembunyikan.');
    }

    /** Activity log helper (pakai seo_id) */
    private function logActivity($userId = null, $seoId = null, $action = null, $status = null, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'seo_id'      => $seoId,
                'module'      => 'seo_notifications',
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
            log_message('error', 'Failed to log activity in SEO Notifications: ' . $e->getMessage());
        }
    }
}