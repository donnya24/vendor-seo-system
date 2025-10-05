<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AreasModel;
use App\Models\VendorAreasModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class VendorAreas extends BaseController
{
    protected $areasModel;
    protected $vendorAreasModel;
    protected $vendorProfilesModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->areasModel = new AreasModel();
        $this->vendorAreasModel = new VendorAreasModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    /* ===================== LOG HELPERS ===================== */

    private function writeLog(string $action, string $description, ?int $entityId = null): void
    {
        try {
            $user = service('auth')->user();
            $ua = $this->request?->getUserAgent();
            $uaString = $ua ? $ua->getAgentString() : null;

            $this->activityLogsModel->insert([
                'user_id' => $user ? (int) $user->id : null,
                'module' => 'vendor_areas',
                'action' => $action,
                'description' => $description,
                'ip_address' => $this->request?->getIPAddress(),
                'user_agent' => $uaString,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'ActivityLogs insert failed: {err}', ['err' => $e->getMessage()]);
        }
    }

    private function describeAreas(array $ids, int $limit = 8, bool $withPath = true): string
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) return '-';

        $rows = $this->areasModel->select('id,name,type,parent_id')->whereIn('id', $ids)->findAll();

        $byId = [];
        foreach ($rows as $r) {
            $byId[(int)$r['id']] = $r;
        }

        $parts = [];
        $count = 0;
        foreach ($ids as $id) {
            if (!isset($byId[$id])) continue;
            $r = $byId[$id];
            $parts[] = $withPath ? $this->buildPathById((int)$r['id']) : $r['name'];
            $count++;
            if ($count >= $limit) break;
        }

        $extra = max(0, count($ids) - $count);
        return $extra > 0 ? (implode('; ', $parts) . " (+{$extra} lainnya)") : implode('; ', $parts);
    }

    /* ===================== PATH/LABEL HELPERS ===================== */

    private function labelByType(string $type, string $name): string
    {
        switch ($type) {
            case 'province': return 'Provinsi ' . $name;
            case 'city': return 'Kota ' . $name;
            case 'regency': return 'Kabupaten ' . $name;
            case 'district': return 'Kecamatan ' . $name;
            case 'village': return 'Kel/Desa ' . $name;
            case 'region': return $name;
            default: return $name;
        }
    }

    private function buildPathById(int $leafId): string
    {
        static $cache = [];
        $segments = [];

        if (!isset($cache[$leafId])) {
            $cache[$leafId] = $this->areasModel->select('id,name,type,parent_id')->find($leafId);
        }
        $row = $cache[$leafId] ?? null;
        if (!$row) return '';

        $segments[] = $this->labelByType($row['type'], $row['name']);

        $pid = $row['parent_id'] ?? null;
        $hops = 0;
        while ($pid && $hops < 10) {
            if (!isset($cache[$pid])) {
                $cache[$pid] = $this->areasModel->select('id,name,type,parent_id')->find($pid);
            }
            $p = $cache[$pid] ?? null;
            if (!$p) break;

            if (strcasecmp($p['name'], 'Seluruh Indonesia') === 0) {
                $segments[] = $p['name'];
                break;
            }
            if ($p['type'] !== 'region') {
                $segments[] = $this->labelByType($p['type'], $p['name']);
            }

            $pid = $p['parent_id'] ?? null;
            $hops++;
        }

        return implode(', ', $segments);
    }

    /* ===================== PAGES ===================== */

    public function index()
    {
        // Get all vendors with their areas
        $vendors = $this->vendorProfilesModel
            ->select('id, business_name, status, phone')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        $vendorAreas = [];
        foreach ($vendors as $vendor) {
            $areas = $this->vendorAreasModel
                ->select('areas.id, areas.name, areas.type')
                ->join('areas', 'areas.id = vendor_areas.area_id', 'left')
                ->where('vendor_areas.vendor_id', $vendor['id'])
                ->orderBy('areas.name', 'ASC')
                ->findAll();

            // Filter out null areas and check for "Seluruh Indonesia"
            $areas = array_values(array_filter($areas, fn($r) => !empty($r['id'])));
            
            $hasAll = false;
            foreach ($areas as $area) {
                if (strcasecmp($area['name'], 'Seluruh Indonesia') === 0) { 
                    $hasAll = true; 
                    break; 
                }
            }
            
            // If has "Seluruh Indonesia", show only that
            if ($hasAll) {
                $areas = array_values(array_filter($areas, fn($r) =>
                    strcasecmp($r['name'], 'Seluruh Indonesia') === 0
                ));
            }

            // Build paths for display
            foreach ($areas as &$area) {
                $area['path'] = $this->buildPathById((int)$area['id']);
            }
            unset($area);

            $vendorAreas[] = [
                'vendor' => $vendor,
                'areas' => $areas,
                'hasAll' => $hasAll
            ];
        }

        $data = [
            'title' => 'Area Layanan Vendor',
            'vendorAreas' => $vendorAreas
        ];

        return view('admin/vendor_areas/index', $data);
    }

    public function manage($vendorId = null)
    {
        if ($vendorId) {
            // Edit mode - dengan vendor_id
            return $this->edit($vendorId);
        } else {
            // Create mode - tanpa vendor_id
            return $this->create();
        }
    }

    public function create()
    {
        // Get vendors that don't have any areas yet
        $vendorsWithAreas = $this->vendorAreasModel
            ->select('vendor_id')
            ->distinct()
            ->findAll();
        
        $vendorIdsWithAreas = array_column($vendorsWithAreas, 'vendor_id');
        
        $vendors = $this->vendorProfilesModel
            ->select('id, business_name, status, phone')
            ->whereNotIn('id', $vendorIdsWithAreas) // Only vendors without areas
            ->orderBy('business_name', 'ASC')
            ->findAll();

        $isModal = $this->request->isAJAX() || $this->request->getGet('modal') === '1';

        return view('admin/vendor_areas/create', [
            'title' => 'Tambah Area Layanan Vendor',
            'vendors' => $vendors,
            'isModal' => $isModal,
        ]);
    }

    public function edit($vendorId)
    {
        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            return redirect()->to(site_url('admin/areas'))
                ->with('error', 'Vendor tidak ditemukan.');
        }

        // Ambil data vendor dan area dari URL jika ada
        $vendorFromUrl = $this->request->getGet('vendor');
        $areasFromUrl = $this->request->getGet('areas');
        
        $selectedAreas = [];
        
        // Jika ada data area dari URL, gunakan itu
        if (!empty($areasFromUrl)) {
            $areasData = json_decode(urldecode($areasFromUrl), true);
            if (is_array($areasData)) {
                foreach ($areasData as $area) {
                    $selectedAreas[] = [
                        'id' => (int)$area['id'],
                        'name' => $area['name'],
                        'type' => $area['type'],
                        'path' => $area['path'] ?? $area['name']
                    ];
                }
            }
        }
        
        // Jika tidak ada data dari URL atau data kosong, ambil dari database
        if (empty($selectedAreas)) {
            $vendorAreas = $this->vendorAreasModel
                ->select('areas.id, areas.name, areas.type, areas.parent_id')
                ->join('areas', 'areas.id = vendor_areas.area_id', 'inner')
                ->where('vendor_areas.vendor_id', $vendorId)
                ->orderBy('areas.name', 'ASC')
                ->findAll();

            foreach ($vendorAreas as $area) {
                $areaPath = $this->buildPathById((int)$area['id']);
                $selectedAreas[] = [
                    'id' => (int)$area['id'],
                    'name' => $area['name'],
                    'type' => $area['type'],
                    'path' => $areaPath
                ];
            }
        }

        // Check for "Seluruh Indonesia"
        $isAll = false;
        foreach ($selectedAreas as $area) {
            if (strcasecmp($area['name'], 'Seluruh Indonesia') === 0) { 
                $isAll = true; 
                break; 
            }
        }

        $isModal = $this->request->isAJAX() || $this->request->getGet('modal') === '1';

        return view('admin/vendor_areas/edit', [
            'title'          => 'Edit Area Layanan - ' . $vendor['business_name'],
            'vendor'         => $vendor,
            'selectedAreas'  => $selectedAreas,
            'isAllIndonesia' => $isAll,
            'isModal'        => $isModal,
        ]);
    }

    /* ===================== API ===================== */

    public function search()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $q = trim((string) ($this->request->getGet('q') ?? ''));

        if (mb_strlen($q) < 2) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        try {
            $rows = $this->areasModel->select('id, name, type, parent_id')
                ->like('name', $q, 'both')
                ->orderBy('LENGTH(name)', 'ASC')
                ->orderBy('name', 'ASC')
                ->limit(30)
                ->findAll();

            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'id' => (int) $row['id'],
                    'name' => $row['name'],
                    'type' => $row['type'],
                    'path' => $this->buildPathById((int)$row['id']),
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Search error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Search failed'
            ]);
        }
    }

    public function getSelectedAreas($vendorId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Vendor tidak ditemukan.']);
        }

        $vendorAreas = $this->vendorAreasModel
            ->select('areas.id, areas.name, areas.type, areas.parent_id')
            ->join('areas', 'areas.id = vendor_areas.area_id', 'inner')
            ->where('vendor_areas.vendor_id', $vendorId)
            ->orderBy('areas.name', 'ASC')
            ->findAll();

        $selectedAreas = [];
        foreach ($vendorAreas as $area) {
            $selectedAreas[] = [
                'id' => (int)$area['id'],
                'name' => $area['name'],
                'type' => $area['type'],
                'path' => $this->buildPathById((int)$area['id'])
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $selectedAreas
        ]);
    }

    public function attach()
    {
        $isAjax = $this->request->isAJAX();

        $vendorId = (int) $this->request->getPost('vendor_id');
        $allIndonesia = $this->request->getPost('all_indonesia') === '1';
        $areaIds = json_decode($this->request->getPost('area_ids_json') ?? '[]', true);
        $areaIds = is_array($areaIds) ? array_values(array_unique(array_map('intval', $areaIds))) : [];

        if (empty($vendorId)) {
            $msg = ['status' => 'error', 'message' => 'Pilih vendor terlebih dahulu.'];
            return $isAjax
                ? $this->response->setJSON($msg)
                : redirect()->back()->with('error', $msg['message']);
        }

        // Check if vendor exists
        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            $msg = ['status' => 'error', 'message' => 'Vendor tidak ditemukan.'];
            return $isAjax
                ? $this->response->setJSON($msg)
                : redirect()->back()->with('error', $msg['message']);
        }

        $businessName = $vendor['business_name'] ?? 'Unknown';
        $db = \Config\Database::connect();

        $db->transStart();
        try {
            // Reset existing areas
            $this->vendorAreasModel->where('vendor_id', $vendorId)->delete();

            if ($allIndonesia) {
                $now = date('Y-m-d H:i:s');

                $row = $this->areasModel->where('name', 'Seluruh Indonesia')->first();
                if ($row) {
                    $aid = (int) $row['id'];
                } else {
                    $this->areasModel->insert([
                        'name' => 'Seluruh Indonesia',
                        'type' => 'region',
                        'created_at' => $now,
                    ]);
                    $aid = (int) $this->areasModel->getInsertID();
                }

                $this->vendorAreasModel->insert([
                    'vendor_id' => $vendorId,
                    'area_id' => $aid,
                    'created_at' => $now,
                ]);

                $db->transComplete();
                $this->writeLog('set', 'Set area: Seluruh Indonesia untuk vendor ' . $businessName, $aid);
            } else {
                if (empty($areaIds)) {
                    // Clear all areas
                    $db->transComplete();
                    $this->writeLog('set', 'Kosongkan area layanan untuk vendor ' . $businessName, null);
                } else {
                    $existIds = $this->areasModel->whereIn('id', $areaIds)->select('id')->findColumn('id');

                    if (empty($existIds)) {
                        $db->transRollback();
                        $msg = ['status' => 'error', 'message' => 'Area yang dipilih tidak ditemukan.'];
                        return $isAjax
                            ? $this->response->setJSON($msg)
                            : redirect()->back()->with('error', $msg['message']);
                    }

                    $now = date('Y-m-d H:i:s');
                    $batch = [];
                    foreach ($existIds as $aid) {
                        $batch[] = [
                            'vendor_id' => $vendorId,
                            'area_id' => (int)$aid,
                            'created_at' => $now
                        ];
                    }
                    $this->vendorAreasModel->insertBatch($batch);

                    $db->transComplete();
                    $this->writeLog('set', 'Set area (' . count($existIds) . '): ' . $this->describeAreas($existIds, 8, true) . ' untuk vendor ' . $businessName, null);
                }
            }

            $msg = ['status' => 'success', 'message' => 'Area vendor berhasil disimpan.'];
            if ($isAjax) {
                return $this->response->setJSON(array_merge($msg, [
                    'redirect' => site_url('admin/areas'),
                ]));
            }
            return redirect()->to(site_url('admin/areas'))->with('success', $msg['message']);

        } catch (\Throwable $e) {
            $db->transRollback();
            $this->writeLog('error', 'Gagal menyimpan area: ' . $e->getMessage() . ' untuk vendor ' . $businessName, null);
            $msg = ['status' => 'error', 'message' => 'Gagal menyimpan area: ' . $e->getMessage()];
            if ($isAjax) {
                return $this->response->setJSON($msg);
            }
            return redirect()->back()->with('error', $msg['message']);
        }
    }

    public function delete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $vendorId = (int)$this->request->getPost('vendor_id');
        $areaId = (int)$this->request->getPost('area_id');

        if (empty($vendorId) || empty($areaId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Vendor ID dan Area ID diperlukan.']);
        }

        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Vendor tidak ditemukan.']);
        }

        $area = $this->areasModel->select('id,name,type,parent_id')->find($areaId);
        $businessName = $vendor['business_name'] ?? 'Unknown';

        $deleted = $this->vendorAreasModel
            ->where('vendor_id', $vendorId)
            ->where('area_id', $areaId)
            ->delete();

        if ($deleted) {
            $desc = 'Hapus area dari vendor ' . $businessName;
            if ($area) { 
                $desc .= ': ' . $this->buildPathById((int)$area['id']); 
            }
            $this->writeLog('delete', $desc, $areaId);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Area berhasil dihapus dari vendor.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Gagal menghapus area dari vendor.'
        ]);
    }

    public function clearAll($vendorId)
    {
        // Handle both AJAX and regular form requests
        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Vendor tidak ditemukan.']);
            }
            return redirect()->to(site_url('admin/areas'))->with('error', 'Vendor tidak ditemukan.');
        }

        $businessName = $vendor['business_name'] ?? 'Unknown';

        try {
            // Hapus semua area untuk vendor ini
            $deleted = $this->vendorAreasModel->where('vendor_id', $vendorId)->delete();

            if ($deleted) {
                $this->writeLog('clear', 'Hapus semua area untuk vendor: ' . $businessName, null);
                
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Semua area berhasil dihapus dari vendor.'
                    ]);
                }
                return redirect()->to(site_url('admin/areas'))->with('success', 'Semua area berhasil dihapus dari vendor ' . $businessName);
            } else {
                throw new \Exception('Tidak ada data yang dihapus');
            }

        } catch (\Exception $e) {
            log_message('error', 'Clear areas error: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Gagal menghapus area dari vendor: ' . $e->getMessage()
                ]);
            }
            return redirect()->to(site_url('admin/areas'))->with('error', 'Gagal menghapus area dari vendor: ' . $e->getMessage());
        }
    }
}