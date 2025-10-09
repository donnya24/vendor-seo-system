<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorServicesProductsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use Config\Database;

class VendorServicesProducts extends BaseController
{
    protected $vendorServicesProductsModel;
    protected $vendorProfilesModel;
    protected $activityLogsModel;
    protected $db;

    public function __construct()
    {
        $this->vendorServicesProductsModel = new VendorServicesProductsModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->db = Database::connect();
    }

    public function index()
    {
        /** @var array[] $vendors */
        $vendors = $this->vendorProfilesModel
            ->select('id, business_name, status')
            ->findAll();

        $vendorServices = [];

        foreach ($vendors as $vendor) {
            $services = $this->vendorServicesProductsModel->getGroupedServicesProducts($vendor['id']);

            $vendorServices[] = [
                'vendor'   => $vendor,
                'services' => $services
            ];
        }

        $data = [
            'title'          => 'Layanan & Produk Vendor',
            'vendors'        => $vendors,
            'vendorServices' => $vendorServices
        ];

        return view('admin/layanan_produk_vendor/index', $data);
    }

    public function create()
    {
        $data = [
            'title'   => 'Tambah Layanan & Produk Vendor',
            'vendors' => $this->vendorProfilesModel->findAll()
        ];

        return view('admin/layanan_produk_vendor/create', $data);
    }

    public function store()
    {
        $vendorId           = $this->request->getPost('vendor_id');
        $serviceName        = $this->request->getPost('service_name');
        $serviceDescription = $this->request->getPost('service_description');
        $products           = $this->request->getPost('products') ?? [];

        if (empty($vendorId) || empty($serviceName) || empty($products)) {
            $response = [
                'status'  => 'error',
                'message' => 'Lengkapi semua field yang diperlukan'
            ];

            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->with('error', $response['message']);
        }

        /** @var array|null $vendor */
        $vendor = $this->vendorProfilesModel->find($vendorId);

        if (!$vendor) {
            $response = [
                'status'  => 'error',
                'message' => 'Vendor tidak ditemukan'
            ];

            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->with('error', $response['message']);
        }

        $this->db->transStart();

        try {
            foreach ($products as $index => $product) {
                if (empty($product['product_name'])) {
                    continue;
                }

                $price = (int)preg_replace('/[^\d]/', '', $product['price'] ?? '0');
                
                $data = [
                    'vendor_id'          => $vendorId,
                    'service_name'       => $serviceName,
                    'service_description'=> $serviceDescription,
                    'product_name'       => $product['product_name'],
                    'product_description'=> $product['product_description'] ?? '',
                    'price'              => (int)$price,
                    'attachment_url'     => $product['attachment_url'] ?? '',
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s')
                ];

                $file = $this->request->getFile("products.{$index}.attachment");
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                        $newName = $file->getRandomName();
                        if ($file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName)) {
                            $data['attachment'] = $newName;
                        }
                    }
                }

