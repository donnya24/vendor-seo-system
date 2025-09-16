<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;

class Logs extends BaseController
{
    public function index()
    {
        // Ambil vendorId dari GET, session, atau fallback ke null
        $vendorId = $this->request->getGet('vendor_id') ?? session('vendor_id');

        $model = new ActivityLogsModel();

        $query = $model->orderBy('created_at', 'DESC');

        // Filter vendor jika ada
        if (!empty($vendorId)) {
            $query->where('vendor_id', (int) $vendorId);
        }

        // Ambil semua log
        $logs = $query->findAll();

        return view('seo/activity_logs/index', [
            'title'      => 'Log Activity',
            'activeMenu' => 'logs',
            'logs'       => $logs,
            'vendorId'   => $vendorId,
        ]);
    }
}
