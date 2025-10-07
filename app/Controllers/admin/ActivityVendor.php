<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\VendorProfilesModel;

class ActivityVendor extends BaseController
{
    protected $activityLogs;
    protected $vendorProfiles;

    public function __construct()
    {
        $this->activityLogs   = new ActivityLogsModel();
        $this->vendorProfiles = new VendorProfilesModel();
    }

    public function index()
    {
        $request = service('request');
        $vendorId = $request->getGet('vendor_id');

        // Ambil daftar semua vendor untuk dropdown
        $vendors = $this->vendorProfiles
            ->select('id, business_name')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        // Query log aktivitas - HANYA yang memiliki vendor_id (aktivitas vendor)
        $builder = $this->activityLogs
            ->select('activity_logs.*, vendor_profiles.business_name')
            ->join('vendor_profiles', 'vendor_profiles.id = activity_logs.vendor_id', 'inner') // INNER JOIN untuk pastikan hanya vendor
            ->orderBy('activity_logs.created_at', 'DESC');

        if (!empty($vendorId)) {
            $builder->where('activity_logs.vendor_id', $vendorId);
        }

        $logs = $builder->findAll();

        $data = [
            'title'     => 'Aktivitas Vendor',
            'logs'      => $logs,
            'vendors'   => $vendors,
            'vendor_id' => $vendorId,
        ];

        return view('admin/activityvendor/index', $data);
    }

    public function deleteAll()
    {
        // Pastikan hanya admin yang bisa hapus
        $user = service('auth')->user();
        if (!$user || !in_array($user->username, ['admin', 'Administrator Utama'])) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        $request = service('request');
        $vendorId = $request->getGet('vendor_id'); // Filter vendor jika ada

        $builder = $this->activityLogs
            ->join('vendor_profiles', 'vendor_profiles.id = activity_logs.vendor_id', 'inner');

        // Hapus berdasarkan filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('activity_logs.vendor_id', $vendorId);
        }

        $logsToDelete = $builder->findAll();
        $deletedCount = count($logsToDelete);

        if ($deletedCount > 0) {
            // Hapus menggunakan where condition untuk efisiensi
            if (!empty($vendorId)) {
                $this->activityLogs->where('vendor_id', $vendorId)->delete();
            } else {
                // Hapus semua log yang terkait dengan vendor
                $this->activityLogs->where('vendor_id IS NOT NULL')->delete();
            }

            // Log aktivitas hapus semua
            log_activity_auto('delete_all', "Menghapus {$deletedCount} riwayat aktivitas vendor" . (!empty($vendorId) ? " untuk vendor ID {$vendorId}" : ""), [
                'module' => 'admin_activity_vendor',
                'deleted_count' => $deletedCount,
                'filtered_vendor' => $vendorId ?? 'all'
            ]);

            return redirect()->back()->with('success', "Berhasil menghapus {$deletedCount} riwayat aktivitas vendor.");
        }

        return redirect()->back()->with('error', 'Tidak ada data yang bisa dihapus.');
    }
}