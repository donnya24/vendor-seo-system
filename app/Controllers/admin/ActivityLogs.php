<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\ActivityLogsModel;

class ActivityLogs extends BaseAdminController
{
    public function index()
    {
        // Load common data for header
        $commonData = $this->loadCommonData();
        
        // Pastikan user yang login adalah admin
        $user = service('auth')->user();
        if (!$user || !in_array($user->username, ['admin', 'Administrator Utama'])) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        // Ambil log aktivitas HANYA untuk user admin
        $adminUsernames = ['admin', 'Administrator Utama'];
        $logs = (new ActivityLogsModel())
            ->whereIn('user_id', function($builder) use ($adminUsernames) {
                $builder->select('id')
                       ->from('users')
                       ->whereIn('username', $adminUsernames);
            })
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Render view dengan data yang diperlukan
        return view('admin/activitylogs/index', array_merge([
            'title' => 'Activity Logs - Admin',
            'logs' => $logs,
            'page' => 'Activity Logs',
        ], $commonData));
    }

    public function deleteAll()
    {
        // Pastikan hanya admin yang bisa hapus
        $user = service('auth')->user();
        if (!$user || !in_array($user->username, ['admin', 'Administrator Utama'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        // Hapus hanya log aktivitas admin
        $adminUsernames = ['admin', 'Administrator Utama'];
        $logs = (new ActivityLogsModel())
            ->whereIn('user_id', function($builder) use ($adminUsernames) {
                $builder->select('id')
                       ->from('users')
                       ->whereIn('username', $adminUsernames);
            })
            ->findAll();

        $deletedCount = count($logs);

        if ($deletedCount > 0) {
            // Hapus semua log aktivitas admin
            (new ActivityLogsModel())
                ->whereIn('user_id', function($builder) use ($adminUsernames) {
                    $builder->select('id')
                           ->from('users')
                           ->whereIn('username', $adminUsernames);
                })
                ->delete();

            // Log aktivitas hapus semua
            log_activity_auto('delete_all', "Menghapus {$deletedCount} riwayat aktivitas admin", [
                'module' => 'admin_activity_logs',
                'deleted_count' => $deletedCount
            ]);

            return redirect()->back()->with('success', "Berhasil menghapus {$deletedCount} riwayat aktivitas admin.");
        }

        return redirect()->back()->with('error', 'Tidak ada data yang bisa dihapus.');
    }
}