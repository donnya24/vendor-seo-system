<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AnnouncementsModel;

class Announcements extends BaseController
{
public function index()
{
    $model = new AnnouncementsModel();

    // Auto nonaktifkan yang sudah expired saja (expires_at < now)
    $now = date('Y-m-d H:i:s');
    $model->where('expires_at IS NOT NULL', null, false)
          ->where('expires_at <', $now)
          ->where('status', 'active')
          ->set(['status' => 'inactive'])
          ->update();

    // Ambil semua data urut terbaru
    $items = $model->orderBy('id', 'DESC')->findAll();

    return view('admin/announcements/index', ['items' => $items]);
}

public function create()
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

    $model = new \App\Models\AnnouncementsModel();
    $model->insert($data);

    // 🧩 Taruh di SINI ⤵️
    $this->sendAnnouncementNotification(
        $data['audience'],
        $data['title'],
        $data['content']
    );

    return redirect()->to(site_url('admin/announcements'))
                     ->with('success', 'Announcement berhasil dibuat dan notifikasi dikirim.');
}

    public function edit($id)
    {
        $item = (new AnnouncementsModel())->find($id);
        return view('admin/announcements/edit', [
            'page' => 'Announcements',
            'item' => $item
        ]);
    }

public function store()
{
    $model = new AnnouncementsModel();

    $data = [
        'title'      => $this->request->getPost('title'),
        'content'    => $this->request->getPost('content'),
        'audience'   => $this->request->getPost('audience'),
        'publish_at' => $this->request->getPost('publish_at'),
        'expires_at' => $this->request->getPost('expires_at'),
        'status'     => 'active',
        'created_at' => date('Y-m-d H:i:s'),
    ];

    $announcementId = $model->insert($data, true);

    // 🔥 kirim notifikasi otomatis
    $this->sendAnnouncementNotification(
        $data['audience'],
        $data['title'],
        $data['content']
    );

    return redirect()->to(site_url('admin/announcements'))
                     ->with('success', 'Announcement berhasil dibuat dan dikirim!');
}

public function update($id)
{
    $data = [
        'title'      => $this->request->getPost('title'),
        'content'    => $this->request->getPost('content'),
        'audience'   => $this->request->getPost('audience') ?: 'all',
        'publish_at' => $this->request->getPost('publish_at'),
        'expires_at' => $this->request->getPost('expires_at'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];

    // 🔥 Tambahkan logika otomatis status berdasarkan waktu sekarang
    $now = time();
    $expiresAt = !empty($data['expires_at']) ? strtotime($data['expires_at']) : null;

    if ($expiresAt && $expiresAt < $now) {
        $data['status'] = 'inactive';
    } else {
        $data['status'] = 'active';
    }

    (new AnnouncementsModel())->update($id, $data);

    return redirect()->to(site_url('admin/announcements'))
                     ->with('success', 'Announcement berhasil diperbarui.');
}

public function delete($id)
{
    $userRole = session()->get('role');
    $userId   = session()->get('id');

    $notifModel = new \App\Models\NotificationsModel();
    $db = \Config\Database::connect();

    if ($userRole === 'admin') {
        // Admin hapus total
        $db->table('notification_user_state')->where('notification_id', $id)->delete();
        $notifModel->delete($id);
    } else {
        // Vendor / SEO cuma sembunyikan
        $db->table('notification_user_state')
           ->where('notification_id', $id)
           ->where('user_id', $userId)
           ->update([
               'hidden'     => 1,
               'hidden_at'  => date('Y-m-d H:i:s')
           ]);
    }

    return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
}

    public function checkExpire()
    {
        $now = date('Y-m-d H:i:s');
        $model = new AnnouncementsModel();
        $model->where('expires_at IS NOT NULL', null, false)
            ->where('expires_at <', $now)
            ->where('status', 'active')
            ->set(['status' => 'inactive'])
            ->update();
    }

    public function autoExpireAnnouncements()
    {
        $db = \Config\Database::connect();
        $db->table('announcements')
        ->where('expires_at <', date('Y-m-d H:i:s'))
        ->where('status', 'active')
        ->update(['status' => 'inactive']);
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

}
