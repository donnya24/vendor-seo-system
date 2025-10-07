<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\VendorProfilesModel;

class Targets extends BaseController
{
    protected $model;
    protected $vendorModel;

    public function __construct()
    {
        $this->model = new SeoKeywordTargetsModel();
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
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
            ->withLatestReport();

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

        $rows = $builder->orderBy('priority', 'DESC')
            ->orderBy('status', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Log aktivitas view targets
        $logDescription = "Melihat daftar SEO targets";
        $extraData = ['module' => 'targets'];
        
        if (!empty($vendorId)) {
            $vendorName = $this->getVendorName($vendorId, $vendors);
            $logDescription .= " untuk vendor {$vendorName}";
            $extraData['vendor_id'] = $vendorId;
        } else {
            $logDescription .= " semua vendor";
        }
        
        if (!empty($priority)) {
            $logDescription .= " dengan priority {$priority}";
            $extraData['priority'] = $priority;
        }
        
        if (!empty($status)) {
            $logDescription .= " dengan status {$status}";
            $extraData['status'] = $status;
        }

        log_activity_auto('view', $logDescription, $extraData);

        return view('seo/targets/index', [
            'title'      => 'SEO Targets',
            'vendorId'   => $vendorId,
            'priority'   => $priority,
            'status'     => $status,
            'vendors'    => $vendors,
            'targets'    => $rows,
            'activeMenu' => 'targets'
        ]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Use getPost() because client sends FormData (multipart/form-data)
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
            'priority'         => $data['priority'] ?? 'Low',
            'status'           => $data['status'] ?? 'pending',
            'notes'            => $data['notes'] ?? null,
            'created_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            $id = $this->model->insert($insertData, true);
        } catch (\Exception $e) {
            // Return JSON on DB error
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        if ($id) {
            log_activity_auto('create', "Menambahkan target SEO: '{$insertData['keyword']}' untuk vendor {$vendorName}", [
                'module' => 'targets',
                'vendor_id' => $vendorId,
                'target_id' => $id
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

        // Get existing target for logging
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
            'priority'         => $data['priority'] ?? 'Low',
            'status'           => $data['status'] ?? 'pending',
            'notes'            => $data['notes'] ?? null,
            'updated_at'       => date('Y-m-d H:i:s'),
        ];

        try {
            $updated = $this->model->update($id, $updateData);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        if ($updated) {
            log_activity_auto('update', "Memperbarui target SEO: '{$updateData['keyword']}' untuk vendor {$vendorName}", [
                'module' => 'targets',
                'vendor_id' => $vendorId,
                'target_id' => $id
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

        // Log view detail
        log_activity_auto('view', "Melihat detail target SEO: '{$target['keyword']}'", [
            'module' => 'targets',
            'vendor_id' => $target['vendor_id'],
            'target_id' => $id
        ]);

        return $this->response->setJSON(['success' => true, 'data' => $target]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Get target data before deletion for logging
        $target = $this->model->find($id);
        if (!$target) {
            return $this->response->setJSON(['success' => false, 'message' => 'Target tidak ditemukan']);
        }

        $vendorName = $this->getVendorName($target['vendor_id'], $this->vendorModel->findAll());

        try {
            $deleted = $this->model->delete($id);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        if ($deleted) {
            log_activity_auto('delete', "Menghapus target SEO: '{$target['keyword']}' untuk vendor {$vendorName}", [
                'module' => 'targets',
                'vendor_id' => $target['vendor_id'],
                'target_id' => $id
            ]);
        }

        return $this->response->setJSON([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Target berhasil dihapus.' : 'Gagal menghapus target.'
        ]);
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
}