<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoKeywordTargetsModel;
use App\Models\ActivityLogsModel;

class Targets extends BaseController
{
    protected $model;

    public function __construct()
    {
        $this->model = new SeoKeywordTargetsModel();
    }

    public function index()
    {
        $vendorId = $this->request->getGet('vendor_id')
            ?? session()->get('vendor_id')
            ?? 1;

        $rows = $this->model
            ->select('seo_keyword_targets.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = seo_keyword_targets.vendor_id', 'left')
            ->withLatestReport()
            ->where('seo_keyword_targets.vendor_id', $vendorId)
            ->orderBy('priority', 'DESC')
            ->orderBy('status', 'ASC')
            ->findAll();

        $this->logActivity($vendorId, 'targets', 'view', 'Melihat daftar SEO targets');

        return view('seo/targets/index', [
            'title'      => 'SEO Targets',
            'vendorId'   => $vendorId,
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

        $insertData = [
            'vendor_id'        => $data['vendor_id'] ?? session()->get('vendor_id') ?? 1,
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
            $this->logActivity($insertData['vendor_id'], 'targets', 'create', 'Menambahkan target: '.$insertData['keyword']);
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

        $updateData = [
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
            $this->logActivity($data['vendor_id'] ?? session()->get('vendor_id'), 'targets', 'update', 'Update target: '.$data['keyword']);
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

        try {
            $deleted = $this->model->delete($id);
        } catch (\Exception $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }

        if ($deleted) {
            $this->logActivity(session()->get('vendor_id'), 'targets', 'delete', 'Menghapus target ID: '.$id);
        }

        return $this->response->setJSON([
            'success' => (bool)$deleted,
            'message' => $deleted ? 'Target berhasil dihapus.' : 'Gagal menghapus target.'
        ]);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        (new ActivityLogsModel())->insert([
            'user_id'    => session()->get('user_id'),
            'vendor_id'  => $vendorId ?? session()->get('vendor_id') ?? 1,
            'module'     => $module,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
