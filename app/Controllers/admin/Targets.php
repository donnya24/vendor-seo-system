<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use App\Models\SeoProfilesModel;

class Targets extends BaseAdminController
{
    protected $model;
    protected $vendorModel;
    protected $activityLogsModel;
    protected $notificationsModel;
    protected $userModel;
    protected $seoProfilesModel;

    public function __construct()
    {
        $this->model = new SeoKeywordTargetsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
        $this->seoProfilesModel = new SeoProfilesModel();
    }

    public function index()
    {
        // Log activity akses halaman targets
        $this->logActivity(
            'view_targets',
            'Mengakses halaman SEO targets'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = method_exists($this, 'loadCommonData') ? $this->loadCommonData() : [];
        
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $priority = $this->request->getGet('priority');
        $status = $this->request->getGet('status');

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        $builder = $this->model
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left')
            ->withLatestReport(); // PERBAIKAN: Tambahkan withLatestReport()

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('seo_keyword_targets.vendor_id', $vendorId);
        }

        // Filter priority jika dipilih
        if (!empty($priority) && in_array($priority, ['low', 'medium', 'high'])) {
            $builder->where('seo_keyword_targets.priority', $priority);
        }

        // Filter status jika dipilih
        if (!empty($status) && in_array($status, ['pending', 'in_progress', 'completed'])) {
            $builder->where('seo_keyword_targets.status', $status);
        }

        $targets = $builder->orderBy('priority', 'DESC')
            ->orderBy('status', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Log activity dengan filter
        $filterData = [];
        if (!empty($vendorId)) {
            $vendorName = 'Unknown Vendor';
            foreach ($vendors as $vendor) {
                if ($vendor['id'] == $vendorId) {
                    $vendorName = $vendor['business_name'];
                    break;
                }
            }
            
            $filterData['vendor_id'] = $vendorId;
            $filterData['vendor_name'] = $vendorName;
        }
        if (!empty($priority)) {
            $filterData['priority'] = $priority;
        }
        if (!empty($status)) {
            $filterData['status'] = $status;
        }

        if (!empty($filterData)) {
            $this->logActivity(
                'view_targets_with_filter',
                'Mengakses halaman SEO targets dengan filter',
                $filterData
            );
        }

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/targets/index', array_merge([
            'title'      => 'SEO Targets - Admin',
            'vendorId'   => $vendorId,
            'priority'   => $priority,
            'status'     => $status,
            'vendors'    => $vendors,
            'targets'    => $targets
        ], $commonData));
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getPost();

        // Basic validation
        if (empty($data['project_name']) || empty($data['keyword'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Project & Keyword wajib diisi']);
        }

        $vendorId = $data['vendor_id'] ?? session()->get('vendor_id') ?? 1;
        $vendorName = $this->getVendorName($vendorId, $this->vendorModel->findAll());

        $insertData = [
            'vendor_id'        => $vendorId,
            'project_name'     => $data['project_name'],
            'keyword'          => $data['keyword'],
            'current_position' => $data['current_position'] !== '' ? $data['current_position'] : null,
            'target_position'  => $data['target_position'] !== '' ? $data['target_position'] : null,
            'deadline'         => $data['deadline'] ?: null,
            'priority'         => $data['priority'] ?? 'low', // PERBAIKAN: Default 'low' bukan 'Low'
            'status'           => $data['status'] ?? 'pending',
            'notes'            => $data['notes'] ?? null,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            $id = $this->model->insert($insertData, true);
            
            // Log activity create target
            $this->logActivity(
                'create_target',
                'Membuat SEO target baru: ' . $data['keyword'],
                [
                    'target_id' => $id,
                    'vendor_id' => $vendorId,
                    'project_name' => $data['project_name'],
                    'keyword' => $data['keyword'],
                    'priority' => $data['priority'] ?? 'low',
                    'status' => $data['status'] ?? 'pending'
                ]
            );

            // BUAT NOTIFIKASI UNTUK VENDOR DAN TIM SEO
            $this->createTargetNotification($id, $vendorId, 'create', $insertData);
            
            // PERBAIKAN: If status is completed, create a report
            if ($insertData['status'] === 'completed') {
                $target = $this->model->find($id);
                if ($target) {
                    $this->model->createReportFromTarget($id);
                }
            }

        } catch (\Exception $e) {
            // Log activity error
            $this->logActivity(
                'create_target_error',
                'Gagal membuat SEO target: ' . $e->getMessage(),
                [
                    'vendor_id' => $vendorId,
                    'project_name' => $data['project_name'],
                    'keyword' => $data['keyword'],
                    'error' => $e->getMessage()
                ]
            );
            
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => (bool)$id,
            'message' => $id ? 'Target berhasil disimpan.' : 'Gagal menyimpan target.'
        ]);
    }

    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $data = $this->request->getPost();

        if (empty($data['project_name']) || empty($data['keyword'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Project & Keyword wajib diisi']);
        }

        // Get existing target
        $existingTarget = $this->model->find($id);
        if (!$existingTarget) {
            return $this->response->setJSON(['success' => false, 'message' => 'Target tidak ditemukan']);
        }

        $vendorId = $data['vendor_id'] ?? $existingTarget['vendor_id'];
        $vendorName = $this->getVendorName($vendorId, $this->vendorModel->findAll());

        $updateData = [
            'vendor_id'        => $vendorId,
            'project_name'     => $data['project_name'],
            'keyword'          => $data['keyword'],
            'current_position' => $data['current_position'] !== '' ? $data['current_position'] : null,
            'target_position'  => $data['target_position'] !== '' ? $data['target_position'] : null,
            'deadline'         => $data['deadline'] ?: null,
            'priority'         => $data['priority'] ?? 'low', // PERBAIKAN: Default 'low' bukan 'Low'
            'status'           => $data['status'] ?? 'pending',
            'notes'            => $data['notes'] ?? null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            $updated = $this->model->update($id, $updateData);
            
            // Log activity update target
            $this->logActivity(
                'update_target',
                'Memperbarui SEO target: ' . $data['keyword'],
                [
                    'target_id' => $id,
                    'vendor_id' => $vendorId,
                    'project_name' => $data['project_name'],
                    'keyword' => $data['keyword'],
                    'priority' => $data['priority'] ?? 'low',
                    'status' => $data['status'] ?? 'pending'
                ]
            );

            // BUAT NOTIFIKASI UNTUK VENDOR DAN TIM SEO
            $actionType = ($updateData['status'] === 'completed' && $existingTarget['status'] !== 'completed') 
                ? 'complete' 
                : 'update';
            
            $this->createTargetNotification($id, $vendorId, $actionType, $updateData, $existingTarget);
            
            // PERBAIKAN: Handle status changes
            if ($updateData['status'] === 'completed' && $existingTarget['status'] !== 'completed') {
                // Status changed to completed, create a report
                $updatedTarget = $this->model->find($id);
                if ($updatedTarget) {
                    $this->model->createReportFromTarget($id);
                }
            } elseif ($updateData['status'] !== 'completed' && $existingTarget['status'] === 'completed') {
                // Status changed from completed to something else, delete related reports
                $this->model->deleteRelatedReports($id);
            }

        } catch (\Exception $e) {
            // Log activity error
            $this->logActivity(
                'update_target_error',
                'Gagal memperbarui SEO target: ' . $e->getMessage(),
                [
                    'target_id' => $id,
                    'vendor_id' => $vendorId,
                    'project_name' => $data['project_name'],
                    'keyword' => $data['keyword'],
                    'error' => $e->getMessage()
                ]
            );
            
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => (bool)$updated,
            'message' => $updated ? 'Target berhasil diperbarui.' : 'Gagal mengupdate target.'
        ]);
    }

    public function edit($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $target = $this->model->find($id);
        if (!$target) {
            return $this->response->setJSON(['success' => false, 'message' => 'Data tidak ditemukan']);
        }

        // Log activity view edit form
        $this->logActivity(
            'view_edit_target',
            'Membuka form edit SEO target',
            [
                'target_id' => $id,
                'keyword' => $target['keyword']
            ]
        );

        return $this->response->setJSON(['success' => true, 'data' => $target]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $target = $this->model->find($id);
        if (!$target) {
            return $this->response->setJSON(['success' => false, 'message' => 'Target tidak ditemukan']);
        }

        $vendorName = $this->getVendorName($target['vendor_id'], $this->vendorModel->findAll());

        try {
            // PERBAIKAN: Model delete method sudah di-override untuk menghapus related reports
            $deleted = $this->model->delete($id);
            
            // Log activity delete target
            $this->logActivity(
                'delete_target',
                'Menghapus SEO target: ' . $target['keyword'],
                [
                    'target_id' => $id,
                    'vendor_id' => $target['vendor_id'],
                    'project_name' => $target['project_name'],
                    'keyword' => $target['keyword']
                ]
            );

            // BUAT NOTIFIKASI UNTUK VENDOR DAN TIM SEO
            $this->createTargetNotification($id, $target['vendor_id'], 'delete', $target);

        } catch (\Exception $e) {
            // Log activity error
            $this->logActivity(
                'delete_target_error',
                'Gagal menghapus SEO target: ' . $e->getMessage(),
                [
                    'target_id' => $id,
                    'vendor_id' => $target['vendor_id'],
                    'project_name' => $target['project_name'],
                    'keyword' => $target['keyword'],
                    'error' => $e->getMessage()
                ]
            );
            
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        return $this->response->setJSON([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Target berhasil dihapus.' : 'Gagal menghapus target.'
        ]);
    }

    /**
     * Log activity untuk admin
     */
    private function logActivity(string $action, string $description, array $additionalData = []): void
    {
        try {
            $user = service('auth')->user();
            
            $data = [
                'user_id'     => $user ? $user->id : null,
                'module'      => 'admin_targets',
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => (string) $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogsModel->insert($data);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity in Targets: ' . $e->getMessage());
        }
    }

    /**
     * Helper untuk mendapatkan nama vendor
     */
    private function getVendorName(int $vendorId, array $vendors): string
    {
        foreach ($vendors as $vendor) {
            if ($vendor['id'] == $vendorId) {
                return $vendor['business_name'];
            }
        }
        return 'Unknown Vendor';
    }

    /**
     * Buat notifikasi untuk vendor dan tim SEO terkait target SEO dengan type 'system'
     */
    private function createTargetNotification($targetId, $vendorId, $actionType, $targetData, $oldData = null)
    {
        try {
            $db = \Config\Database::connect();
            
            // Dapatkan informasi vendor
            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                return false;
            }

            // Dapatkan user_id dari vendor
            $vendorUserId = $vendor['user_id'] ?? null;
            if (!$vendorUserId) {
                return false;
            }

            // Dapatkan semua admin users
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->get()
                ->getResultArray();

            // Dapatkan admin yang sedang login
            $adminUserId = session()->get('user_id');
            $adminProfile = $db->table('admin_profiles')
                ->where('user_id', $adminUserId)
                ->get()
                ->getRowArray();
            $adminName = $adminProfile['name'] ?? 'Admin';

            // Dapatkan semua SEO users
            $seoUsers = $db->table('seo_profiles sp')
                ->select('sp.user_id')
                ->join('users u', 'u.id = sp.user_id')
                ->where('sp.status', 'active')
                ->get()
                ->getResultArray();

            // Siapkan data notifikasi berdasarkan action type
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            switch ($actionType) {
                case 'create':
                    $title = 'Target SEO Baru Dibuat';
                    $message = "Admin {$adminName} telah membuat target SEO baru: '{$targetData['keyword']}' untuk vendor {$vendor['business_name']}";
                    break;

                case 'update':
                    $title = 'Target SEO Diperbarui';
                    $message = "Admin {$adminName} telah memperbarui target SEO: '{$targetData['keyword']}' untuk vendor {$vendor['business_name']}";
                    break;

                case 'complete':
                    $title = 'Target SEO Selesai';
                    $message = "🎉 Target SEO '{$targetData['keyword']}' untuk vendor {$vendor['business_name']} telah berhasil diselesaikan oleh Admin {$adminName}";
                    break;

                case 'delete':
                    $title = 'Target SEO Dihapus';
                    $message = "Admin {$adminName} telah menghapus target SEO: '{$targetData['keyword']}' untuk vendor {$vendor['business_name']}";
                    break;

                default:
                    return false;
            }

            // Tambahkan detail untuk update
            if ($actionType === 'update' && $oldData) {
                $changes = [];
                
                if ($targetData['status'] !== $oldData['status']) {
                    $changes[] = "Status: {$oldData['status']} → {$targetData['status']}";
                }
                if ($targetData['priority'] !== $oldData['priority']) {
                    $changes[] = "Priority: {$oldData['priority']} → {$targetData['priority']}";
                }
                if ($targetData['current_position'] != $oldData['current_position']) {
                    $oldPos = $oldData['current_position'] ?? 'Belum ada';
                    $newPos = $targetData['current_position'] ?? 'Belum ada';
                    $changes[] = "Posisi saat ini: {$oldPos} → {$newPos}";
                }

                if (!empty($changes)) {
                    $message .= "\n\nPerubahan:\n• " . implode("\n• ", $changes);
                }
            }

            // Notifikasi untuk VENDOR dengan type system
            if ($vendorUserId) {
                $notifications[] = [
                    'user_id' => $vendorUserId,
                    'vendor_id' => $vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Notifikasi untuk semua ADMIN KECUALI admin yang sedang login dengan type system
            foreach ($adminUsers as $admin) {
                // Skip admin yang sedang login
                if ($admin['user_id'] == $adminUserId) {
                    continue;
                }
                
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Notifikasi untuk semua SEO users dengan type system
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => $seo['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Insert semua notifikasi
            if (!empty($notifications)) {
                $this->notificationsModel->insertBatch($notifications);
                
                // Log untuk debugging
                log_message('info', "Created admin {$actionType} notifications for target {$targetId}: " . count($notifications) . " notifications sent");
                
                return true;
            }

            return false;

        } catch (\Exception $e) {
            log_message('error', "Error creating admin target notification: " . $e->getMessage());
            return false;
        }
    }

    public function exportCsv()
    {
        // Log activity export
        $this->logActivity(
            'export_targets_csv',
            'Mengekspor data SEO targets ke CSV'
        );

        // Get filter parameters
        $vendorId = $this->request->getGet('vendor_id');
        $priority = $this->request->getGet('priority');
        $status = $this->request->getGet('status');

        // Build query
        $query = $this->model
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left');

        if (!empty($vendorId) && $vendorId !== 'all') {
            $query->where('seo_keyword_targets.vendor_id', $vendorId);
        }

        if (!empty($priority) && in_array($priority, ['low', 'medium', 'high'])) {
            $query->where('seo_keyword_targets.priority', $priority);
        }

        if (!empty($status) && in_array($status, ['pending', 'in_progress', 'completed'])) {
            $query->where('seo_keyword_targets.status', $status);
        }

        $targets = $query->orderBy('priority', 'DESC')
            ->orderBy('status', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        if (empty($targets)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        // Set headers untuk download CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="seo_targets_' . date('Y-m-d_H-i-s') . '.csv"');

        $output = fopen('php://output', 'w');

        // Add BOM untuk UTF-8
        fputs($output, "\xEF\xBB\xBF");

        // Header CSV
        $headers = [
            'No',
            'ID Target',
            'Vendor',
            'Project',
            'Keyword',
            'Posisi Saat Ini',
            'Target Posisi',
            'Deadline',
            'Priority',
            'Status',
            'Catatan',
            'Tgl Dibuat',
            'Tgl Diperbarui'
        ];
        fputcsv($output, $headers);

        // Data rows
        $no = 1;
        foreach ($targets as $target) {
            $row = [
                $no++,
                $target['id'] ?? '-',
                $target['vendor_name'] ?? '-',
                $target['project_name'] ?? '-',
                $target['keyword'] ?? '-',
                $target['current_position'] ?? '-',
                $target['target_position'] ?? '-',
                $target['deadline'] ? date('d/m/Y', strtotime($target['deadline'])) : '-',
                ucfirst($target['priority'] ?? '-'),
                ucfirst(str_replace('_', ' ', $target['status'] ?? '-')),
                $target['notes'] ?? '-',
                $target['created_at'] ? date('d/m/Y H:i', strtotime($target['created_at'])) : '-',
                $target['updated_at'] ? date('d/m/Y H:i', strtotime($target['updated_at'])) : '-'
            ];
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}