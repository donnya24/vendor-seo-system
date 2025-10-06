<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;

class ActivityLogs extends BaseController
{
    private $vendorProfile;
    private $vendorId;
    private $isVerified;

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        if (!$user) {
            return false;
        }
        
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? 0;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorId;
    }

    private function withVendorData(array $data = []): array
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    public function index()
    {
        helper('activity');
        
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dahulu.');
        }

        // Log aktivitas view logs menggunakan helper
        log_activity_auto('view', 'Melihat riwayat aktivitas', [
            'module' => 'vendor_activity_logs'
        ]);

        // Konfigurasi pagination
        $perPage = 20;
        $currentPage = $this->request->getGet('page') ? (int) $this->request->getGet('page') : 1;
        $offset = ($currentPage - 1) * $perPage;

        // Query logs berdasarkan vendor_id
        $logsModel = new \App\Models\ActivityLogsModel();
        
        $totalLogs = $logsModel->where('vendor_id', $this->vendorId)->countAllResults();
        $logs = $logsModel->where('vendor_id', $this->vendorId)
                         ->orderBy('created_at', 'DESC')
                         ->findAll($perPage, $offset);

        // Process logs untuk display - HAPUS REFERENSI KE STATUS
        $processedLogs = [];
        foreach ($logs as $log) {
            $log['action_label'] = $this->getActionLabel($log['action']);
            $log['module_label'] = $this->getModuleLabel($log['module']);
            $log['badge_class'] = $this->getActionBadgeClass($log['action']);
            // HAPUS BARIS INI: $log['status_badge'] = $this->getStatusBadgeClass($log['status']);
            $processedLogs[] = $log;
        }

        // Hitung pagination
        $totalPages = ceil($totalLogs / $perPage);

        // Render via layout master
        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Riwayat Aktivitas',
            'content_view' => 'vendoruser/activity_logs/index',
            'content_data' => [
                'page' => 'Activity Logs',
                'logs' => $processedLogs,
                'currentPage' => $currentPage,
                'totalPages' => $totalPages,
                'totalLogs' => $totalLogs,
                'perPage' => $perPage,
                'startNo' => $offset + 1,
            ],
        ]));
    }

    // Helper untuk label action yang lebih user-friendly
    private function getActionLabel(string $action): string
    {
        $actions = [
            'create' => 'Tambah Data',
            'read' => 'Lihat Data',
            'view' => 'Lihat',
            'update' => 'Perbarui Data',
            'edit' => 'Edit',
            'delete' => 'Hapus Data',
            'search' => 'Pencarian',
            'login' => 'Login',
            'logout' => 'Logout',
            'register' => 'Registrasi',
            'login_failed' => 'Login Gagal',
            'register_failed' => 'Registrasi Gagal',
            'register_error' => 'Error Registrasi',
            'create_form' => 'Buka Form Tambah',
            'edit_form' => 'Buka Form Edit',
            'view_form' => 'Buka Form',
            'error' => 'Error',
            'set' => 'Set Data'
        ];

        return $actions[$action] ?? ucfirst($action);
    }

    // Helper untuk label module yang lebih user-friendly
    private function getModuleLabel(string $module): string
    {
        $modules = [
            'vendor_areas' => 'Area Layanan',
            'vendor_activity_logs' => 'Riwayat Aktivitas',
            'vendor_dashboard' => 'Dashboard',
            'vendor_profile' => 'Profil Vendor',
            'vendor_commissions' => 'Komisi',
            'vendor_leads' => 'Leads',
            'vendor_services_products' => 'Layanan & Produk',
            'vendor_notifications' => 'Notifikasi',
            'auth' => 'Autentikasi',
            'vendor_auth' => 'Autentikasi Vendor',
            'admin_auth' => 'Autentikasi Admin',
            'seo_auth' => 'Autentikasi SEO',
            'areas' => 'Area Layanan'
        ];

        return $modules[$module] ?? ucfirst(str_replace('_', ' ', $module));
    }

    // Helper untuk badge class berdasarkan action
    private function getActionBadgeClass(string $action): string
    {
        $classes = [
            'create' => 'bg-green-100 text-green-800',
            'read' => 'bg-blue-100 text-blue-800',
            'view' => 'bg-blue-100 text-blue-800',
            'update' => 'bg-yellow-100 text-yellow-800',
            'edit' => 'bg-yellow-100 text-yellow-800',
            'delete' => 'bg-red-100 text-red-800',
            'search' => 'bg-purple-100 text-purple-800',
            'login' => 'bg-green-100 text-green-800',
            'logout' => 'bg-gray-100 text-gray-800',
            'register' => 'bg-teal-100 text-teal-800',
            'login_failed' => 'bg-red-100 text-red-800',
            'register_failed' => 'bg-red-100 text-red-800',
            'register_error' => 'bg-red-100 text-red-800',
            'create_form' => 'bg-indigo-100 text-indigo-800',
            'edit_form' => 'bg-indigo-100 text-indigo-800',
            'view_form' => 'bg-indigo-100 text-indigo-800',
            'error' => 'bg-red-100 text-red-800',
            'set' => 'bg-teal-100 text-teal-800'
        ];

        return $classes[$action] ?? 'bg-gray-100 text-gray-800';
    }
}