<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;
use App\Models\NotificationsModel;
use App\Models\UserModel;
use App\Models\SeoProfilesModel;

class Commissions extends BaseController
{
    private $vendorProfile;
    private $vendorId;
    private $isVerified;
    private $commissionModel;
    private $notificationsModel;
    private $userModel;
    private $seoProfilesModel;

    public function __construct()
    {
        $this->commissionModel = new CommissionsModel();
        $this->notificationsModel = new NotificationsModel();
        $this->userModel = new UserModel();
        $this->seoProfilesModel = new SeoProfilesModel();
    }

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int)$user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? 0;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool)$this->vendorProfile;
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

    private function withVendorData(array $data = []): array
    {
        return array_merge($data, [
            'vp' => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function logActivity(string $action, ?string $description = null): void
    {
        $user = service('auth')->user();
        (new ActivityLogsModel())->insert([
            'user_id'    => $user->id,
            'vendor_id'  => $this->vendorId,
            'module'     => 'commission',
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent(),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Validasi apakah periode sudah ada
     */
    private function isPeriodExists(string $periodStart, string $periodEnd, ?int $excludeId = null): bool
    {
        $query = $this->commissionModel
            ->where('vendor_id', $this->vendorId)
            ->where('period_start', $periodStart)
            ->where('period_end', $periodEnd);

        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }

        return $query->countAllResults() > 0;
    }

    /**
     * Buat notifikasi untuk admin dan SEO terkait komisi
     */
    private function createCommissionNotification($commissionId, $actionType, $commissionData)
    {
        try {
            $db = \Config\Database::connect();
            
            // Dapatkan semua admin users yang aktif
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1) // Pastikan user aktif
                ->get()
                ->getResultArray();
            
            log_message('info', "Found " . count($adminUsers) . " active admin users for notification");

            // Dapatkan semua SEO users yang aktif
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
            
            log_message('info', "Found " . count($seoUsers) . " active SEO users for notification");
            if (empty($seoUsers)) {
                log_message('warning', "No active SEO users found. Check if group name 'seo' is correct and users are active.");
            }

            // Format jumlah komisi
            $amount = number_format($commissionData['amount'] ?? 0, 0, ',', '.');
            
            // Format periode
            $periodStart = $commissionData['period_start'] ? date('d M Y', strtotime($commissionData['period_start'])) : '-';
            $periodEnd = $commissionData['period_end'] ? date('d M Y', strtotime($commissionData['period_end'])) : '-';
            $period = "{$periodStart} - {$periodEnd}";

            // Siapkan data notifikasi berdasarkan action type
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            switch ($actionType) {
                case 'create':
                    $title = 'Komisi Baru Diterima';
                    $message = "📝 Vendor {$this->vendorProfile['business_name']} mengirim komisi baru periode {$period} sebesar Rp {$amount}";
                    break;

                case 'update':
                    $title = 'Komisi Diperbarui';
                    $message = "✏️ Vendor {$this->vendorProfile['business_name']} memperbarui komisi periode {$period} sebesar Rp {$amount}";
                    break;

                default:
                    return false;
            }

            // Tambahkan detail komisi
            $message .= "\n\nDetail Komisi:";
            $message .= "\n• Vendor: {$this->vendorProfile['business_name']}";
            $message .= "\n• Periode: {$period}";
            $message .= "\n• Jumlah: Rp {$amount}";
            $message .= "\n• Status: " . ucfirst($commissionData['status'] ?? 'unpaid');

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
            log_message('info', "Added notifications for " . count($adminUsers) . " admin users");

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
            log_message('info', "Added notifications for " . count($seoUsers) . " SEO users");

            // Insert semua notifikasi
            if (!empty($notifications)) {
                $this->notificationsModel->insertBatch($notifications);
                
                // Log untuk debugging
                log_message('info', "Created commission {$actionType} notifications for commission {$commissionId}: " . count($notifications) . " notifications sent");
                
                return true;
            }

            log_message('warning', "No notifications were created for commission {$commissionId}");
            return false;

        } catch (\Exception $e) {
            log_message('error', "Error creating commission notification: " . $e->getMessage());
            log_message('error', $e->getTraceAsString());
            return false;
        }
    }

    public function index()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        // PERBAIKAN: Ubah pengurutan data agar yang terbaru tampil di bawah
        $list = $this->commissionModel
            ->where('vendor_id', $this->vendorId)
            ->orderBy('period_start', 'ASC') // PERBAIKAN: Ubah dari DESC ke ASC
            ->findAll();

        $this->logActivity('view', 'Melihat daftar komisi');

        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Komisi',
            'content_view' => 'vendoruser/commissions/index',
            'content_data' => [
                'page'  => 'Komisi',
                'items' => $list,
            ],
        ]));
    }

    public function create()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $this->logActivity('create_form', 'Membuka form tambah komisi');

        return view('vendoruser/commissions/create', $this->withVendorData([
            'page' => 'Tambah Komisi',
        ]));
    }

    public function store()
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $rules = [
            'period_start' => 'required|valid_date',
            'period_end'   => 'required|valid_date',
            'earning'      => 'required|decimal',
            'amount'       => 'required|decimal',
            'proof'        => 'permit_empty|max_size[proof,10240]|ext_in[proof,pdf,jpg,jpeg,png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodStart = $this->request->getPost('period_start');
        $periodEnd = $this->request->getPost('period_end');

        // VALIDASI PERIODE SUDAH ADA
        if ($this->isPeriodExists($periodStart, $periodEnd)) {
            return redirect()->back()->withInput()->with('error', 'Komisi untuk periode ' . date('d M Y', strtotime($periodStart)) . ' - ' . date('d M Y', strtotime($periodEnd)) . ' sudah ada. Silakan gunakan periode yang berbeda.');
        }

        $data = [
            'vendor_id'    => $this->vendorId,
            'period_start' => $periodStart,
            'period_end'   => $periodEnd,
            'earning'      => $this->request->getPost('earning'),
            'amount'       => $this->request->getPost('amount'),
            'status'       => 'unpaid',
            'created_at'   => date('Y-m-d H:i:s'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($file = $this->request->getFile('proof')) {
            if ($file->isValid() && !$file->hasMoved()) {
                $filename = $file->getRandomName();
                $file->move(FCPATH.'uploads/commissions/', $filename);
                $data['proof'] = $filename;
            }
        }

        $this->commissionModel->insert($data);
        $commissionId = $this->commissionModel->insertID();
        
        // Kirim notifikasi ke admin dan SEO
        $this->createCommissionNotification($commissionId, 'create', $data);
        
        $this->logActivity('create', "Mengirim komisi periode {$data['period_start']} - {$data['period_end']}");

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', 'Komisi berhasil dikirim.');
    }

    public function edit($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $item = $this->commissionModel
            ->where(['id' => $id, 'vendor_id' => $this->vendorId])
            ->first();

        if (! $item) {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi tidak ditemukan.');
        }

        // Cek jika status sudah paid
        if ($item['status'] === 'paid') {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi yang sudah dibayar tidak dapat diedit.');
        }

        $this->logActivity('edit_form', "Membuka form edit komisi ID {$id}");

        return view('vendoruser/commissions/edit', $this->withVendorData([
            'page' => 'Edit Komisi',
            'item' => $item,
        ]));
    }

    public function update($id)
    {
        if (! $this->checkVerifiedAccess()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $item = $this->commissionModel
            ->where(['id' => $id, 'vendor_id' => $this->vendorId])
            ->first();

        if (! $item) {
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        // Cek jika status sudah paid
        if ($item['status'] === 'paid') {
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi yang sudah dibayar tidak dapat diedit.');
        }

        $rules = [
            'period_start' => 'required|valid_date',
            'period_end'   => 'required|valid_date',
            'earning'      => 'required|decimal',
            'amount'       => 'required|decimal',
            'proof'        => 'permit_empty|max_size[proof,10240]|ext_in[proof,pdf,jpg,jpeg,png]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $periodStart = $this->request->getPost('period_start');
        $periodEnd = $this->request->getPost('period_end');

        // VALIDASI PERIODE SUDAH ADA (kecuali untuk data yang sedang diedit)
        if ($this->isPeriodExists($periodStart, $periodEnd, $id)) {
            return redirect()->back()->withInput()->with('error', 'Komisi untuk periode ' . date('d M Y', strtotime($periodStart)) . ' - ' . date('d M Y', strtotime($periodEnd)) . ' sudah ada. Silakan gunakan periode yang berbeda.');
        }

        $updateData = [
            'period_start' => $periodStart,
            'period_end'   => $periodEnd,
            'earning'      => $this->request->getPost('earning'),
            'amount'       => $this->request->getPost('amount'),
            'updated_at'   => date('Y-m-d H:i:s'),
        ];

        if ($file = $this->request->getFile('proof')) {
            if ($file->isValid() && !$file->hasMoved()) {
                // Hapus file proof lama jika ada
                if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
                    unlink(FCPATH.'uploads/commissions/'.$item['proof']);
                }
                $filename = $file->getRandomName();
                $file->move(FCPATH.'uploads/commissions/', $filename);
                $updateData['proof'] = $filename;
            }
        }

        $this->commissionModel->update($id, $updateData);
        
        // Ambil data terbaru untuk notifikasi
        $updatedItem = $this->commissionModel->find($id);
        
        // Kirim notifikasi ke admin dan SEO
        $this->createCommissionNotification($id, 'update', $updatedItem);
        
        $this->logActivity('update', "Memperbarui komisi ID {$id}");

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', 'Komisi berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (! $this->checkVerifiedAccess()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $item = $this->commissionModel
            ->where(['id' => (int)$id, 'vendor_id' => $this->vendorId])
            ->first();

        if (! $item) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Komisi tidak ditemukan.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi tidak ditemukan.');
        }

        // Cek jika status sudah paid
        if ($item['status'] === 'paid') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Komisi yang sudah dibayar tidak dapat dihapus.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/commissions'))
                ->with('error', 'Komisi yang sudah dibayar tidak dapat dihapus.');
        }

        // Hapus file proof jika ada
        if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
            @unlink(FCPATH.'uploads/commissions/'.$item['proof']);
        }

        $this->commissionModel->delete((int)$id);
        $this->logActivity('delete', "Menghapus komisi ID {$id}");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => 'Komisi berhasil dihapus.',
                'csrfHash' => csrf_hash(),
            ]);
        }

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', 'Komisi berhasil dihapus.');
    }

    public function deleteMultiple()
    {
        if (! $this->checkVerifiedAccess()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Akun vendor Anda belum diverifikasi. Silakan lengkapi profil dan tunggu verifikasi dari admin.');
        }

        $ids = $this->request->isAJAX()
            ? ($this->request->getJSON(true)['ids'] ?? [])
            : ($this->request->getPost('ids') ?? []);

        $ids = array_values(array_unique(array_map('intval', (array)$ids)));

        if (empty($ids)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Tidak ada komisi yang dipilih.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Tidak ada komisi yang dipilih.');
        }

        $items = $this->commissionModel
            ->whereIn('id', $ids)
            ->where('vendor_id', $this->vendorId)
            ->findAll();

        if (empty($items)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Data tidak ditemukan atau bukan milik Anda.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan atau bukan milik Anda.');
        }

        // Filter hanya yang status bukan paid
        $editableItems = array_filter($items, function($item) {
            return $item['status'] !== 'paid';
        });

        if (empty($editableItems)) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'   => 'error',
                    'message'  => 'Komisi yang sudah dibayar tidak dapat dihapus.',
                    'csrfHash' => csrf_hash(),
                ]);
            }
            return redirect()->back()->with('error', 'Komisi yang sudah dibayar tidak dapat dihapus.');
        }

        $deleted = 0;
        foreach ($editableItems as $item) {
            // Hapus file proof jika ada
            if (!empty($item['proof']) && file_exists(FCPATH.'uploads/commissions/'.$item['proof'])) {
                @unlink(FCPATH.'uploads/commissions/'.$item['proof']);
            }
            $this->commissionModel->delete((int)$item['id']);
            $this->logActivity('delete', "Menghapus komisi ID {$item['id']}");
            $deleted++;
        }

        $msg = "Berhasil menghapus {$deleted} data.";

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'   => 'success',
                'message'  => $msg,
                'csrfHash' => csrf_hash(),
            ]);
        }

        return redirect()->to(site_url('vendoruser/commissions'))
            ->with('success', $msg);
    }
}