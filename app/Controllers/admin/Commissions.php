<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Commissions extends BaseController
{
    protected $commissionModel;
    protected $vendorModel;
    protected $activityLogsModel;

    public function __construct()
    {
        $this->commissionModel = new CommissionsModel();
        $this->vendorModel = new VendorProfilesModel();
        $this->activityLogsModel = new ActivityLogsModel();
    }

    public function index()
    {
        // Filter by vendor jika ada
        $vendorId = $this->request->getGet('vendor_id');
        $status = $this->request->getGet('status');

        // Build query
        $query = $this->commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name, vendor_profiles.owner_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left');

        if ($vendorId && $vendorId !== 'all') {
            $query->where('commissions.vendor_id', $vendorId);
        }

        if ($status && $status !== 'all') {
            $query->where('commissions.status', $status);
        }

        $commissions = $query->orderBy('commissions.created_at', 'DESC')
            ->paginate(20);

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $this->vendorModel
            ->select('id, business_name')
            ->where('status', 'verified')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        return view('admin/commissions/index', [
            'title'       => 'Manajemen Komisi Vendor',
            'activeMenu'  => 'commissions',
            'commissions' => $commissions,
            'pager'       => $this->commissionModel->pager,
            'vendors'     => $vendors,
            'vendor_id'   => $vendorId,
            'status'      => $status
        ]);
    }

    public function verify($id)
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        // PERBAIKAN: Akses array dengan benar
        $commissionStatus = $commission['status'] ?? '';

        // Validasi status sebelum verifikasi
        if ($commissionStatus === 'paid') {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Komisi ini sudah dalam status Paid dan tidak dapat diverifikasi ulang.'
            ]);
        }

        try {
            // PERBAIKAN: Hanya update kolom yang ada di tabel
            $updateData = [
                'status' => 'paid'
            ];

            // Tambahkan paid_at hanya jika kolomnya ada di allowedFields
            if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                $updateData['paid_at'] = date('Y-m-d H:i:s');
            }

            // Update status menjadi 'paid'
            $this->commissionModel->update($id, $updateData);

            // Log activity
            $vendorId = $commission['vendor_id'] ?? 0;
            $this->logActivity(
                $vendorId,
                'commissions',
                'verify',
                "Komisi #{$id} telah diverifikasi dan ditandai sebagai dibayar."
            );

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Komisi berhasil diverifikasi dan ditandai sebagai dibayar.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error verifying commission: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id)
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $commission = $this->commissionModel->find($id);

        if (!$commission) {
            return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
        }

        try {
            // PERBAIKAN: Akses vendor_id sebagai array
            $vendorId = $commission['vendor_id'] ?? 0;
            
            // Hapus komisi
            $this->commissionModel->delete($id);

            // Log activity
            $this->logActivity(
                $vendorId,
                'commissions',
                'delete',
                "Komisi #{$id} telah dihapus."
            );

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Komisi berhasil dihapus.'
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error deleting commission: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkAction()
    {
        // Validasi AJAX request
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }

        $action = $this->request->getPost('action');
        $commissionIds = $this->request->getPost('commission_ids');

        if (empty($commissionIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada komisi yang dipilih.']);
        }

        // Validasi action
        if (!in_array($action, ['verify', 'delete'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Aksi tidak valid.']);
        }

        // Pastikan commission_ids adalah array
        if (!is_array($commissionIds)) {
            $commissionIds = [$commissionIds];
        }

        $successCount = 0;
        $errorCount = 0;
        $errorMessages = [];
        $successIds = [];
        $errorIds = [];

        foreach ($commissionIds as $id) {
            try {
                $commission = $this->commissionModel->find($id);
                if (!$commission) {
                    $errorCount++;
                    $errorMessages[] = "Komisi #{$id} tidak ditemukan";
                    $errorIds[] = $id;
                    continue;
                }

                switch ($action) {
                    case 'verify':
                        // PERBAIKAN: Akses status sebagai array
                        $commissionStatus = $commission['status'] ?? '';
                        
                        // Validasi status sebelum verifikasi
                        if ($commissionStatus === 'paid') {
                            $errorCount++;
                            $errorMessages[] = "Komisi #{$id} sudah dalam status Paid dan tidak dapat diverifikasi ulang";
                            $errorIds[] = $id;
                            continue 2; // Skip ke komisi berikutnya
                        }

                        // PERBAIKAN: Hanya update kolom yang ada di tabel
                        $updateData = [
                            'status' => 'paid'
                        ];

                        // Tambahkan paid_at hanya jika kolomnya ada di allowedFields
                        if (in_array('paid_at', $this->commissionModel->allowedFields)) {
                            $updateData['paid_at'] = date('Y-m-d H:i:s');
                        }

                        // Update status menjadi 'paid'
                        $this->commissionModel->update($id, $updateData);
                        break;

                    case 'delete':
                        $this->commissionModel->delete($id);
                        break;

                    default:
                        $errorCount++;
                        $errorMessages[] = "Aksi '{$action}' tidak valid untuk komisi #{$id}";
                        $errorIds[] = $id;
                        continue 2;
                }

                // PERBAIKAN: Akses vendor_id sebagai array
                $vendorId = $commission['vendor_id'] ?? 0;
                
                // Log activity
                $this->logActivity(
                    $vendorId,
                    'commissions',
                    $action,
                    "Komisi #{$id} telah diproses dengan aksi: {$action}"
                );

                $successCount++;
                $successIds[] = $id;

            } catch (\Exception $e) {
                log_message('error', "Error processing commission #{$id}: " . $e->getMessage());
                $errorCount++;
                $errorMessages[] = "Komisi #{$id}: " . $e->getMessage();
                $errorIds[] = $id;
            }
        }

        // Response yang lebih detail
        $response = [
            'success' => $successCount > 0,
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'success_ids' => $successIds,
            'error_ids' => $errorIds,
            'errors' => $errorMessages
        ];

        if ($successCount > 0 && $errorCount === 0) {
            // Semua berhasil
            $response['message'] = "Berhasil memproses {$successCount} komisi.";
        } elseif ($successCount > 0 && $errorCount > 0) {
            // Sebagian berhasil, sebagian gagal
            $response['message'] = "Berhasil memproses {$successCount} komisi. {$errorCount} komisi gagal diproses.";
        } else {
            // Semua gagal
            $response['message'] = "Gagal memproses komisi yang dipilih.";
        }

        return $this->response->setJSON($response);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        try {
            $data = [
                'user_id'     => session('user_id') ?? 1,
                'vendor_id'   => (int)($vendorId ?? 0),
                'module'      => $module,
                'action'      => $action,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress() ?? '127.0.0.1',
                'user_agent'  => (string)($this->request->getUserAgent() ?? 'Unknown'),
                'created_at'  => date('Y-m-d H:i:s'),
            ];

            $this->activityLogsModel->insert($data);
            
        } catch (\Exception $e) {
            log_message('error', 'Failed to log activity: ' . $e->getMessage());
        }
    }
}