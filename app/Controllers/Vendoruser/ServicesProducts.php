<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorServicesProductsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use Config\Database;

class ServicesProducts extends BaseController
{
    private $vendorProfile;
    private $isVerified;
    private $vendorId;
    private $userId;
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /* ------------------------ Utils ------------------------ */
    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->userId = (int) ($user->id ?? 0);

        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', $this->userId)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? null;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorId;
    }

    private function withVendorData(array $data = [])
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function respondAjax(string $status, string $message, int $httpCode = 200, array $extra = [])
    {
        if ($this->request->isAJAX() || $this->request->getHeaderLine('X-Requested-With') === 'XMLHttpRequest') {
            return $this->response->setStatusCode($httpCode)->setJSON(array_merge([
                'status'   => $status,
                'message'  => $message,
                'csrfHash' => csrf_hash(),
            ], $extra));
        }
        $type = $status === 'success' ? 'success' : 'error';
        return redirect()->back()->with($type, $message);
    }

    private function logActivity(string $action, string $description, string $module = 'services_products'): void
    {
        try {
            (new ActivityLogsModel())->insert([
                'user_id'     => $this->userId,
                'vendor_id'   => $this->vendorId,
                'module'      => $module,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // abaikan error logging
        }
    }

    /* ----------------------------- List ----------------------------- */
    public function index()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $list = (new VendorServicesProductsModel())
            ->where('vendor_id', $this->vendorId)
            ->orderBy('service_name', 'ASC')
            ->orderBy('product_name', 'ASC')
            ->findAll();

        $this->logActivity('view', 'Melihat daftar layanan dan produk');

        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Layanan & Produk',
            'content_view' => 'vendoruser/services_products/index',
            'content_data' => [
                'page'             => 'Layanan & Produk',
                'servicesProducts' => $list,
            ],
        ]));
    }

    /* ---------------------------- Create ---------------------------- */
    public function create()
    {
        return $this->createGroup();
    }

    public function createGroup()
    {
        if (! $this->initVendor()) {
            return $this->response->setStatusCode(400)->setBody('Profil vendor belum ada.');
        }

        $this->logActivity('view_form', 'Membuka form tambah layanan/produk');
        return view('vendoruser/services_products/create', $this->withVendorData([
            'page' => 'Tambah Layanan / Produk',
        ]));
    }

