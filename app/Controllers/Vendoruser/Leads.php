<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use App\Models\SeoProfilesModel;

class Leads extends BaseController
{
    private $vendorProfile;
    private $isVerified;
    private $vendorId;
    private $activityLogsModel;
    private $notificationsModel;
    private $userModel;
    private $seoProfilesModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
        $this->seoProfilesModel = new SeoProfilesModel();
    }

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? null;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorProfile;
    }

    private function checkVerifiedAccess(): bool
    {
        if (! $this->initVendor()) {
            return false;
        }
        
        if (! $this->isVerified) {
            return false;
        }
        
        return true;
    }

    private function withVendorData(array $data = [])
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function logActivity(string $action, ?string $description = null, array $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            $data = [
                'user_id'     => $user->id,
                'vendor_id'   => $this->vendorId,
                'module'      => 'leads',
                'action'      => $action,
                'status'      => 'success',
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }

            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in Leads: ' . $e->getMessage());
        }
    }

    public function index()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $m = new LeadsModel();

        $start = (string) $this->request->getGet('start_date');
        $end   = (string) $this->request->getGet('end_date');

        $m->where('vendor_id', $this->vendorId);

        if ($start !== '' && $end !== '') {
            $m->where('tanggal_mulai >=', $start)
            ->where('tanggal_selesai <=', $end);
        }

        $m->orderBy('tanggal_mulai', 'DESC');

        $this->logActivity('view', 'Melihat daftar leads', [
            'start_date' => $start,
            'end_date' => $end
        ]);

        // ⬇️ gunakan layout master
        return view('vendoruser/layouts/vendor_master', [
            'title'        => 'Laporan Leads',
            // data yang dibutuhkan layout (header/sidebar)
            'vp'           => $this->vendorProfile,
            'isVerified'   => $this->isVerified,

            // view konten utama & datanya
            'content_view' => 'vendoruser/leads/index',
            'content_data' => [
                'page'       => 'Laporan Leads',
                'leads'      => $m->findAll(),
                'start_date' => $start,
                'end_date'   => $end,
            ],
        ]);
    }

    public function create()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $this->logActivity('create_form', 'Membuka form tambah leads');

        return view('vendoruser/leads/create', $this->withVendorData([
            'page' => 'Tambah Laporan Leads',
        ]));
    }

    public function show($id)
    {
        if (! $this->checkVerifiedAccess()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>'Akun vendor Anda belum diverifikasi.']);
            }
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>'Data tidak ditemukan']);
            }
            return redirect()->to(site_url('vendoruser/leads'))->with('error','Data tidak ditemukan');
        }

        $this->logActivity('view_detail', "Melihat detail leads ID {$id}", [
            'lead_id' => $id
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'success','lead'=>$lead]);
        }

        return view('vendoruser/leads/show', $this->withVendorData(['lead'=>$lead]));
    }

    public function edit($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $lead = (new LeadsModel())->where([
            'id'        => (int) $id,
            'vendor_id' => $this->vendorId,
        ])->first();

        if (! $lead) {
            return redirect()->to(site_url('vendoruser/leads'))
                ->with('error', 'Laporan tidak ditemukan.');
        }

        $this->logActivity('edit_form', "Membuka form edit leads ID {$id}", [
            'lead_id' => $id
        ]);

        return view('vendoruser/leads/edit', $this->withVendorData([
            'page' => 'Edit Laporan Leads',
            'lead' => $lead,
        ]));
    }

    public function store()
    {
        if (! $this->checkVerifiedAccess()) {
            return $this->respondAjax('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $rules = [
            'tanggal_mulai'       => 'required|valid_date[Y-m-d]',
            'tanggal_selesai'     => 'required|valid_date[Y-m-d]',
            'jumlah_leads_masuk'  => 'required|integer',
            'jumlah_leads_closing'=> 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
        }

        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        
        // Validasi bahwa tanggal mulai tidak boleh setelah tanggal selesai
        if (strtotime($tanggalMulai) > strtotime($tanggalSelesai)) {
            return $this->respondAjax('error', 'Tanggal mulai tidak boleh setelah tanggal selesai');
        }

        $data = [
            'vendor_id'           => $this->vendorId,
            'tanggal_mulai'       => $tanggalMulai,
            'tanggal_selesai'     => $tanggalSelesai,
            'jumlah_leads_masuk'  => (int) $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_closing'=> (int) $this->request->getPost('jumlah_leads_closing'),
            'reported_by_vendor'  => $this->vendorId,
            'assigned_at'         => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $leadsModel = new LeadsModel();
        $result = $leadsModel->insert($data);
        $insertId = $leadsModel->getInsertID();

        if ($result) {
            // 🔔 KIRIM NOTIFIKASI KE ADMIN & SEO
            $this->sendLeadsReportNotification($data, 'create', $insertId);

            $this->logActivity('create', "Menambahkan laporan leads periode {$tanggalMulai} - {$tanggalSelesai}", [
                'lead_id' => $insertId,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'jumlah_leads_masuk' => $data['jumlah_leads_masuk'],
                'jumlah_leads_closing' => $data['jumlah_leads_closing']
            ]);
            return $this->respondAjax('success', 'Laporan leads berhasil ditambahkan.');
        } else {
            return $this->respondAjax('error', 'Gagal menambahkan laporan leads.');
        }
    }

    public function update($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return $this->respondAjax('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            return $this->respondAjax('error', 'Laporan tidak ditemukan.');
        }

        $rules = [
            'tanggal_mulai'       => 'required|valid_date[Y-m-d]',
            'tanggal_selesai'     => 'required|valid_date[Y-m-d]',
            'jumlah_leads_masuk'  => 'required|integer',
            'jumlah_leads_closing'=> 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
        }

        $tanggalMulai = $this->request->getPost('tanggal_mulai');
        $tanggalSelesai = $this->request->getPost('tanggal_selesai');
        
        // Validasi bahwa tanggal mulai tidak boleh setelah tanggal selesai
        if (strtotime($tanggalMulai) > strtotime($tanggalSelesai)) {
            return $this->respondAjax('error', 'Tanggal mulai tidak boleh setelah tanggal selesai');
        }

        $updateData = [
            'tanggal_mulai'       => $tanggalMulai,
            'tanggal_selesai'     => $tanggalSelesai,
            'jumlah_leads_masuk'  => (int) $this->request->getPost('jumlah_leads_masuk'),
            'jumlah_leads_closing'=> (int) $this->request->getPost('jumlah_leads_closing'),
            'updated_at'          => date('Y-m-d H:i:s'),
        ];

        $result = $m->update((int) $id, $updateData);

        if ($result) {
            // 🔔 KIRIM NOTIFIKASI KE ADMIN & SEO
            $this->sendLeadsReportNotification(array_merge($lead, $updateData), 'update', $id);

            $this->logActivity('update', "Memperbarui laporan leads ID {$id}", [
                'lead_id' => $id,
                'tanggal_mulai' => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'jumlah_leads_masuk' => $updateData['jumlah_leads_masuk'],
                'jumlah_leads_closing' => $updateData['jumlah_leads_closing']
            ]);
            return $this->respondAjax('success', 'Laporan leads berhasil diperbarui.');
        } else {
            return $this->respondAjax('error', 'Gagal memperbarui laporan leads.');
        }
    }

    public function delete($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return $this->respondAjax('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            return $this->respondAjax('error', 'Laporan tidak ditemukan.');
        }

        $result = $m->delete((int) $id);

        if ($result) {
            // 🔔 KIRIM NOTIFIKASI KE ADMIN & SEO
            $this->sendLeadsReportNotification($lead, 'delete', $id);

            $this->logActivity('delete', "Menghapus laporan leads ID {$id}", [
                'lead_id' => $id,
                'tanggal_mulai' => $lead['tanggal_mulai'],
                'tanggal_selesai' => $lead['tanggal_selesai']
            ]);
            return $this->respondAjax('success', 'Laporan leads berhasil dihapus.');
        } else {
            return $this->respondAjax('error', 'Gagal menghapus laporan leads.');
        }
    }

    private function respondAjax(string $status, string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => $status,
                'message' => $message
            ]);
        }

        $type = $status === 'success' ? 'success' : 'error';
        return redirect()->back()->with($type, $message);
    }

    public function deleteMultiple()
    {
        if (! $this->checkVerifiedAccess()) {
            return $this->respondAjax('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $ids = $this->request->getJSON(true)['ids'] ?? [];

        if (empty($ids)) {
            return $this->respondAjax('error', 'Tidak ada data terpilih.');
        }

        $m = new LeadsModel();
        $leadsToDelete = $m->where('vendor_id', $this->vendorId)
                      ->whereIn('id', $ids)
                      ->findAll();

        $deleted = $m->where('vendor_id', $this->vendorId)
                ->whereIn('id', $ids)
                ->delete();

        if ($deleted) {
            // 🔔 KIRIM NOTIFIKASI UNTUK SETIAP LEADS YANG DIHAPUS
            foreach ($leadsToDelete as $lead) {
                $this->sendLeadsReportNotification($lead, 'delete', $lead['id']);
            }

            $this->logActivity('delete_multiple', "Menghapus multiple laporan leads", [
                'lead_ids' => $ids,
                'count' => count($ids)
            ]);
            return $this->respondAjax('success', 'Data terpilih berhasil dihapus.');
        }

        return $this->respondAjax('error', 'Gagal menghapus data terpilih.');
    }

    /**
     * Kirim notifikasi laporan leads ke Admin & SEO
     */
    private function sendLeadsReportNotification($leadsData, $action = 'create', $leadId = null)
    {
        try {
            $db = \Config\Database::connect();
            
            $vendorName = $this->vendorProfile['business_name'] ?? 'Vendor Tidak Dikenal';
            $ownerName = $this->vendorProfile['owner_name'] ?? 'Tidak Dikenal';
            
            // Format periode
            $period = date('d/m/Y', strtotime($leadsData['tanggal_mulai'])) . ' - ' . date('d/m/Y', strtotime($leadsData['tanggal_selesai']));
            
            // Tentukan action text
            $actionText = '';
            $emoji = '';
            switch ($action) {
                case 'create':
                    $actionText = 'mengirim laporan leads baru';
                    $emoji = '📝';
                    break;
                case 'update':
                    $actionText = 'memperbarui laporan leads';
                    $emoji = '✏️';
                    break;
                case 'delete':
                    $actionText = 'menghapus laporan leads';
                    $emoji = '🗑️';
                    break;
                default:
                    $actionText = 'melakukan aksi pada laporan leads';
                    $emoji = '📋';
            }

            $title = 'Laporan Leads Vendor';
            $message = "{$emoji} Vendor {$vendorName} (Pemilik: {$ownerName}) {$actionText} untuk periode {$period} dengan {$leadsData['jumlah_leads_masuk']} leads masuk dan {$leadsData['jumlah_leads_closing']} leads closing.";

            // Tambahkan detail laporan
            $message .= "\n\nDetail Laporan:";
            $message .= "\n• Vendor: {$vendorName}";
            $message .= "\n• Periode: {$period}";
            $message .= "\n• Leads Masuk: {$leadsData['jumlah_leads_masuk']}";
            $message .= "\n• Leads Closing: {$leadsData['jumlah_leads_closing']}";
            $message .= "\n• Status: " . ucfirst($action);

            // Siapkan data notifikasi
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            // 1. Dapatkan semua admin users yang aktif
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1) // Pastikan user aktif
                ->get()
                ->getResultArray();
            
            log_message('info', "Found " . count($adminUsers) . " active admin users for leads notification");

            // 2. Dapatkan semua SEO users yang aktif
            // Metode 1: Melalui auth_groups_users (jika SEO users memiliki grup 'seo')
            $seoUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'seo')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();
            
            // Jika tidak ada SEO users melalui auth_groups, coba metode 2
            if (empty($seoUsers)) {
                log_message('info', "No SEO users found through auth_groups, trying seo_profiles table");
                
                // Metode 2: Melalui seo_profiles table
                $seoProfiles = $this->seoProfilesModel
                    ->select('user_id')
                    ->where('status', 'active') // Asumsi status aktif
                    ->findAll();
                
                // Konversi ke format yang sama dengan metode 1
                $seoUsers = array_map(function($profile) {
                    return ['user_id' => $profile['user_id']];
                }, $seoProfiles);
                
                // Filter hanya user yang aktif di tabel users
                $activeSeoUserIds = $db->table('users')
                    ->select('id')
                    ->whereIn('id', array_column($seoUsers, 'user_id'))
                    ->where('active', 1)
                    ->get()
                    ->getResultArray();
                
                // Filter seoUsers untuk hanya menyertakan user yang aktif
                $activeSeoUserIds = array_column($activeSeoUserIds, 'id');
                $seoUsers = array_filter($seoUsers, function($user) use ($activeSeoUserIds) {
                    return in_array($user['user_id'], $activeSeoUserIds);
                });
                
                // Re-index array
                $seoUsers = array_values($seoUsers);
            }
            
            log_message('info', "Found " . count($seoUsers) . " active SEO users for leads notification");
            if (empty($seoUsers)) {
                log_message('warning', "No active SEO users found. Check if group name 'seo' is correct and users are active.");
            }

            // PERBAIKAN: Notifikasi untuk semua ADMIN dengan type system
            foreach ($adminUsers as $admin) {
                $notifications[] = [
                    'user_id' => $admin['user_id'],
                    'vendor_id' => $this->vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // PERBAIKAN: Notifikasi untuk semua SEO dengan type system
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => $seo['user_id'],
                    'vendor_id' => $this->vendorId,
                    'type' => 'system', // PERUBAHAN: type menjadi 'system'
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
                
                // Log untuk debugging
                log_message('info', "Created leads {$action} notifications: " . count($notifications) . " notifications sent");
                
                return true;
            }

            log_message('warning', "No notifications were created for leads report");
            return false;

        } catch (\Throwable $e) {
            log_message('error', 'Gagal mengirim notifikasi laporan leads: ' . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            return false;
        }
    }
}