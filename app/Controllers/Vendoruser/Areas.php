<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\AreasModel;
use App\Models\VendorAreasModel;
use App\Models\VendorProfilesModel;

class Areas extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())
            ->where('user_id', (int)service('auth')->user()->id)
            ->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        $all = (new AreasModel())->orderBy('name','ASC')->findAll();
        $attached = (new VendorAreasModel())->where('vendor_id',$vid)->findAll();
        $attachedIds = array_column($attached,'area_id');

        return view('vendoruser/areas/index', [
            'page'=>'Area',
            'areas'=>$all,
            'attachedIds'=>$attachedIds
        ]);
    }

    // Method untuk menampilkan form create
    public function create()
    {
        return view('vendoruser/areas/create', [
            'title' => 'Tambah Area Baru'
        ]);
    }

    // Method untuk menyimpan area baru - DIPERBAIKI
    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        // DEBUG: Log semua data POST
        log_message('debug', 'Store Method - POST Data: ' . print_r($this->request->getPost(), true));
        
        $validation = \Config\Services::validation();
        $validation->setRules([
            'name' => 'required|min_length[3]|max_length[100]',
            'type' => 'required|in_list[city,province,region]'
        ]);
        
        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            log_message('debug', 'Store Method - Validation Errors: ' . print_r($errors, true));
            return $this->response->setJSON([
                'status' => 'error',
                'message' => implode(', ', $errors)
            ]);
        }
        
        // Pastikan type lowercase untuk konsistensi dengan ENUM
        $name = $this->request->getPost('name');
        $type = strtolower($this->request->getPost('type'));
        
        // Validasi manual untuk memastikan type sesuai ENUM
        $allowedTypes = ['city', 'province', 'region'];
        if (!in_array($type, $allowedTypes)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tipe area tidak valid. Pilih: city, province, atau region'
            ]);
        }
        
        log_message('debug', "Store Method - Name: {$name}, Type: {$type}");
        
        $data = [
            'name' => $name,
            'type' => $type,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        log_message('debug', 'Store Method - Data to insert: ' . print_r($data, true));
        
        $areaModel = new AreasModel();
        $vendorAreaModel = new VendorAreasModel();
        $vid = $this->vendorId();
        
        // Mulai transaction
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Insert ke tabel areas
            if (!$areaModel->insert($data)) {
                $errors = $areaModel->errors();
                log_message('debug', 'Store Method - Area Model Errors: ' . print_r($errors, true));
                throw new \Exception('Gagal menambahkan area: ' . implode(', ', $errors));
            }
            
            $newAreaId = $areaModel->getInsertID();
            log_message('debug', 'Store Method - New Area ID: ' . $newAreaId);
            
            // Otomatis tambahkan ke vendor_areas
            $vendorAreaData = [
                'vendor_id' => $vid,
                'area_id' => $newAreaId,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            if (!$vendorAreaModel->insert($vendorAreaData)) {
                $errors = $vendorAreaModel->errors();
                log_message('debug', 'Store Method - Vendor Area Model Errors: ' . print_r($errors, true));
                throw new \Exception('Gagal menambahkan area ke layanan vendor: ' . implode(', ', $errors));
            }
            
            $db->transComplete();
            
            log_message('debug', 'Store Method - Success: Area created with type: ' . $type);
            
            return $this->response->setJSON([
                'status' => 'success', 
                'message' => 'Area berhasil ditambahkan dan otomatis ditambahkan ke layanan Anda.'
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Store Method - Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => $e->getMessage()
            ]);
        }
    }

    // Method untuk menghapus area - DIPERBAIKI
    public function delete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }
        
        $vid = $this->vendorId();
        $aid = (int)$this->request->getPost('area_id');
        
        log_message('debug', 'Delete Method - Vendor ID: ' . $vid . ', Area ID: ' . $aid);
        
        $areaModel = new AreasModel();
        $vendorAreaModel = new VendorAreasModel();

        // Mulai transaction untuk konsistensi data
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // 1. Hapus dari vendor_areas (semua vendor yang menggunakan area ini)
            $vendorDeleted = $vendorAreaModel->where('area_id', $aid)->delete();
            log_message('debug', 'Delete Method - Vendor areas deleted: ' . $vendorDeleted);
            
            // 2. Hapus dari areas
            $deleted = $areaModel->delete($aid);
            log_message('debug', 'Delete Method - Areas deleted: ' . $deleted);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus area dari tabel areas');
            }
            
            $db->transComplete();
            
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Area berhasil dihapus permanen.'
            ]);
            
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Delete Method - Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}