                $this->vendorServicesProductsModel->insert($data);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan data ke database.');
            }

            $this->activityLogsModel->insert([
                'user_id'     => session()->get('user_id'),
                'module'      => 'vendor_services',
                'action'      => 'create',
                'description' => 'Menambah layanan/produk untuk vendor: ' . $vendor['business_name'],
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            $response = [
                'status'   => 'success',
                'message'  => 'Layanan & produk vendor berhasil ditambahkan',
                'redirect' => site_url('admin/services')
            ];

            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->to('admin/services')->with('success', $response['message']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());

            $response = [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];

            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->withInput()->with('error', $response['message']);
        }
    }

    public function edit($id = null)
    {
        // Jika ID tidak ada, coba ambil dari query string
        if ($id === null) {
            $serviceName = $this->request->getGet('service_name');
            $vendorId    = $this->request->getGet('vendor_id');

            if (empty($serviceName) || empty($vendorId)) {
                return redirect()->to('admin/services')->with('error', 'Parameter tidak valid');
            }

            $service = $this->vendorServicesProductsModel
                ->where('vendor_id', $vendorId)
                ->where('service_name', $serviceName)
                ->first();

            if (!$service) {
                return redirect()->to('admin/services')->with('error', 'Layanan tidak ditemukan');
            }

            $id = $service['id'];
        }

        $service = $this->vendorServicesProductsModel->find($id);
        if (!$service) {
            return redirect()->to('admin/services')->with('error', 'Layanan tidak ditemukan');
        }

        /** @var array|null $vendor */
        $vendor = $this->vendorProfilesModel->find($service['vendor_id']);

        $products = $this->vendorServicesProductsModel
            ->where('vendor_id', $service['vendor_id'])
            ->where('service_name', $service['service_name'])
            ->findAll();

        $data = [
            'title'    => 'Edit Layanan & Produk Vendor',
            'service'  => $service,
            'vendor'   => $vendor,
            'products' => $products,
            'vendors'  => $this->vendorProfilesModel->findAll()
        ];

        return view('admin/layanan_produk_vendor/edit', $data);
    }

    public function update($id = null)
    {
        if ($id === null) {
            $serviceName = $this->request->getPost('service_name_original');
            $vendorId    = $this->request->getPost('vendor_id');

            if (empty($serviceName) || empty($vendorId)) {
                $response = [
                    'status'  => 'error',
                    'message' => 'Parameter tidak valid'
                ];
                return $this->request->isAJAX()
                    ? $this->response->setJSON($response)
                    : redirect()->back()->with('error', $response['message']);
            }

            $service = $this->vendorServicesProductsModel
                ->where('vendor_id', $vendorId)
                ->where('service_name', $serviceName)
                ->first();

            if (!$service) {
                $response = [
                    'status'  => 'error',
                    'message' => 'Layanan tidak ditemukan'
                ];
                return $this->request->isAJAX()
                    ? $this->response->setJSON($response)
                    : redirect()->back()->with('error', $response['message']);
            }

            $id = $service['id'];
        }

        $service = $this->vendorServicesProductsModel->find($id);
        if (!$service) {
            $response = [
                'status'  => 'error',
                'message' => 'Layanan tidak ditemukan'
            ];
            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->with('error', $response['message']);
        }

        $vendorId           = $this->request->getPost('vendor_id');
        $serviceName        = $this->request->getPost('service_name');
        $serviceDescription = $this->request->getPost('service_description');
        $products           = $this->request->getPost('products') ?? [];

        if (empty($vendorId) || empty($serviceName) || empty($products)) {
            $response = [
                'status'  => 'error',
                'message' => 'Lengkapi semua field yang diperlukan'
            ];
            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->with('error', $response['message']);
        }

        /** @var array|null $vendor */
        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            $response = [
                'status'  => 'error',
                'message' => 'Vendor tidak ditemukan'
            ];
            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->with('error', $response['message']);
        }

        $this->db->transStart();

        try {
            $this->vendorServicesProductsModel
                ->where('vendor_id', $service['vendor_id'])
                ->where('service_name', $service['service_name'])
                ->delete();

            foreach ($products as $index => $product) {
                if (empty($product['product_name'])) {
                    continue;
                }

                $price = (int)preg_replace('/[^\d]/', '', $product['price'] ?? '0');
                
                $data = [
                    'vendor_id'          => $vendorId,
                    'service_name'       => $serviceName,
                    'service_description'=> $serviceDescription,
                    'product_name'       => $product['product_name'],
                    'product_description'=> $product['product_description'] ?? '',
                    'price'              => (int)$price,
                    'attachment_url'     => $product['attachment_url'] ?? '',
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s')
                ];

                $file = $this->request->getFile("products.{$index}.attachment");
                if ($file && $file->isValid() && !$file->hasMoved()) {
                    $ext = strtolower($file->getExtension());
                    if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                        $newName = $file->getRandomName();
                        if ($file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName)) {
                            $data['attachment'] = $newName;
                        }
                    }
                }

                $this->vendorServicesProductsModel->insert($data);
            }

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                throw new \Exception('Gagal menyimpan data ke database.');
            }

            $this->activityLogsModel->insert([
                'user_id'     => session()->get('user_id'),
                'module'      => 'vendor_services',
                'action'      => 'update',
                'description' => 'Mengubah layanan/produk untuk vendor: ' . $vendor['business_name'],
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s')
            ]);

            $response = [
                'status'   => 'success',
                'message'  => 'Layanan & produk vendor berhasil diperbarui',
                'redirect' => site_url('admin/services')
            ];

            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->to('admin/services')->with('success', $response['message']);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());

            $response = [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];

            return $this->request->isAJAX()
                ? $this->response->setJSON($response)
                : redirect()->back()->withInput()->with('error', $response['message']);
        }
    }

    public function delete($id)
    {
        $service = $this->vendorServicesProductsModel->find($id);
        if (!$service) {
            return redirect()->back()->with('error', 'Layanan tidak ditemukan');
        }

        /** @var array|null $vendor */
        $vendor = $this->vendorProfilesModel->find($service['vendor_id']);

        $this->vendorServicesProductsModel
            ->where('vendor_id', $service['vendor_id'])
            ->where('service_name', $service['service_name'])
            ->delete();

        $businessName = $vendor ? $vendor['business_name'] : 'Unknown';

        $this->activityLogsModel->insert([
            'user_id'     => session()->get('user_id'),
            'module'      => 'vendor_services',
            'action'      => 'delete',
            'description' => 'Menghapus layanan/produk untuk vendor: ' . $businessName,
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => $this->request->getUserAgent()->getAgentString(),
            'created_at'  => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('admin/services')->with('success', 'Layanan & produk vendor berhasil dihapus');
    }

    public function deleteMultiple()
    {
        $serviceNames = $this->request->getPost('service_names');
        
        if (empty($serviceNames)) {
            $response = [
                'status'  => 'error',
                'message' => 'Tidak ada layanan yang dipilih'
            ];
            
            return $this->response->setJSON($response);
        }
        
        $this->db->transStart();
        
        try {
            $deletedCount = 0;
            
            foreach ($serviceNames as $serviceName) {
                // Cari layanan berdasarkan nama
                $service = $this->vendorServicesProductsModel
                    ->where('service_name', $serviceName)
                    ->first();
                    
                if ($service) {
                    // Hapus semua produk dengan layanan yang sama
                    $this->vendorServicesProductsModel
                        ->where('vendor_id', $service['vendor_id'])
                        ->where('service_name', $service['service_name'])
                        ->delete();
                        
                    $deletedCount++;
                }
            }
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Gagal menghapus data dari database.');
            }
            
            $this->activityLogsModel->insert([
                'user_id'     => session()->get('user_id'),
                'module'      => 'vendor_services',
                'action'      => 'delete_multiple',
                'description' => "Menghapus {$deletedCount} layanan/produk vendor",
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent()->getAgentString(),
                'created_at'  => date('Y-m-d H:i:s')
            ]);
            
            $response = [
                'status'  => 'success',
                'message' => "Berhasil menghapus {$deletedCount} layanan beserta produknya"
            ];
            
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
            
            $response = [
                'status'  => 'error',
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ];
            
            return $this->response->setJSON($response);
        }
    }

    public function search()
    {
        $q = $this->request->getGet('q');

        if (empty($q)) {
            return $this->response->setJSON([]);
        }

        $vendors = $this->vendorProfilesModel
            ->like('business_name', $q)
            ->findAll();

        return $this->response->setJSON($vendors);
    }
}