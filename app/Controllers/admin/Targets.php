<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Targets extends BaseAdminController
{
    protected $model;
    protected $vendorModel;
    protected $activityLogsModel;

    public function __construct()
    {
        // Hapus parent::__construct() karena BaseController tidak memiliki constructor
        $this->model = new SeoKeywordTargetsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        // Log activity akses halaman targets
        $this->logActivity(
            'view_targets',
            'Mengakses halaman SEO targets'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();
        
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $priority = $this->request->getGet('priority');
        $status = $this->request->getGet('status');

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel->findAll();

        $builder = $this->model
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left');

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
            // PERBAIKAN: Cari vendor dari array vendors
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

        $insertData = [
            'vendor_id'        => $vendorId,
            'project_name'     => $data['project_name'],
            'keyword'          => $data['keyword'],
            'current_position' => $data['current_position'] !== '' ? $data['current_position'] : null,
            'target_position'  => $data['target_position'] !== '' ? $data['target_position'] : null,
            'deadline'         => $data['deadline'] ?: null,
            'priority'         => $data['priority'] ?? 'low',
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

        $updateData = [
            'vendor_id'        => $vendorId,
            'project_name'     => $data['project_name'],
            'keyword'          => $data['keyword'],
            'current_position' => $data['current_position'] !== '' ? $data['current_position'] : null,
            'target_position'  => $data['target_position'] !== '' ? $data['target_position'] : null,
            'deadline'         => $data['deadline'] ?: null,
            'priority'         => $data['priority'] ?? 'low',
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

        try {
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
    private function logActivity($action, $description, $additionalData = [])
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