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
        $userId = $request->getGet('id');

        // Ambil semua user SEO (untuk dropdown) - HANYA TIM SEO
        $users = $this->seoProfiles
            ->select('user_id, name')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Ambil log aktivitas HANYA untuk user SEO
        $builder = $this->activityLogs
            ->select('activity_logs.*, seo_profiles.name')
            ->join('seo_profiles', 'seo_profiles.user_id = activity_logs.user_id', 'inner')
            ->orderBy('activity_logs.created_at', 'DESC');

        // Filter jika user SEO tertentu dipilih
        if (!empty($userId)) {
            $builder->where('activity_logs.user_id', $userId);
        }

        $logs = $builder->findAll();

        $data = [
            'title'   => 'Aktivitas Tim SEO',
            'logs'    => $logs,
            'users'   => $users,
            'user_id' => $userId,
        ];

        return view('admin/activityseo/index', $data);
    }

    public function deleteAll()
    {
        // Pastikan hanya admin yang bisa hapus
        $user = service('auth')->user();
        if (!$user || !in_array($user->username, ['admin', 'Administrator Utama'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        $request = service('request');
        $userId = $request->getGet('id'); // Filter user jika ada

        $builder = $this->activityLogs
            ->join('seo_profiles', 'seo_profiles.user_id = activity_logs.user_id', 'inner');

        // Hapus berdasarkan filter user jika dipilih
        if (!empty($userId)) {
            $builder->where('activity_logs.user_id', $userId);
        }

        $logsToDelete = $builder->findAll();
        $deletedCount = count($logsToDelete);

        if ($deletedCount > 0) {
            // Hapus menggunakan where condition untuk efisiensi
            if (!empty($userId)) {
                $this->activityLogs->where('user_id', $userId)->delete();
            } else {
                // Hapus semua log yang terkait dengan user SEO
                $this->activityLogs->whereIn('user_id', function($builder) {
                    $builder->select('user_id')->from('seo_profiles');
                })->delete();
            }

            // Log aktivitas hapus semua
            log_activity_auto('delete_all', "Menghapus {$deletedCount} riwayat aktivitas SEO" . (!empty($userId) ? " untuk user ID {$userId}" : ""), [
                'module' => 'admin_activity_seo',
                'deleted_count' => $deletedCount,
                'filtered_user' => $userId ?? 'all'
            ]);

            return redirect()->back()->with('success', "Berhasil menghapus {$deletedCount} riwayat aktivitas SEO.");
        }

        return redirect()->back()->with('error', 'Tidak ada data yang bisa dihapus.');
    }
}