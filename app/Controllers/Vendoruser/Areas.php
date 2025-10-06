<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\AreasModel;
use App\Models\VendorAreasModel;
use App\Models\VendorProfilesModel;

class Areas extends BaseController
{
    private $vendorProfile;
    private $vendorId;
    private $isVerified;

    private function initVendor(): bool
    {
        $user = service('auth')->user();
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

    /* ===================== LOG HELPERS ===================== */

    private function describeAreas(array $ids, int $limit = 8, bool $withPath = true): string
    {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (empty($ids)) return '-';

        $am   = new AreasModel();
        $rows = $am->select('id,name,type,parent_id')->whereIn('id', $ids)->findAll();

        $byId = [];
        foreach ($rows as $r) $byId[(int)$r['id']] = $r;

        $parts = [];
        $count = 0;
        foreach ($ids as $id) {
            if (!isset($byId[$id])) continue;
            $r = $byId[$id];
            $parts[] = $withPath ? $this->buildPathById($am, (int)$r['id']) : $r['name'];
            $count++;
            if ($count >= $limit) break;
        }

        $extra = max(0, count($ids) - $count);
        return $extra > 0 ? (implode('; ', $parts) . " (+{$extra} lainnya)") : implode('; ', $parts);
    }

    /* ===================== PAGES ===================== */

    public function index()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        // Log aktivitas view
        if (function_exists('log_activity_auto')) {
            log_activity_auto('view', 'Melihat daftar area layanan', [
                'module' => 'vendor_areas',
                'vendor_id' => $this->vendorId
            ]);
        }

        // LEFT JOIN + filter null (biarkan sesuai punyamu)
        $vendorAreas = (new VendorAreasModel())
            ->select('areas.id, areas.name, areas.type')
            ->join('areas', 'areas.id = vendor_areas.area_id', 'left')
            ->where('vendor_areas.vendor_id', $this->vendorId)
            ->orderBy('areas.name', 'ASC')
            ->findAll();

        $vendorAreas = array_values(array_filter($vendorAreas, fn($r) => !empty($r['id'])));

        // Jika ada "Seluruh Indonesia", tampilkan hanya itu
        $hasAll = false;
        foreach ($vendorAreas as $va) {
            if (strcasecmp($va['name'], 'Seluruh Indonesia') === 0) { $hasAll = true; break; }
        }
        if ($hasAll) {
            $vendorAreas = array_values(array_filter($vendorAreas, fn($r) =>
                strcasecmp($r['name'], 'Seluruh Indonesia') === 0
            ));
        }

        // ⬇️ HANYA BAGIAN INI YANG DIUBAH: render lewat layout master
        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Area Layanan',                 // dipakai di <title> & header
            'content_view' => 'vendoruser/areas/index',       // view konten utama
            'content_data' => [                               // data khusus untuk view konten
                'page'         => 'Area',
                'vendorAreas'  => $vendorAreas,
                'hasAll'       => $hasAll,
            ],
        ]));
    }

    public function create()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada.');
        }

        // Log aktivitas create form
        if (function_exists('log_activity_auto')) {
            log_activity_auto('create_form', 'Membuka form tambah area layanan', [
                'module' => 'vendor_areas',
                'vendor_id' => $this->vendorId
            ]);
        }

        $isModal = $this->request->isAJAX() || $this->request->getGet('modal') === '1';

        return view('vendoruser/areas/create', $this->withVendorData([
            'title'   => 'Pilih Area Layanan',
            'isModal' => $isModal,
        ]));
    }

    public function edit()
    {
        if (! $this->initVendor()) {
            return $this->response->setStatusCode(403)->setBody('Unauthorized');
        }

        // Log aktivitas edit form
        if (function_exists('log_activity_auto')) {
            log_activity_auto('edit_form', 'Membuka form edit area layanan', [
                'module' => 'vendor_areas',
                'vendor_id' => $this->vendorId
            ]);
        }

        $areaModel = new AreasModel();

        $vendorAreas = (new VendorAreasModel())
            ->select('areas.id, areas.name, areas.type')
            ->join('areas', 'areas.id = vendor_areas.area_id', 'inner')
            ->where('vendor_areas.vendor_id', $this->vendorId)
            ->orderBy('areas.name', 'ASC')
            ->findAll();

        foreach ($vendorAreas as &$va) {
            $va['path'] = $this->buildPathById($areaModel, (int) $va['id']);
        }
        unset($va);

        $isAll = false;
        foreach ($vendorAreas as $va) {
            if (strcasecmp($va['name'], 'Seluruh Indonesia') === 0) { $isAll = true; break; }
        }

        $isModal = $this->request->isAJAX() || $this->request->getGet('modal') === '1';

        return view('vendoruser/areas/edit', $this->withVendorData([
            'selectedAreas'  => $vendorAreas,
            'isAllIndonesia' => $isAll,
            'title'          => 'Edit Area Layanan',
            'isModal'        => $isModal,
        ]));
    }

    /* ===================== PATH/LABEL HELPERS ===================== */

    private function labelByType(string $type, string $name): string
    {
        switch ($type) {
            case 'province': return 'Provinsi ' . $name;
            case 'city':     return 'Kota ' . $name;
            case 'regency':  return 'Kabupaten ' . $name;
            case 'district': return 'Kecamatan ' . $name;
            case 'village':  return 'Kel/Desa ' . $name;
            case 'region':   return $name;
            default:         return $name;
        }
    }

    private function buildPathById(AreasModel $model, int $leafId): string
    {
        static $cache = [];
        $segments = [];

        if (!isset($cache[$leafId])) {
            $cache[$leafId] = $model->select('id,name,type,parent_id')->find($leafId);
        }
        $row = $cache[$leafId] ?? null;
        if (!$row) return '';

        $segments[] = $this->labelByType($row['type'], $row['name']);

        $pid  = $row['parent_id'] ?? null;
        $hops = 0;
        while ($pid && $hops < 10) {
            if (!isset($cache[$pid])) {
                $cache[$pid] = $model->select('id,name,type,parent_id')->find($pid);
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

    /* ===================== API ===================== */

   public function search()
    {
        // Inisialisasi vendor untuk mendapatkan vendor_id
        if (! $this->initVendor()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $q = trim((string) ($this->request->getGet('q') ?? ''));

        // Log aktivitas search - SEKARANG SUDAH PASTI ADA VENDOR_ID
        if (function_exists('log_activity_auto')) {
            log_activity_auto('search', 'Mencari area: ' . $q, [
                'module' => 'vendor_areas',
                'vendor_id' => $this->vendorId
            ]);
        }

        if (mb_strlen($q) < 2) {
            return $this->response->setJSON(['status' => 'success', 'data' => []]);
        }

        $model = new AreasModel();

        $rows = $model->select('id, name, type, parent_id')
                      ->like('name', $q, 'both')
                      ->orderBy('LENGTH(name)', 'ASC')
                      ->orderBy('name', 'ASC')
                      ->limit(30)
                      ->findAll();

        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'id'   => (int) $row['id'],
                'name' => $row['name'],
                'type' => $row['type'],
                'path' => $this->buildPathById($model, (int) $row['id']),
            ];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data'   => $data,
        ]);
    }

    public function attach()
    {
        $isAjax = $this->request->isAJAX();

        if (! $this->initVendor()) {
            $msg = ['status'=>'error','message'=>'Profil vendor belum ada.'];
            return $isAjax
                ? $this->response->setJSON($msg)
                : redirect()->back()->with('error', $msg['message']);
        }

        $allIndonesia = $this->request->getPost('all_indonesia') === '1';
        $areaIds = json_decode($this->request->getPost('area_ids_json') ?? '[]', true);
        $areaIds = is_array($areaIds) ? array_values(array_unique(array_map('intval', $areaIds))) : [];

        $areaModel       = new AreasModel();
        $vendorAreaModel = new VendorAreasModel();
        $db = \Config\Database::connect();

        $db->transStart();
        try {
            // Reset lama
            $vendorAreaModel->where('vendor_id', $this->vendorId)->delete();

            if ($allIndonesia) {
                $now = date('Y-m-d H:i:s');

                $row = $areaModel->where('name', 'Seluruh Indonesia')->first();
                if ($row) {
                    $aid = (int) $row['id'];
                } else {
                    $areaModel->insert([
                        'name'       => 'Seluruh Indonesia',
                        'type'       => 'region',
                        'created_at' => $now,
                    ]);
                    $aid = (int) $areaModel->getInsertID();
                }

                $vendorAreaModel->insert([
                    'vendor_id'  => $this->vendorId,
                    'area_id'    => $aid,
                    'created_at' => $now,
                ]);

                $db->transComplete();
                
                // PERBAIKAN: gunakan helper untuk log
                if (function_exists('log_activity_auto')) {
                    log_activity_auto('set', 'Set area: Seluruh Indonesia', [
                        'module' => 'vendor_areas',
                        'vendor_id' => $this->vendorId,
                        'area_id' => $aid
                    ]);
                }
            } else {
                if (empty($areaIds)) {
                    // clear semua area
                    $db->transComplete();
                    
                    // PERBAIKAN: gunakan helper untuk log
                    if (function_exists('log_activity_auto')) {
                        log_activity_auto('set', 'Kosongkan area layanan', [
                            'module' => 'vendor_areas',
                            'vendor_id' => $this->vendorId
                        ]);
                    }
                } else {
                    $existIds = $areaModel->whereIn('id', $areaIds)->select('id')->findColumn('id');

                    if (empty($existIds)) {
                        $db->transRollback();
                        $msg = ['status'=>'error','message'=>'Area yang dipilih tidak ditemukan.'];
                        return $isAjax
                            ? $this->response->setJSON($msg)
                            : redirect()->back()->with('error', $msg['message']);
                    }

                    $now = date('Y-m-d H:i:s');
                    $batch = [];
                    foreach ($existIds as $aid) {
                        $batch[] = ['vendor_id'=>$this->vendorId, 'area_id'=>(int)$aid, 'created_at'=>$now];
                    }
                    $vendorAreaModel->insertBatch($batch);

                    $db->transComplete();
                    
                    // PERBAIKAN: gunakan helper untuk log
                    if (function_exists('log_activity_auto')) {
                        log_activity_auto('set', 'Set area (' . count($existIds) . '): ' . $this->describeAreas($existIds, 8, true), [
                            'module' => 'vendor_areas',
                            'vendor_id' => $this->vendorId,
                            'area_ids' => $existIds
                        ]);
                    }
                }
            }

            $msg = ['status'=>'success','message'=>'Area layanan berhasil disimpan.'];
            if ($isAjax) {
                return $this->response->setJSON(array_merge($msg, [
                    'redirect' => site_url('vendoruser/areas'),
                ]));
            }
            return redirect()->to(site_url('vendoruser/areas'))->with('success', $msg['message']);

        } catch (\Throwable $e) {
            $db->transRollback();
            
            // PERBAIKAN: gunakan helper untuk log error
            if (function_exists('log_activity_auto')) {
                log_activity_auto('error', 'Gagal menyimpan area: ' . $e->getMessage(), [
                    'module' => 'vendor_areas',
                    'vendor_id' => $this->vendorId
                ]);
            }
            
            $msg = ['status'=>'error','message'=>'Gagal menyimpan area: ' . $e->getMessage()];
            if ($isAjax) {
                return $this->response->setJSON($msg);
            }
            return redirect()->back()->with('error', $msg['message']);
        }
    }

    public function delete()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'error','message'=>'Invalid request']);
        }
        if (! $this->initVendor()) {
            return $this->response->setJSON(['status'=>'error','message'=>'Profil vendor belum ada.']);
        }

        $aid  = (int)$this->request->getPost('area_id');
        $am   = new AreasModel();
        $area = $am->select('id,name,type,parent_id')->find($aid);

        $vendorAreaModel = new VendorAreasModel();
        $ok = $vendorAreaModel->where('vendor_id', $this->vendorId)
                              ->where('area_id', $aid)
                              ->delete();

        if ($ok) {
            $desc = 'Hapus area';
            if ($area) { 
                $desc .= ': ' . $this->buildPathById($am, (int)$area['id']); 
            }
            
            // PERBAIKAN: gunakan helper untuk log
            if (function_exists('log_activity_auto')) {
                log_activity_auto('delete', $desc, [
                    'module' => 'vendor_areas',
                    'vendor_id' => $this->vendorId,
                    'area_id' => $aid
                ]);
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Area dihapus.'
            ]);
        }

        return $this->response->setJSON([
            'status'=>'error',
            'message'=>'Gagal menghapus area.'
        ]);
    }

    public function form()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'));
        }
        
        // Log aktivitas form
        if (function_exists('log_activity_auto')) {
            log_activity_auto('view_form', 'Membuka form area layanan', [
                'module' => 'vendor_areas',
                'vendor_id' => $this->vendorId
            ]);
        }
        
        return view('vendoruser/areas/form', $this->withVendorData());
    }
}