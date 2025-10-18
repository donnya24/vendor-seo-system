<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;

class Leads extends BaseAdminController
{
    protected $leadsModel;
    protected $vendorModel;
    protected $activityLogsModel;
    protected $notificationsModel;
    protected $userModel;

    public function __construct()
    {
        $this->leadsModel = new LeadsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
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

        // Dapatkan informasi vendor
        $vendor = $this->vendorModel->find($vendorId);
        $vendorName = is_object($vendor) ? $vendor->business_name : ($vendor['business_name'] ?? 'Unknown Vendor');
        
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

        // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
        $this->sendLeadNotification($leadId, $vendorId, 'create', [
            'vendor_name' => $vendorName,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'jumlah_leads_masuk' => $jumlahLeadsMasuk,
            'jumlah_leads_closing' => $jumlahLeadsClosing
        ]);

        // Log activity create lead
        $this->logActivity(
            'create_lead',
            'Menambahkan lead baru untuk vendor: ' . $vendorName,
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
        
        // Dapatkan informasi vendor
        $vendor = $this->vendorModel->find($vendorId);
        $vendorName = is_object($vendor) ? $vendor->business_name : ($vendor['business_name'] ?? 'Unknown Vendor');
        
        $this->leadsModel->update($id, [
            'vendor_id'            => $vendorId,
            'tanggal_mulai'        => $tanggalMulai,
            'tanggal_selesai'      => $tanggalSelesai,
            'jumlah_leads_masuk'   => $jumlahLeadsMasuk,
            'jumlah_leads_closing' => $jumlahLeadsClosing,
            'reported_by_vendor'   => $this->request->getPost('reported_by_vendor') ?? 0,
            'updated_at'           => date('Y-m-d H:i:s'),
        ]);

        // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO
        $this->sendLeadNotification($id, $vendorId, 'update', [
            'vendor_name' => $vendorName,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'jumlah_leads_masuk' => $jumlahLeadsMasuk,
            'jumlah_leads_closing' => $jumlahLeadsClosing
        ]);

        // Log activity update lead
        $this->logActivity(
            'update_lead',
            'Memperbarui lead ID: ' . $id . ' untuk vendor: ' . $vendorName,
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
        // PERBAIKAN: Periksa metode POST bukan DELETE
        if (!$this->request->is('post')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Metode request tidak diizinkan'
            ])->setStatusCode(405);
        }
        
        $lead = $this->leadsModel->find($id);
        
        if (!$lead) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lead tidak ditemukan'
            ]);
        }

        $vendorId = $lead['vendor_id'];
        // Dapatkan informasi vendor
        $vendor = $this->vendorModel->find($vendorId);
        $vendorName = is_object($vendor) ? $vendor->business_name : ($vendor['business_name'] ?? 'Unknown Vendor');

        // 🔔 KIRIM NOTIFIKASI KE VENDOR DAN TIM SEO SEBELUM DIHAPUS
        $this->sendLeadNotification($id, $vendorId, 'delete', [
            'vendor_name' => $vendorName,
            'tanggal_mulai' => $lead['tanggal_mulai'],
            'tanggal_selesai' => $lead['tanggal_selesai'],
            'jumlah_leads_masuk' => $lead['jumlah_leads_masuk'],
            'jumlah_leads_closing' => $lead['jumlah_leads_closing']
        ]);

        $this->leadsModel->delete($id);
        
        // Log activity delete lead
        $this->logActivity(
            'delete_lead',
            'Menghapus lead ID: ' . $id . ' untuk vendor: ' . $vendorName,
            [
                'lead_id' => $id,
                'vendor_id' => $vendorId,
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
        // PERBAIKAN: Periksa metode POST bukan DELETE
        if (!$this->request->is('post')) {
            return redirect()->back()->with('error', 'Metode request tidak diizinkan');
        }
        
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
            // 🔔 KIRIM NOTIFIKASI UNTUK SETIAP LEAD YANG DIHAPUS
            foreach ($leadsToDelete as $lead) {
                // Dapatkan informasi vendor
                $vendor = $this->vendorModel->find($lead['vendor_id']);
                $vendorName = is_object($vendor) ? $vendor->business_name : ($vendor['business_name'] ?? 'Unknown Vendor');
                
                $this->sendLeadNotification($lead['id'], $lead['vendor_id'], 'delete', [
                    'vendor_name' => $vendorName,
                    'tanggal_mulai' => $lead['tanggal_mulai'],
                    'tanggal_selesai' => $lead['tanggal_selesai'],
                    'jumlah_leads_masuk' => $lead['jumlah_leads_masuk'],
                    'jumlah_leads_closing' => $lead['jumlah_leads_closing']
                ]);
            }

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
     * Kirim notifikasi untuk lead
     */
    private function sendLeadNotification($leadId, $vendorId, $actionType, $data = [])
    {
        try {
            $db = \Config\Database::connect();
            
            // Dapatkan informasi vendor
            $vendor = $this->vendorModel->find($vendorId);
            if (!$vendor) {
                return false;
            }

            // Dapatkan user_id dari vendor
            $vendorUserId = is_object($vendor) ? $vendor->user_id : ($vendor['user_id'] ?? null);
            if (!$vendorUserId) {
                return false;
            }

            // Dapatkan semua admin users
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();

            // Dapatkan admin yang sedang login
            $currentUser = service('auth')->user();
            $adminUserId = $currentUser ? $currentUser->id : session()->get('user_id');
            $adminProfile = $db->table('admin_profiles')
                ->where('user_id', $adminUserId)
                ->get()
                ->getRowArray();
            $adminName = $adminProfile['name'] ?? 'Admin';

            // Dapatkan semua SEO users
            $seoUsers = $db->table('seo_profiles sp')
                ->select('sp.user_id')
                ->join('users u', 'u.id = sp.user_id')
                ->where('sp.status', 'active')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();

            // Format data
            $vendorName = $data['vendor_name'] ?? 'Unknown Vendor';
            $tanggalMulai = $data['tanggal_mulai'] ? date('d M Y', strtotime($data['tanggal_mulai'])) : '-';
            $tanggalSelesai = $data['tanggal_selesai'] ? date('d M Y', strtotime($data['tanggal_selesai'])) : '-';
            $jumlahMasuk = $data['jumlah_leads_masuk'] ?? 0;
            $jumlahClosing = $data['jumlah_leads_closing'] ?? 0;

            // Siapkan data notifikasi berdasarkan action type
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            switch ($actionType) {
                case 'create':
                    $title = '📊 Lead Baru Ditambahkan';
                    $message = "Lead baru telah ditambahkan oleh {$adminName} untuk vendor {$vendorName}";
                    break;

                case 'update':
                    $title = '✏️ Lead Diperbarui';
                    $message = "Lead untuk vendor {$vendorName} telah diperbarui oleh {$adminName}";
                    break;

                case 'delete':
                    $title = '🗑️ Lead Dihapus';
                    $message = "Lead untuk vendor {$vendorName} telah dihapus oleh {$adminName}";
                    break;

                default:
                    return false;
            }

            // Tambahkan detail lead
            $message .= "\n\nDetail Lead:";
            $message .= "\n• Vendor: {$vendorName}";
            $message .= "\n• Periode: {$tanggalMulai} - {$tanggalSelesai}";
            $message .= "\n• Leads Masuk: {$jumlahMasuk}";
            $message .= "\n• Leads Closing: {$jumlahClosing}";
            $message .= "\n• Diproses oleh: {$adminName}";

            // Notifikasi untuk VENDOR dengan type 'system'
            if ($vendorUserId) {
                $notifications[] = [
                    'user_id' => $vendorUserId,
                    'vendor_id' => $vendorId,
                    'type' => 'system',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Notifikasi untuk semua ADMIN KECUALI admin yang sedang login dengan type 'system'
            foreach ($adminUsers as $admin) {
                if ($admin['user_id'] == $adminUserId) {
                    continue;
                }
                
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'system',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Notifikasi untuk semua SEO users dengan type 'system'
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => $seo['user_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'system',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Insert semua notifikasi
            if (!empty($notifications)) {
                $this->notificationsModel->insertBatch($notifications);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            log_message('error', "Error creating lead notification: " . $e->getMessage());
            return false;
        }
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
                'created_at'  => date('Y-m-d H:i:s'),
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