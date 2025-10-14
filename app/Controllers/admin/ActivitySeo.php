<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\ActivityLogsModel;
use App\Models\SeoProfilesModel;

class ActivitySeo extends BaseAdminController
{
    protected $activityLogs;
    protected $seoProfiles;

    public function __construct()
    {
        // Hapus baris parent::__construct() karena BaseController tidak memiliki constructor
        $this->activityLogs = new ActivityLogsModel();
        $this->seoProfiles  = new SeoProfilesModel();
    }

    public function index()
    {
        // Log activity akses halaman aktivitas SEO
        $this->logActivity(
            'view_activity_seo',
            'Mengakses halaman aktivitas tim SEO'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();
        
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

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/activityseo/index', array_merge($data, $commonData));
    }

    public function deleteAll()
    {
        // Pastikan hanya admin yang bisa hapus
        $user = service('auth')->user();
        if (!$user || !in_array($user->username, ['admin', 'Administrator Utama'])) {
            // Log percobaan akses tidak sah
            $this->logActivity(
                'unauthorized_access',
                'Percobaan menghapus aktivitas SEO tanpa izin',
                ['target' => 'delete_all_activity_seo']
            );
            
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
                $seoUserIds = $this->seoProfiles->findColumn('user_id');
                if (!empty($seoUserIds)) {
                    $this->activityLogs->whereIn('user_id', $seoUserIds)->delete();
                }
            }

            // Log aktivitas hapus semua
            $this->logActivity(
                'delete_all_activity_seo',
                "Menghapus {$deletedCount} riwayat aktivitas SEO" . (!empty($userId) ? " untuk user ID {$userId}" : ""),
                [
                    'module' => 'admin_activity_seo',
                    'deleted_count' => $deletedCount,
                    'filtered_user' => $userId ?? 'all'
                ]
            );

            return redirect()->back()->with('success', "Berhasil menghapus {$deletedCount} riwayat aktivitas SEO.");
        }

        return redirect()->back()->with('error', 'Tidak ada data yang bisa dihapus.');
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
                'module'      => 'admin_activity_seo',
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => (string) $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogs->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in ActivitySeo: ' . $e->getMessage());
        }
    }
}