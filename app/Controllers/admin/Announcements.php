<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\AnnouncementsModel;
use App\Models\NotificationsModel;
use App\Models\ActivityLogsModel; // Tambahkan ini

class Announcements extends BaseAdminController
{
    protected $announcementsModel;
    protected $notificationsModel;
    protected $activityLogsModel; // Tambahkan property ini

    public function __construct()
    {
        // Hapus parent::__construct() karena BaseController tidak memiliki constructor
        $this->announcementsModel = new AnnouncementsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->activityLogsModel = new ActivityLogsModel(); // Inisialisasi model ini
    }

    public function index()
    {
        // Log activity akses halaman announcements
        $this->logActivity(
            'view_announcements',
            'Mengakses halaman announcements'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Auto nonaktifkan yang sudah expired saja (expires_at < now)
        $now = date('Y-m-d H:i:s');
        $this->announcementsModel
            ->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', $now)
            ->where('status', 'active')
            ->set(['status' => 'inactive'])
            ->update();

        // Ambil semua data urut terbaru
        $items = $this->announcementsModel->orderBy('id', 'DESC')->findAll();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/announcements/index', array_merge(['items' => $items], $commonData));
    }

    public function create()
    {
        // Log activity akses form create announcement
        $this->logActivity(
            'view_create_announcement',
            'Mengakses form create announcement'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/announcements/create', $commonData);
    }

    public function store()
    {
        $data = [
            'title'      => $this->request->getPost('title'),
            'content'    => $this->request->getPost('content'),
            'audience'   => $this->request->getPost('audience') ?: 'all',
            'publish_at' => $this->request->getPost('publish_at'),
            'expires_at' => $this->request->getPost('expires_at'),
            'status'     => 'active',
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $announcementId = $this->announcementsModel->insert($data, true);

        // Log activity create announcement
        $this->logActivity(
            'create_announcement',
            'Membuat announcement baru: ' . $data['title'],
            [
                'announcement_id' => $announcementId,
                'title' => $data['title'],
                'audience' => $data['audience']
            ]
        );

        // Kirim notifikasi otomatis
        $this->sendAnnouncementNotification(
            $data['audience'],
            $data['title'],
            $data['content']
        );

        return redirect()->to(site_url('admin/announcements'))
                        ->with('success', 'Announcement berhasil dibuat dan dikirim!');
    }

    public function edit($id)
    {
        // Log activity akses form edit announcement
        $this->logActivity(
            'view_edit_announcement',
            'Mengakses form edit announcement',
            ['announcement_id' => $id]
        );

        $item = $this->announcementsModel->find($id);
        
        if (!$item) {
            return redirect()->to(site_url('admin/announcements'))
                            ->with('error', 'Announcement tidak ditemukan');
        }

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/announcements/edit', array_merge([
            'page' => 'Announcements',
            'item' => $item
        ], $commonData));
    }

    public function update($id)
    {
        $item = $this->announcementsModel->find($id);
        
        if (!$item) {
            return redirect()->to(site_url('admin/announcements'))
                            ->with('error', 'Announcement tidak ditemukan');
        }

        $data = [
            'title'      => $this->request->getPost('title'),
            'content'    => $this->request->getPost('content'),
            'audience'   => $this->request->getPost('audience') ?: 'all',
            'publish_at' => $this->request->getPost('publish_at'),
            'expires_at' => $this->request->getPost('expires_at'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // Tambahkan logika otomatis status berdasarkan waktu sekarang
        $now = time();
        $expiresAt = !empty($data['expires_at']) ? strtotime($data['expires_at']) : null;

        if ($expiresAt && $expiresAt < $now) {
            $data['status'] = 'inactive';
        } else {
            $data['status'] = 'active';
        }

        $this->announcementsModel->update($id, $data);

        // Log activity update announcement
        $this->logActivity(
            'update_announcement',
            'Memperbarui announcement: ' . $data['title'],
            [
                'announcement_id' => $id,
                'title' => $data['title'],
                'audience' => $data['audience']
            ]
        );

        // Kirim notifikasi jika audience berubah
        if ($item['audience'] !== $data['audience']) {
            $this->sendAnnouncementNotification(
                $data['audience'],
                $data['title'],
                $data['content']
            );
        }

        return redirect()->to(site_url('admin/announcements'))
                        ->with('success', 'Announcement berhasil diperbarui.');
    }

    public function delete($id)
    {
        $item = $this->announcementsModel->find($id);
        
        if (!$item) {
            return redirect()->to(site_url('admin/announcements'))
                            ->with('error', 'Announcement tidak ditemukan');
        }

        $userRole = session()->get('role');
        $userId   = session()->get('id');

        // Hapus announcement dari tabel announcements
        $this->announcementsModel->delete($id);
        
        // Hapus juga notifikasi terkait jika ada
        $db = \Config\Database::connect();
        $db->table('notifications')
        ->where('type', 'announcement')
        ->where('title', $item['title'])
        ->delete();
        
        // Log activity delete announcement
        $this->logActivity(
            'delete_announcement',
            'Menghapus announcement: ' . $item['title'],
            [
                'announcement_id' => $id,
                'title' => $item['title'],
                'action' => 'delete'
            ]
        );

        return redirect()->back()->with('success', 'Announcement berhasil dihapus.');
    }

    public function checkExpire()
    {
        $now = date('Y-m-d H:i:s');
        $this->announcementsModel
            ->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', $now)
            ->where('status', 'active')
            ->set(['status' => 'inactive'])
            ->update();
            
        // Log activity check expired announcements
        $this->logActivity(
            'check_expired_announcements',
            'Menonaktifkan announcement yang sudah expired'
        );
    }

    public function autoExpireAnnouncements()
    {
        $db = \Config\Database::connect();
        $db->table('announcements')
        ->where('expires_at <', date('Y-m-d H:i:s'))
        ->where('status', 'active')
        ->update(['status' => 'inactive']);
        
        // Log activity auto expire announcements
        $this->logActivity(
            'auto_expire_announcements',
            'Menonaktifkan announcement yang sudah expired secara otomatis'
        );
    }

    private function sendAnnouncementNotification(string $audience, string $title, string $message)
    {
        $db = db_connect();

        if ($audience === 'vendor' || $audience === 'all') {
            $vendors = $db->table('vendor_profiles')->select('id, user_id')->get()->getResultArray();

            foreach ($vendors as $v) {
                $db->table('notifications')->insert([
                    'user_id'    => $v['user_id'] ?? null,
                    'vendor_id'  => $v['id'],
                    'seo_id'     => null,
                    'type'       => 'announcement',
                    'title'      => $title,
                    'message'    => $message,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        if ($audience === 'seo_team' || $audience === 'all') {
            $seos = $db->table('seo_profiles')->select('id, user_id')->get()->getResultArray();

            foreach ($seos as $s) {
                $db->table('notifications')->insert([
                    'user_id'    => $s['user_id'] ?? null,
                    'vendor_id'  => null,
                    'seo_id'     => $s['id'],
                    'type'       => 'announcement',
                    'title'      => $title,
                    'message'    => $message,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
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
                'module'      => 'admin_announcements',
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
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Announcements: ' . $e->getMessage());
        }
    }
}