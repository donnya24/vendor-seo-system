<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;

class Notifications extends BaseController
{
    protected $db;
    protected $table = 'notifications';

    public function __construct()
    {
        $this->db = db_connect();
    }

    // ================= INDEX (LIST NOTIFIKASI) =================
    public function index()
    {
        $userId = (int) service('auth')->user()->id;

        $items = $this->db->table($this->table)
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->get()
            ->getResultArray();

        return view('vendoruser/notifications/index', [
            'page'  => 'Notifikasi',
            'items' => $items
        ]);
    }

    // ================= MARK READ (SATU NOTIF) =================
    public function markRead($id)
    {
        $userId = (int) service('auth')->user()->id;

        $this->db->table($this->table)
            ->where(['id' => $id, 'user_id' => $userId])
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ])
            ->update();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Notifikasi sudah ditandai dibaca.');
    }

    // ================= MARK ALL READ =================
    public function markAllRead()
    {
        $userId = (int) service('auth')->user()->id;

        $this->db->table($this->table)
            ->where('user_id', $userId)
            ->set([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ])
            ->update();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    // ================= DELETE ONE =================
    public function delete($id)
    {
        $userId = (int) service('auth')->user()->id;

        $this->db->table($this->table)
            ->where(['id' => $id, 'user_id' => $userId])
            ->delete();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    // ================= DELETE ALL =================
    public function deleteAll()
    {
        $userId = (int) service('auth')->user()->id;

        $this->db->table($this->table)
            ->where('user_id', $userId)
            ->delete();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('success', 'Semua notifikasi berhasil dihapus.');
    }
}
