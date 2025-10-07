<?php

namespace App\Controllers\Admin;

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

        return view('admin/targets/index', [
            'title'      => 'SEO Targets - Admin',
            'vendorId'   => $vendorId,
            'priority'   => $priority,
            'status'     => $status,
            'vendors'    => $vendors,
            'targets'    => $targets
        ]);
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
}