public function store()
{
    if (! $this->initVendor()) {
        if ($this->request->isAJAX()) {
            return $this->respondAjax('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.', 400);
        }
        return redirect()->back()->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
    }

    // Validasi input
    $validation = \Config\Services::validation();
    $validation->setRules([
        'service_name' => 'required|min_length[3]|max_length[255]',
        'products.*.product_name' => 'required|min_length[2]|max_length[255]',
        'products.*.price' => 'permit_empty|numeric|greater_than_equal_to[0]',
    ], [
        'service_name' => [
            'required' => 'Nama layanan wajib diisi.',
            'min_length' => 'Nama layanan minimal 3 karakter.',
            'max_length' => 'Nama layanan maksimal 255 karakter.'
        ],
        'products.*.product_name' => [
            'required' => 'Nama produk wajib diisi.',
            'min_length' => 'Nama produk minimal 2 karakter.',
            'max_length' => 'Nama produk maksimal 255 karakter.'
        ],
        'products.*.price' => [
            'numeric' => 'Harga harus berupa angka.',
            'greater_than_equal_to' => 'Harga tidak boleh negatif.'
        ]
    ]);

    if (!$validation->withRequest($this->request)->run()) {
        $errors = $validation->getErrors();
        if ($this->request->isAJAX()) {
            return $this->respondAjax('error', implode(', ', $errors), 422);
        }
        return redirect()->back()->withInput()->with('errors', $errors);
    }

    $svcName  = trim((string) $this->request->getPost('service_name'));
    $svcDesc  = trim((string) $this->request->getPost('service_description'));
    $products = $this->request->getPost('products') ?? [];

    // Validasi manual tambahan
    if (!is_array($products) || count($products) === 0) {
        $errorMsg = 'Tambahkan minimal 1 produk.';
        if ($this->request->isAJAX()) {
            return $this->respondAjax('error', $errorMsg, 422);
        }
        return redirect()->back()->withInput()->with('error', $errorMsg);
    }

    $m = new VendorServicesProductsModel();
    $rowsToInsert = [];
    $validProducts = 0;

    foreach ($products as $index => $p) {
        $productName = trim($p['product_name'] ?? '');
        if ($productName === '') {
            continue; // Skip produk tanpa nama
        }

        // Handle file upload jika ada
        $attachmentName = null;
        $file = $this->request->getFile("products.{$index}.attachment");
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                $newName = $file->getRandomName();
                if ($file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName)) {
                    $attachmentName = $newName;
                }
            }
        }

        $rowsToInsert[] = [
            'vendor_id'           => $this->vendorId,
            'service_name'        => $svcName,
            'service_description' => !empty($svcDesc) ? $svcDesc : null,
            'product_name'        => $productName,
            'product_description' => !empty(trim($p['product_description'] ?? '')) ? trim($p['product_description']) : null,
            'price'               => isset($p['price']) && $p['price'] !== '' ? (float) $p['price'] : null,
            'attachment'          => $attachmentName,
            'attachment_url'      => !empty(trim($p['attachment_url'] ?? '')) ? trim($p['attachment_url']) : null,
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];
        
        $validProducts++;
    }

    if ($validProducts === 0) {
        $errorMsg = 'Tidak ada produk valid yang bisa disimpan. Pastikan nama produk diisi.';
        if ($this->request->isAJAX()) {
            return $this->respondAjax('error', $errorMsg, 422);
        }
        return redirect()->back()->withInput()->with('error', $errorMsg);
    }

    // Gunakan transaction database untuk konsistensi
    $this->db->transStart();
    
    try {
        foreach ($rowsToInsert as $row) {
            $m->insert($row);
        }
        
        $this->db->transComplete();
        
        if ($this->db->transStatus() === FALSE) {
            throw new \Exception('Gagal menyimpan data ke database.');
        }
        
        $this->logActivity('create', "Menambah layanan: {$svcName} (" . count($rowsToInsert) . " produk)");
        
        $successMsg = 'Layanan & produk berhasil ditambahkan.';
        
        if ($this->request->isAJAX()) {
            return $this->respondAjax('success', $successMsg, 200, [
                'redirect' => route_to('sp_index')
            ]);
        }
        
        return redirect()->to(route_to('sp_index'))->with('success', $successMsg);
        
    } catch (\Exception $e) {
        $this->db->transRollback();
        
        // Hapus file yang sudah diupload jika ada error
        foreach ($rowsToInsert as $row) {
            if (!empty($row['attachment'])) {
                @unlink(ROOTPATH . 'public/uploads/vendor_products/' . $row['attachment']);
            }
        }
        
        $errorMsg = 'Terjadi kesalahan sistem: ' . $e->getMessage();
        log_message('error', $errorMsg);
        
        if ($this->request->isAJAX()) {
            return $this->respondAjax('error', 'Terjadi kesalahan sistem. Silakan coba lagi.', 500);
        }
        
        return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    }
}

    /* ----------------------------- Edit ----------------------------- */
    public function edit($serviceName = null)
    {
        return $this->editGroup();
    }

    public function editGroup()
    {
        if (! $this->initVendor()) {
            return $this->response->setStatusCode(400)->setBody('Profil vendor belum ada.');
        }

        $serviceName = trim((string) $this->request->getGet('service_name'));
        if ($serviceName === '') {
            return $this->response->setStatusCode(400)->setBody('service_name required');
        }

        $m = new VendorServicesProductsModel();

        $products = $m->where('vendor_id', $this->vendorId)
                      ->where('service_name', $serviceName)
                      ->orderBy('id', 'ASC')
                      ->findAll();

        if (empty($products)) {
            return $this->response->setStatusCode(404)->setBody('Service not found');
        }

        $serviceDescription = $products[0]['service_description'] ?? '';

        $this->logActivity('view_form', "Membuka form edit service group: {$serviceName}");

        return view('vendoruser/services_products/edit', $this->withVendorData([
            'page'               => 'Edit Layanan / Produk',
            'serviceName'        => $serviceName,
            'serviceDescription' => $serviceDescription,
            'products'           => $products,
        ]));
    }

    /* ---------------------------- Update ---------------------------- */
    public function update()
    {
        return $this->updateGroup();
    }

    public function updateGroup()
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada.', 400);
        }

        $m = new VendorServicesProductsModel();

        $svcNameOrig = trim((string)$this->request->getPost('service_name_original'));
        $svcName     = trim((string)$this->request->getPost('service_name'));
        $svcDesc     = (string)$this->request->getPost('service_description');

        $products = $this->request->getPost('products') ?? [];

        if ($svcNameOrig !== '') {
            $m->where('vendor_id', $this->vendorId)
              ->where('service_name', $svcNameOrig)
              ->set([
                  'service_name'        => $svcName,
                  'service_description' => $svcDesc,
                  'updated_at'          => date('Y-m-d H:i:s'),
              ])->update();
        }

        foreach ($products as $i => $prod) {
            $pid   = (int)($prod['id'] ?? 0);
            $pname = trim((string)($prod['product_name'] ?? ''));
            $pdesc = trim((string)($prod['product_description'] ?? ''));
            $price = $prod['price'] ?? null;
            $url   = trim((string)($prod['attachment_url'] ?? ''));
            $del   = (int)($prod['delete_flag'] ?? 0) === 1;
            $rmAtt = (int)($prod['remove_attachment'] ?? 0) === 1;

            $file  = $this->request->getFile("products.{$i}.attachment");

            // Hapus
            if ($pid && $del) {
                $row = $m->where(['id'=>$pid, 'vendor_id'=>$this->vendorId])->first();
                if ($row && !empty($row['attachment'])) {
                    @unlink(ROOTPATH . 'public/uploads/vendor_products/' . $row['attachment']);
                }
                $m->delete($pid);
                continue;
            }

            $clearAttachment = false;
            $newAttachment   = null;

            if ($rmAtt && $pid) {
                $row = $m->where(['id'=>$pid, 'vendor_id'=>$this->vendorId])->first();
                if ($row && !empty($row['attachment'])) {
                    @unlink(ROOTPATH . 'public/uploads/vendor_products/' . $row['attachment']);
                }
                $clearAttachment = true;
            }

            if ($file && $file->isValid() && !$file->hasMoved()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['pdf','png','jpg','jpeg'])) {
                    $newName = $file->getRandomName();
                    $file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName);
                    $newAttachment   = $newName;
                    $clearAttachment = false;
                }
            }

            if ($pid) {
                $data = [
                    'service_name'        => $svcName,
                    'service_description' => $svcDesc,
                    'product_name'        => $pname,
                    'product_description' => $pdesc,
                    'price'               => ($price === '' ? null : (float)$price),
                    'attachment_url'      => $url !== '' ? $url : null,
                    'updated_at'          => date('Y-m-d H:i:s'),
                ];
                if ($clearAttachment) {
                    $data['attachment'] = null;
                } elseif ($newAttachment !== null) {
                    $data['attachment'] = $newAttachment;
                }
                $m->update($pid, $data);
                continue;
            }

            // Tambah baru pada update
            $hasSomething = $pname !== '' || $pdesc !== '' || $url !== '' || $newAttachment || (string)$price !== '';
            if ($hasSomething) {
                $data = [
                    'vendor_id'           => $this->vendorId,
                    'service_name'        => $svcName,
                    'service_description' => $svcDesc,
                    'product_name'        => $pname,
                    'product_description' => $pdesc,
                    'price'               => ($price === '' ? null : (float)$price),
                    'attachment'          => $newAttachment ?: null,
                    'attachment_url'      => $url !== '' ? $url : null,
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                ];
                $m->insert($data);
            }
        }

        $this->logActivity('update', "Update group layanan: {$svcName}");
        return $this->respondAjax('success', 'Layanan & semua produk berhasil diperbarui.');
    }

    /* ---------------------------- Delete ---------------------------- */
    public function delete($id)
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada.', 400);
        }

        $m  = new VendorServicesProductsModel();
        $sp = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();
        if (! $sp) {
            return $this->respondAjax('error', 'Data tidak ditemukan.', 404);
        }

        if (!empty($sp['attachment'])) {
            @unlink(ROOTPATH . 'public/uploads/vendor_products/' . $sp['attachment']);
        }

        $m->delete((int) $id);

        $this->logActivity('delete', "Menghapus: {$sp['service_name']} / {$sp['product_name']} (ID: {$id})");
        return $this->respondAjax('success', 'Layanan/Produk berhasil dihapus.');
    }

    public function deleteMultiple()
    {
        if (! $this->initVendor()) {
            return $this->respondAjax('error', 'Profil vendor belum ada.', 400);
        }

        $payload = $this->request->isAJAX() ? $this->request->getJSON(true) : $this->request->getPost();
        $serviceNames = $payload['service_names'] ?? [];

        if (empty($serviceNames) || !is_array($serviceNames)) {
            return $this->respondAjax('error', 'Tidak ada layanan yang dipilih.', 422);
        }

        $serviceNames = array_values(array_unique(array_filter(array_map('trim', $serviceNames))));
        if (empty($serviceNames)) {
            return $this->respondAjax('error', 'Pilihan tidak valid.', 422);
        }

        $m = new VendorServicesProductsModel();

        $rows = $m->where('vendor_id', $this->vendorId)
                  ->whereIn('service_name', $serviceNames)
                  ->findAll();
        foreach ($rows as $row) {
            if (!empty($row['attachment'])) {
                @unlink(ROOTPATH . 'public/uploads/vendor_products/' . $row['attachment']);
            }
        }

        $m->where('vendor_id', $this->vendorId)
          ->whereIn('service_name', $serviceNames)
          ->delete();

        $affected = $this->db->affectedRows();
        if ($affected > 0) {
            $this->logActivity('delete', 'Menghapus layanan: ' . implode(', ', $serviceNames));
            return $this->respondAjax('success', "{$affected} baris terhapus dari " . count($serviceNames) . " layanan.");
        }

        return $this->respondAjax('error', 'Tidak ada data yang terhapus.', 404);
    }
}
