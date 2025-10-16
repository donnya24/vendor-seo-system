<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController; // Perbaikan: Extend BaseAdminController
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel; // Tambahkan model ActivityLogs

class Leads extends BaseAdminController // Perbaikan: Extend BaseAdminController
{
    protected $leadsModel;
    protected $vendorModel;
    protected $activityLogsModel; // Tambahkan property

    public function __construct()
    {
        // Hapus parent::__construct() karena BaseController tidak memiliki constructor
        $this->leadsModel = new LeadsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel(); // Inisialisasi model
    }

    public function index()
    {
        // Log activity akses halaman leads
        $this->logActivity(
            'view_leads',
            'Mengakses halaman manajemen leads'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();
        
        $request = service('request');
        $vendorId = $request->getGet('vendor_id');

        $builder = $this->leadsModel
            ->select('leads.*, vendor_profiles.business_name AS vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
            ->orderBy('leads.id', 'DESC');

        // Filter by vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('leads.vendor_id', $vendorId);
        }

        $leads = $builder->findAll();
        $vendors = $this->vendorModel
            ->select('id, business_name')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/leads/index', array_merge([
            'page'     => 'Leads',
            'leads'    => $leads,
            'vendors'  => $vendors,
            'vendor_id' => $vendorId,
        ], $commonData));
    }

    public function create()
    {
        // Log activity akses form create lead
        $this->logActivity(
            'view_create_lead',
            'Mengakses form create lead'
        );

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/leads/create', array_merge([
            'vendors' => $this->vendorModel->findAll(),
        ], $commonData));
    }

    public function edit($id)
    {
        // Log activity akses form edit lead
        $this->logActivity(
            'view_edit_lead',
            'Mengakses form edit lead',
            ['lead_id' => $id]
        );

        $lead = $this->leadsModel->find($id);

        if (!$lead) {
            return redirect()->to('admin/leads')->with('error', 'Lead tidak ditemukan');
        }

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/leads/edit', array_merge([
            'lead'    => $lead,
            'vendors' => $this->vendorModel->findAll(),
        ], $commonData));
    }

    public function store()
    {
        $vendorId = $this->request->getPost('vendor_id');
        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        $jumlahLeadsMasuk = $this->request->getPost('jumlah_leads_masuk');
        $jumlahLeadsClosing = $this->request->getPost('jumlah_leads_closing');
        
        // Validasi tanggal
        if (empty($tanggalMulai)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal mulai wajib diisi'
            ]);
        }
        
        // Jika tanggal selesai kosong, gunakan tanggal mulai
        if (empty($tanggalSelesai)) {
            $tanggalSelesai = $tanggalMulai;
        }
        
        // Validasi tanggal selesai tidak boleh lebih awal dari tanggal mulai
        if (strtotime($tanggalSelesai) < strtotime($tanggalMulai)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai'
            ]);
        }
        
        $leadId = $this->leadsModel->insert([
            'vendor_id'            => $vendorId,
            'tanggal_mulai'        => $tanggalMulai,
            'tanggal_selesai'      => $tanggalSelesai,
            'jumlah_leads_masuk'   => $jumlahLeadsMasuk,
            'jumlah_leads_closing' => $jumlahLeadsClosing,
            'reported_by_vendor'   => $this->request->getPost('reported_by_vendor') ?? 0,
            'assigned_at'          => null,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        // Log activity create lead
        $this->logActivity(
            'create_lead',
            'Menambahkan lead baru untuk vendor ID: ' . $vendorId,
            [
                'lead_id' => $leadId,
                'vendor_id' => $vendorId,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'jumlah_leads_masuk' => $jumlahLeadsMasuk,
                'jumlah_leads_closing' => $jumlahLeadsClosing
            ]
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lead berhasil ditambahkan'
        ]);
    }

    public function update($id)
    {
        $lead = $this->leadsModel->find($id);
        
        if (!$lead) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lead tidak ditemukan'
            ]);
        }

        $vendorId = $this->request->getPost('vendor_id');
        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        $jumlahLeadsMasuk = $this->request->getPost('jumlah_leads_masuk');
        $jumlahLeadsClosing = $this->request->getPost('jumlah_leads_closing');
        
        $this->leadsModel->update($id, [
            'vendor_id'            => $vendorId,
            'tanggal_mulai'        => $tanggalMulai,
            'tanggal_selesai'      => $tanggalSelesai,
            'jumlah_leads_masuk'   => $jumlahLeadsMasuk,
            'jumlah_leads_closing' => $jumlahLeadsClosing,
            'reported_by_vendor'   => $this->request->getPost('reported_by_vendor') ?? 0,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        // Log activity update lead
        $this->logActivity(
            'update_lead',
            'Memperbarui lead ID: ' . $id,
            [
                'lead_id' => $id,
                'vendor_id' => $vendorId,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'jumlah_leads_masuk' => $jumlahLeadsMasuk,
                'jumlah_leads_closing' => $jumlahLeadsClosing
            ]
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lead berhasil diperbarui'
        ]);
    }

    public function delete($id)
    {
        $lead = $this->leadsModel->find($id);
        
        if (!$lead) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lead tidak ditemukan'
            ]);
        }

        $this->leadsModel->delete($id);
        
        // Log activity delete lead
        $this->logActivity(
            'delete_lead',
            'Menghapus lead ID: ' . $id,
            [
                'lead_id' => $id,
                'vendor_id' => $lead['vendor_id'] ?? null,
                'tanggal_mulai' => $lead['tanggal_mulai'] ?? null,
                'tanggal_selesai' => $lead['tanggal_selesai'] ?? null,
                'jumlah_leads_masuk' => $lead['jumlah_leads_masuk'] ?? null,
                'jumlah_leads_closing' => $lead['jumlah_leads_closing'] ?? null
            ]
        );

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lead berhasil dihapus'
        ]);
    }
    public function deleteAll()
    {
        $request = service('request');
        $vendorId = $request->getGet('vendor_id');

        $builder = $this->leadsModel;

        // Hapus berdasarkan filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('vendor_id', $vendorId);
        }

        $leadsToDelete = $builder->findAll();
        $deletedCount = count($leadsToDelete);

        if ($deletedCount > 0) {
            // Hapus data
            if (!empty($vendorId)) {
                $builder->where('vendor_id', $vendorId)->delete();
            } else {
                $builder->delete(); // Hapus semua
            }

            // Log aktivitas
            $this->logActivity(
                'delete_all_leads',
                "Menghapus {$deletedCount} data leads" . (!empty($vendorId) ? " untuk vendor ID {$vendorId}" : ""),
                [
                    'module' => 'leads',
                    'deleted_count' => $deletedCount,
                    'filtered_vendor' => $vendorId ?? 'all'
                ]
            );

            return redirect()->back()->with('success', "Berhasil menghapus {$deletedCount} data leads.");
        }

        return redirect()->back()->with('error', 'Tidak ada data yang bisa dihapus.');
    }

    public function show($id)
    {
        // Log activity view detail lead
        $this->logActivity(
            'view_lead_detail',
            'Melihat detail lead ID: ' . $id,
            ['lead_id' => $id]
        );

        $lead = $this->leadsModel->select('leads.*, vendor_profiles.business_name as vendor_name')
                        ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
                        ->find($id);

        if (!$lead) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Lead dengan ID $id tidak ditemukan");
        }

        // Load common data for header (termasuk notifikasi)
        $commonData = $this->loadCommonData();

        // Merge dengan common data (termasuk notifikasi)
        return view('admin/leads/show', array_merge([
            'lead' => $lead,
            'vendors' => $this->vendorModel->findAll(),
        ], $commonData));
    }

   public function export()
    {
        $request = service('request');
        $vendorId = $request->getGet('vendor_id');

        // Ambil data leads (pakai kolom yang benar)
        $builder = $this->leadsModel
            ->select('
                leads.id, 
                vendor_profiles.business_name AS vendor_name, 
                leads.jumlah_leads_masuk, 
                leads.jumlah_leads_closing, 
                leads.tanggal_mulai, 
                leads.tanggal_selesai,
                leads.updated_at
            ')
            ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
            ->orderBy('leads.id', 'DESC');

        if (!empty($vendorId)) {
            $builder->where('leads.vendor_id', $vendorId);
        }

        $leads = $builder->findAll();

        if (empty($leads)) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diexport.');
        }

        // Log activity export
        $this->logActivity(
            'export_leads_csv',
            'Mengekspor data leads ke CSV',
            ['vendor_id' => $vendorId ?? 'all', 'total' => count($leads)]
        );

        // Siapkan file CSV
        $filename = 'leads_export_' . date('Ymd_His') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Header kolom
        fputcsv($output, ['ID', 'Vendor', 'Leads Masuk', 'Leads Closing', 'Periode Tanggal', 'Tanggal Dibuat', 'Tanggal Diperbarui']);

        // Isi data
        foreach ($leads as $lead) {
            // Format periode tanggal
            $periodeTanggal = '';
            if (!empty($lead['tanggal_mulai']) && !empty($lead['tanggal_selesai'])) {
                $periodeTanggal = date('Y-m-d', strtotime($lead['tanggal_mulai'])) . ' s/d ' . date('Y-m-d', strtotime($lead['tanggal_selesai']));
            } elseif (!empty($lead['tanggal_mulai'])) {
                $periodeTanggal = date('Y-m-d', strtotime($lead['tanggal_mulai'])) . ' s/d sekarang';
            } elseif (!empty($lead['tanggal_selesai'])) {
                $periodeTanggal = 'sampai ' . date('Y-m-d', strtotime($lead['tanggal_selesai']));
            } else {
                $periodeTanggal = '-';
            }
            
            fputcsv($output, [
                $lead['id'],
                $lead['vendor_name'],
                $lead['jumlah_leads_masuk'],
                $lead['jumlah_leads_closing'],
                $periodeTanggal,
                $lead['updated_at'] ? date('Y-m-d H:i:s', strtotime($lead['updated_at'])) : '-'
            ]);
        }

        fclose($output);
        exit;
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
                'module'      => 'admin_leads',
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => (string) $this->request->getUserAgent(),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogsModel->insert($data);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}