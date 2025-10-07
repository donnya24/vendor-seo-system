<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\SeoProfilesModel;

class Logs extends BaseController
{
    public function index()
    {
        // Ambil vendorId dari GET, session, atau fallback ke null
        $vendorId = $this->request->getGet('vendor_id') ?? session('vendor_id');

        $model = new ActivityLogsModel();
        $seoProfileModel = new SeoProfilesModel();

        // Gunakan method dari model yang sudah ada
        $query = $model->select('
            activity_logs.*,
            seo_profiles.name AS seo_name
        ')
        ->join('seo_profiles', 'seo_profiles.id = activity_logs.seo_id', 'left')
        ->where('activity_logs.seo_id IS NOT NULL') // Filter hanya yang ada seo_id
        ->orWhere('activity_logs.seo_id !=', 0) // Atau seo_id tidak 0
        ->orderBy('activity_logs.created_at', 'DESC');

        // Filter vendor jika ada (jika SEO bekerja untuk vendor tertentu)
        if (!empty($vendorId)) {
            $query->where('activity_logs.vendor_id', (int) $vendorId);
        }

        // Debug: Cek query yang dihasilkan
        // dd($query->getCompiledSelect());

        // Ambil semua log
        $logs = $query->findAll();

        // Jika masih kosong, coba cara alternatif
        if (empty($logs)) {
            $logs = $model->getAllWithRelations();
            // Filter manual hanya yang memiliki seo_id
            $logs = array_filter($logs, function($log) {
                return !empty($log['seo_id']);
            });
        }

        return view('seo/activity_logs/index', [
            'title'      => 'Log Activity SEO',
            'activeMenu' => 'logs',
            'logs'       => $logs,
            'vendorId'   => $vendorId,
        ]);
    }
}