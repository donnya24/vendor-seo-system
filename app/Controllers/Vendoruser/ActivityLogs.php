<?php
// app/Controllers/Vendoruser/ActivityLogs.php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;

class ActivityLogs extends BaseController
{
    /**
     * Tampilkan semua activity logs untuk vendor saat ini
     */
    public function index()
    {
        $userId = service('auth')->user()->id;

        $logs = (new ActivityLogsModel())
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return view('Vendoruser/activity_logs/index', [
            'page' => 'Activity',
            'logs' => $logs,
        ]);
    }
}
