<?php
// application/helpers/activity_helper.php

use App\Models\ActivityLogsModel;
use App\Models\AdminProfileModel;
use App\Models\SeoProfilesModel;
use App\Models\VendorProfilesModel;

if (!function_exists('log_activity_auto')) {
    /**
     * Mencatat aktivitas user (admin/seo/vendor) secara otomatis
     *
     * @param string      $action       Jenis aksi (create, update, delete, view, dll)
     * @param string|null $description  Deskripsi tambahan aktivitas
     * @param array       $extraData    Data tambahan opsional (mis. module, vendor_id, dll)
     */
    function log_activity_auto(string $action, string $description = null, array $extraData = []): bool
    {
        try {
            $auth = service('auth');
            $user = $auth->user();
            if (!$user) return false;

            $logs = new ActivityLogsModel();

            // =========================
            // 🔍 Deteksi jenis user
            // =========================
            $userId   = $user->id;
            $adminId  = null;
            $seoId    = null;
            $vendorId = null;

            // Periksa di tabel admin_profiles
            $adminProfile = (new AdminProfileModel())
                ->where('user_id', $userId)
                ->first();
            if ($adminProfile) {
                $adminId = $adminProfile['id'];
            }

            // Periksa di tabel seo_profiles
            $seoProfile = (new SeoProfilesModel())
                ->where('user_id', $userId)
                ->first();
            if ($seoProfile) {
                $seoId = $seoProfile['id'];
            }

            // Periksa di tabel vendor_profiles
            $vendorProfile = (new VendorProfilesModel())
                ->where('user_id', $userId)
                ->first();
            if ($vendorProfile) {
                $vendorId = $vendorProfile['id'];
            }

            // =========================
            // 🧩 Tentukan module
            // =========================
            $module = $extraData['module'] ?? detect_module_name();

            // Jika module tidak dikirim, deteksi dari nama controller
            if (!$module) {
                $router = service('router');
                $controller = $router?->controllerName() ?? '';
                $module = strtolower(basename(str_replace('\\', '/', $controller)));
            }

            // =========================
            // 🧾 Data log
            // =========================
            $data = [
                'user_id'     => $userId,
                'admin_id'    => $adminId,
                'seo_id'      => $seoId,
                'vendor_id'   => $vendorId,
                'module'      => $module,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => service('request')->getIPAddress(),
                'user_agent'  => service('request')->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            // Tambahkan data ekstra jika ada
            if (!empty($extraData)) {
                $data = array_merge($data, $extraData);
            }

            $logs->insert($data);
            return true;
        } catch (\Throwable $e) {
            log_message('error', '[ActivityLog] ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('detect_module_name')) {
    /**
     * Mendeteksi nama module secara otomatis dari namespace controller
     */
    function detect_module_name(): string
    {
        $router = service('router');
        if (!$router) return 'unknown';

        $controller = $router->controllerName();
        if (!$controller) return 'unknown';

        $parts = explode('\\', $controller);

        // Contoh hasil:
        // App\Controllers\Admin\Dashboard => module: admin_dashboard
        // App\Controllers\Vendoruser\Areas => module: vendor_areas
        $section = strtolower($parts[2] ?? 'app');
        $class   = strtolower($parts[count($parts) - 1]);

        return "{$section}_{$class}";
    }
}

if (!function_exists('log_crud_activity')) {
    /**
     * Helper khusus untuk log aktivitas CRUD
     *
     * @param string $crudAction   CRUD action (create, read, update, delete)
     * @param string $entity       Nama entity yang dioperasikan
     * @param mixed  $entityId     ID entity (opsional)
     * @param array  $extraData    Data tambahan
     */
    function log_crud_activity(string $crudAction, string $entity, $entityId = null, array $extraData = []): bool
    {
        $actions = [
            'create' => 'membuat',
            'read'   => 'melihat',
            'update' => 'memperbarui',
            'delete' => 'menghapus'
        ];

        $action = $actions[$crudAction] ?? $crudAction;
        $description = ucfirst($action) . ' data ' . $entity;
        
        if ($entityId) {
            $description .= ' (ID: ' . $entityId . ')';
        }

        return log_activity_auto($crudAction, $description, $extraData);
    }
}