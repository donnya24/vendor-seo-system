<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\SeoReportsModel;
use App\Models\SeoKeywordTargetsModel;
use App\Models\ActivityLogsModel;
use CodeIgniter\HTTP\Files\UploadedFile;

class Reports extends BaseController
{
    public function index()
    {
        $vendorId = $this->request->getGet('vendor_id') 
            ?? session()->get('vendor_id') 
            ?? 1;

        $reportModel = new SeoReportsModel();
        $reports = $reportModel
            ->where('vendor_id', $vendorId)
            ->orderBy('created_at', 'DESC')
            ->paginate(20);

        // Catat log saat membuka laporan
        $this->logActivity($vendorId, 'reports', 'view', 'Melihat daftar laporan SEO');

        return view('seo/reports/index', [
            'title'      => 'Laporan SEO',
            'activeMenu' => 'reports',
            'reports'    => $reports,
            'pager'      => $reportModel->pager,
            'vendorId'   => $vendorId,
        ]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['change']     = $data['change'] ?? null;
        $data['trend']      = $data['trend'] ?? 'stable';
        $data['vendor_id']  = $data['vendor_id'] ?? session()->get('vendor_id') ?? 1;

        $inserted = (new SeoReportsModel())->insert($data);

        if ($inserted) {
            (new SeoKeywordTargetsModel())
                ->where(['vendor_id' => $data['vendor_id'], 'keyword' => $data['keyword']])
                ->set(['current_position' => $data['position']])
                ->update();

            $this->logActivity($data['vendor_id'], 'reports', 'create', 'Menambahkan laporan SEO untuk keyword: '.$data['keyword']);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => (bool) $inserted,
                'message' => $inserted ? 'Report berhasil ditambahkan.' : 'Gagal menambahkan report.'
            ]);
        }

        return redirect()->back()->with('msg', 'Report ditambahkan.');
    }

    public function edit($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        $report = (new SeoReportsModel())->find($id);

        if (!$report) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Report tidak ditemukan'
            ]);
        }

        return $this->response->setJSON($report);
    }

    public function update($id)
    {
        $data = $this->request->getPost();

        $data['updated_at'] = date('Y-m-d H:i:s');

        $updated = (new SeoReportsModel())->update($id, $data);

        if ($updated) {
            $this->logActivity(
                $data['vendor_id'] ?? session()->get('vendor_id') ?? 1,
                'reports',
                'update',
                'Mengupdate laporan SEO ID: '.$id
            );
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => (bool)$updated,
                'message' => $updated ? 'Report berhasil diperbarui.' : 'Gagal mengupdate report.'
            ]);
        }

        return redirect()->back()->with('msg', $updated ? 'Report diperbarui.' : 'Gagal update report.');
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        $reportModel = new SeoReportsModel();
        $report      = $reportModel->find($id);

        if (!$report) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Report tidak ditemukan'
            ]);
        }

        $deleted = $reportModel->delete($id);

        if ($deleted) {
            $this->logActivity(
                $report['vendor_id'] ?? session()->get('vendor_id') ?? 1,
                'reports',
                'delete',
                'Menghapus laporan SEO ID: '.$id
            );
        }

        return $this->response->setJSON([
            'success' => (bool) $deleted,
            'message' => $deleted ? 'Report berhasil dihapus.' : 'Gagal menghapus report.'
        ]);
    }

    public function importCsv()
    {
        $file = $this->request->getFile('file');
        $vendorId = (int)$this->request->getPost('vendor_id');
        
        $vendorId = (int)($this->request->getPost('vendor_id') 
            ?? session()->get('vendor_id') 
            ?? 1);

        if (!$file || !$file->isValid()) {
            return redirect()->back()->with('error', 'File tidak valid');
        }

        $rows = array_map('str_getcsv', file($file->getTempName()));
        $header = array_map('trim', array_shift($rows));
        $importedCount = 0;

        foreach ($rows as $row) {
            $d = array_combine($header, $row);
            if (!$d || empty($d['keyword'])) continue;

            $payload = [
                'vendor_id'  => $vendorId,
                'keyword'    => esc($d['keyword']),
                'project'    => $d['project'] ?? null,
                'position'   => (int)($d['position'] ?? 0),
                'change'     => isset($d['change']) ? (int)$d['change'] : null,
                'trend'      => !empty($d['trend']) ? esc($d['trend']) : 'stable',
                'volume'     => isset($d['volume']) ? (int)$d['volume'] : null,
                'status'     => 'active',
                'created_at' => !empty($d['created_at']) ? esc($d['created_at']) : date('Y-m-d H:i:s'),
            ];

            $inserted = (new SeoReportsModel())->insert($payload);
            if ($inserted) {
                $importedCount++;
                (new SeoKeywordTargetsModel())
                    ->where(['vendor_id' => $vendorId, 'keyword' => $payload['keyword']])
                    ->set(['current_position' => $payload['position']])
                    ->update();
            }
        }

        if ($importedCount > 0) {
            $this->logActivity($vendorId, 'reports', 'import', "Import CSV laporan SEO sejumlah: $importedCount");
        }

        return redirect()->back()->with('msg', "Import selesai ($importedCount laporan).");
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
