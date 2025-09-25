<?php
namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;

class ActivityLogs extends BaseController
{
    public function index()
    {
        // Pastikan user yang login adalah admin
        $user = service('auth')->user();
        if (!$user || !in_array($user->username, ['admin', 'Administrator Utama'])) {
            return redirect()->to(site_url('login'))
                ->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        $userId = $user->id;

        $logs = (new ActivityLogsModel())
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Render view dengan data yang diperlukan
        return view('admin/activitylogs/index', [
            'title' => 'Activity Logs',
            'logs' => $logs,
            'page' => 'Activity Logs',
            'profilePhoto' => session()->get('profile_photo') ?? null,
            'notifications' => session()->get('notifications') ?? []
        ]);
    }
}