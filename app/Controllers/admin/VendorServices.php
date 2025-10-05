<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorServicesProductsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class VendorServices extends BaseController
{
    protected $vendorServicesProductsModel;
    protected $vendorProfilesModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->vendorServicesProductsModel = new VendorServicesProductsModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        // Get all vendors with their services/products
        $vendors = $this->vendorProfilesModel
            ->select('id, business_name, status')
            ->findAll();

        $vendorServices = [];
        foreach ($vendors as $vendor) {
            $services = $this->vendorServicesProductsModel->getGroupedServicesProducts($vendor['id']);
            
            $vendorServices[] = [
                'vendor' => $vendor,
                'services' => $services
            ];
        }

        $data = [
            'title' => 'Layanan & Produk Vendor',
            'vendorServices' => $vendorServices
        ];

        return view('admin/layanan_produk_vendor/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Layanan & Produk Vendor',
            'vendors' => $this->vendorProfilesModel->findAll()
        ];

        return view('admin/layanan_produk_vendor/create', $data);
    }

    public function store()
    {
        $vendorId = $this->request->getPost('vendor_id');
        $serviceName = $this->request->getPost('service_name');
        $serviceDescription = $this->request->getPost('service_description');
        $products = $this->request->getPost('products') ?? [];

        if (empty($vendorId) || empty($serviceName) || empty($products)) {
            $response = [
                'status' => 'error',
                'message' => 'Lengkapi semua field yang diperlukan'
            ];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($response);
            }
            
            return redirect()->back()->with('error', $response['message']);
        }

        // Check if vendor exists
        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            $response = [
                'status' => 'error',
                'message' => 'Vendor tidak ditemukan'
            ];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($response);
            }
            
            return redirect()->back()->with('error', $response['message']);
        }

        // Insert service and products
        foreach ($products as $product) {
            if (empty($product['product_name'])) continue;

            $data = [
                'vendor_id' => $vendorId,
                'service_name' => $serviceName,
                'service_description' => $serviceDescription,
                'product_name' => $product['product_name'],
                'product_description' => $product['product_description'] ?? '',
                'price' => $product['price'] ?? 0,
                'attachment_url' => $product['attachment_url'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle file upload
            $file = $this->request->getFile('attachment_' . $product['temp_id']);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName);
                $data['attachment'] = $newName;
            }

            $this->vendorServicesProductsModel->insert($data);
        }

        // Log activity
        $this->activityLogsModel->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'vendor_services',
            'action' => 'create',
            'description' => 'Menambah layanan/produk untuk vendor: ' . $vendor->business_name,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $response = [
            'status' => 'success',
            'message' => 'Layanan & produk vendor berhasil ditambahkan',
            'redirect' => site_url('admin/services')
        ];
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($response);
        }
        
        return redirect()->to('admin/services')->with('success', $response['message']);
    }

    public function edit($id)
    {
        $service = $this->vendorServicesProductsModel->find($id);
        if (!$service) {
            return redirect()->to('admin/services')->with('error', 'Layanan tidak ditemukan');
        }

        $vendor = $this->vendorProfilesModel->find($service['vendor_id']);
        
        // Get all products for this service
        $products = $this->vendorServicesProductsModel
            ->where('vendor_id', $service['vendor_id'])
            ->where('service_name', $service['service_name'])
            ->findAll();

        $data = [
            'title' => 'Edit Layanan & Produk Vendor',
            'service' => $service,
            'vendor' => $vendor,
            'products' => $products,
            'vendors' => $this->vendorProfilesModel->findAll()
        ];

        return view('admin/layanan_produk_vendor/edit', $data);
    }

    public function update($id)
    {
        $service = $this->vendorServicesProductsModel->find($id);
        if (!$service) {
            $response = [
                'status' => 'error',
                'message' => 'Layanan tidak ditemukan'
            ];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($response);
            }
            
            return redirect()->back()->with('error', $response['message']);
        }

        $vendorId = $this->request->getPost('vendor_id');
        $serviceName = $this->request->getPost('service_name');
        $serviceDescription = $this->request->getPost('service_description');
        $products = $this->request->getPost('products') ?? [];

        if (empty($vendorId) || empty($serviceName) || empty($products)) {
            $response = [
                'status' => 'error',
                'message' => 'Lengkapi semua field yang diperlukan'
            ];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($response);
            }
            
            return redirect()->back()->with('error', $response['message']);
        }

        // Check if vendor exists
        $vendor = $this->vendorProfilesModel->find($vendorId);
        if (!$vendor) {
            $response = [
                'status' => 'error',
                'message' => 'Vendor tidak ditemukan'
            ];
            
            if ($this->request->isAJAX()) {
                return $this->response->setJSON($response);
            }
            
            return redirect()->back()->with('error', $response['message']);
        }

        // Delete all existing products for this service
        $this->vendorServicesProductsModel
            ->where('vendor_id', $service['vendor_id'])
            ->where('service_name', $service['service_name'])
            ->delete();

        // Insert new service and products
        foreach ($products as $product) {
            if (empty($product['product_name'])) continue;

            $data = [
                'vendor_id' => $vendorId,
                'service_name' => $serviceName,
                'service_description' => $serviceDescription,
                'product_name' => $product['product_name'],
                'product_description' => $product['product_description'] ?? '',
                'price' => $product['price'] ?? 0,
                'attachment_url' => $product['attachment_url'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            // Handle file upload
            $file = $this->request->getFile('attachment_' . $product['temp_id']);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/vendor_products/', $newName);
                $data['attachment'] = $newName;
            }

            $this->vendorServicesProductsModel->insert($data);
        }

        // Log activity
        $this->activityLogsModel->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'vendor_services',
            'action' => 'update',
            'description' => 'Mengubah layanan/produk untuk vendor: ' . $vendor->business_name,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $response = [
            'status' => 'success',
            'message' => 'Layanan & produk vendor berhasil diperbarui',
            'redirect' => site_url('admin/services')
        ];
        
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($response);
        }
        
        return redirect()->to('admin/services')->with('success', $response['message']);
    }

    public function delete($id)
    {
        $service = $this->vendorServicesProductsModel->find($id);
        if (!$service) {
            return redirect()->back()->with('error', 'Layanan tidak ditemukan');
        }

        $vendor = $this->vendorProfilesModel->find($service['vendor_id']);

        // Delete all products for this service
        $this->vendorServicesProductsModel
            ->where('vendor_id', $service['vendor_id'])
            ->where('service_name', $service['service_name'])
            ->delete();

        // Log activity
        $this->activityLogsModel->insert([
            'user_id' => session()->get('user_id'),
            'module' => 'vendor_services',
            'action' => 'delete',
            'description' => 'Menghapus layanan/produk untuk vendor: ' . ($vendor ? $vendor->business_name : 'Unknown'),
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('admin/services')->with('success', 'Layanan & produk vendor berhasil dihapus');
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