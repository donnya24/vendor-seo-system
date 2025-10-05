<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\SeoProfilesModel;

class ActivitySeo extends BaseController
{
    protected $activityLogs;
    protected $seoProfiles;

    public function __construct()
    {
        $this->activityLogs = new ActivityLogsModel();
        $this->seoProfiles  = new SeoProfilesModel();
    }

    public function index()
    {
        $request = service('request');
        $userId = $request->getGet('id'); // ✅ ambil dari form (name="id")

        // Ambil semua user SEO (untuk dropdown)
        $users = $this->seoProfiles
            ->select('user_id, name')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Ambil log aktivitas SEO dan join ke tabel seo_profiles via user_id
        $builder = $this->activityLogs
            ->select('activity_logs.*, seo_profiles.name')
            ->join('seo_profiles', 'seo_profiles.user_id = activity_logs.user_id', 'left')
            ->orderBy('activity_logs.created_at', 'DESC');

        // Filter jika user dipilih
        if (!empty($userId)) {
            $builder->where('activity_logs.user_id', $userId);
        }

        $logs = $builder->findAll();

        $data = [
            'title'   => 'Aktivitas SEO',
            'logs'    => $logs,
            'users'   => $users,
            'user_id' => $userId,
        ];

        // Hanya render satu view utama (karena header/footer sudah di-include di dalamnya)
        return view('admin/activityseo/index', $data);
    }
}
