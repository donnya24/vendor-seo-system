<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\VendorServicesProductsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use Config\Database;

class VendorServicesProducts extends BaseAdminController
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
        // Log activity akses halaman vendor services
        $this->logActivity(
            'view_vendor_services',
            'Mengakses halaman Layanan & Produk Vendor'
        );

        // Ambil semua vendor
        $vendors = $this->vendorProfilesModel
            ->select('id, business_name, status')
            ->findAll();

        // Siapkan array untuk menyimpan data vendor dan layanan
        $vendorServices = [];
        
        foreach ($vendors as $vendor) {
            // Ambil semua produk untuk vendor ini
            $products = $this->vendorServicesProductsModel
                ->where('vendor_id', $vendor['id'])
                ->orderBy('service_name', 'ASC')
                ->findAll();

            // Kelompokkan produk berdasarkan layanan
            $services = [];
            foreach ($products as $product) {
                $serviceName = $product['service_name'];
                
                if (!isset($services[$serviceName])) {
                    $services[$serviceName] = [
                        'service_name' => $serviceName,
                        'service_description' => $product['service_description'],
                        'products' => [],
                        'products_harga' => [],
                        'products_deskripsi' => [],
                        'products_lampiran' => [],
                        'products_lampiran_url' => []
                    ];
                }
                
                $services[$serviceName]['products'][] = $product['product_name'];
                $services[$serviceName]['products_harga'][] = $product['price'];
                $services[$serviceName]['products_deskripsi'][] = $product['product_description'];
                $services[$serviceName]['products_lampiran'][] = $product['attachment'];
                $services[$serviceName]['products_lampiran_url'][] = $product['attachment_url'];
            }
            
            // Konversi ke format yang sesuai untuk view
            $formattedServices = [];
            foreach ($services as $service) {
                $formattedServices[] = [
                    'service_name' => $service['service_name'],
                    'service_description' => $service['service_description'],
                    'products' => implode('<br>', $service['products']),
                    'products_harga' => implode('<br>', $service['products_harga']),
                    'products_deskripsi' => implode('<br>', $service['products_deskripsi']),
                    'products_lampiran' => implode('<br>', $service['products_lampiran']),
                    'products_lampiran_url' => implode('<br>', $service['products_lampiran_url'])
                ];
            }
            
            $vendorServices[] = [
                'vendor'   => $vendor,
                'services' => $formattedServices
            ];
        }

        // Load common data untuk header notifikasi
        $commonData = $this->loadCommonData();

        $data = [
            'title'          => 'Layanan & Produk Vendor',
            'vendors'        => $vendors,
            'vendorServices' => $vendorServices
        ];

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/layanan_produk_vendor/index', array_merge($data, $commonData));
    }

    public function create()
    {
        // Log activity akses form create
        $this->logActivity(
            'view_create_vendor_service',
            'Mengakses form tambah Layanan & Produk Vendor'
        );

        // Load common data untuk header notifikasi
        $commonData = $this->loadCommonData();

        $data = [
            'title'   => 'Tambah Layanan & Produk Vendor',
            'vendors' => $this->vendorProfilesModel->findAll()
        ];

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/layanan_produk_vendor/create', array_merge($data, $commonData));
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
                    'price'              => $price,
                    'attachment_url'     => $product['attachment_url'] ?? '',
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s')
                ];

                // Handle file upload
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

            // Log activity create vendor service
            $this->logActivity(
                'create_vendor_service',
                'Menambah layanan/produk untuk vendor: ' . $vendor['business_name'],
                [
                    'vendor_id' => $vendorId,
                    'service_name' => $serviceName,
                    'products_count' => count($products)
                ]
            );

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

            // Log activity error
            $this->logActivity(
                'error_vendor_service',
                'Gagal menambah layanan/produk vendor: ' . $e->getMessage(),
                [
                    'vendor_id' => $vendorId,
                    'service_name' => $serviceName
                ]
            );

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
        // Log activity akses form edit
        $this->logActivity(
            'view_edit_vendor_service',
            'Mengakses form edit Layanan & Produk Vendor',
            ['service_id' => $id]
        );

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

        // Load common data untuk header notifikasi
        $commonData = $this->loadCommonData();

        $data = [
            'title'    => 'Edit Layanan & Produk Vendor',
            'service'  => $service,
            'vendor'   => $vendor,
            'products' => $products,
            'vendors'  => $this->vendorProfilesModel->findAll()
        ];

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/layanan_produk_vendor/edit', array_merge($data, $commonData));
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
        // Hapus semua produk dengan layanan yang sama
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
                'price'              => $price,
                'attachment_url'     => $product['attachment_url'] ?? '',
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s')
            ];

            // Handle file upload
            $file = $this->request->getFile("products.{$index}.attachment");
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $ext = strtolower($file->getExtension());
                if (in_array($ext, ['pdf', 'png', 'jpg', 'jpeg'])) {
                    $newName = $file->getRandomName();
                    if ($file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName)) {
                        $data['attachment'] = $newName;
                        
                        // Hapus file lama jika ada
                        if (!empty($product['existing_attachment'])) {
                            $oldFilePath = ROOTPATH . 'public/uploads/vendor_products/' . $product['existing_attachment'];
                            if (file_exists($oldFilePath)) {
                                unlink($oldFilePath);
                            }
                        }
                    }
                }
            } else {
                // Jika tidak ada file baru, gunakan file yang ada jika tidak dihapus
                if (!empty($product['existing_attachment']) && empty($product['remove_attachment'])) {
                    $data['attachment'] = $product['existing_attachment'];
                }
            }

            $this->vendorServicesProductsModel->insert($data);
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === false) {
            throw new \Exception('Gagal menyimpan data ke database.');
        }

        // Log activity update vendor service
        $this->logActivity(
            'update_vendor_service',
            'Mengubah layanan/produk untuk vendor: ' . $vendor['business_name'],
            [
                'vendor_id' => $vendorId,
                'service_name' => $serviceName,
                'products_count' => count($products)
            ]
        );

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

        // Log activity error
        $this->logActivity(
            'error_vendor_service',
            'Gagal mengubah layanan/produk vendor: ' . $e->getMessage(),
            [
                'vendor_id' => $vendorId,
                'service_name' => $serviceName
            ]
        );

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

        // Log activity delete vendor service
        $this->logActivity(
            'delete_vendor_service',
            'Menghapus layanan/produk untuk vendor: ' . $businessName,
            [
                'vendor_id' => $service['vendor_id'],
                'service_name' => $service['service_name']
            ]
        );

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
            $deletedServices = [];
            
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
                    $deletedServices[] = [
                        'vendor_id' => $service['vendor_id'],
                        'service_name' => $service['service_name']
                    ];
                }
            }
            
            $this->db->transComplete();
            
            if ($this->db->transStatus() === false) {
                throw new \Exception('Gagal menghapus data dari database.');
            }

            // Log activity delete multiple vendor services
            $this->logActivity(
                'delete_multiple_vendor_services',
                "Menghapus {$deletedCount} layanan/produk vendor",
                [
                    'deleted_count' => $deletedCount,
                    'deleted_services' => $deletedServices
                ]
            );
            
            $response = [
                'status'  => 'success',
                'message' => "Berhasil menghapus {$deletedCount} layanan beserta produknya"
            ];
            
            return $this->response->setJSON($response);
        } catch (\Exception $e) {
            $this->db->transRollback();
            log_message('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());

            // Log activity error
            $this->logActivity(
                'error_delete_multiple_vendor_services',
                'Gagal menghapus multiple layanan/produk vendor: ' . $e->getMessage(),
                [
                    'service_names' => $serviceNames
                ]
            );
            
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

    /**
     * Log activity untuk admin
     */
    private function logActivity($action, $description, $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            
            $data = [
                'user_id'     => $user ? $user->id : null,
                'module'      => 'vendor_services',
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
            log_message('error', 'Failed to log activity in VendorServicesProducts: ' . $e->getMessage());
        }
    }
}