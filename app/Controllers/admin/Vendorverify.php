<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;

class Vendorverify extends BaseController
{
    protected $vendorProfiles;

    public function __construct()
    {
        $this->vendorProfiles = new VendorProfilesModel();
        helper('session');
    }

    /**
     * Ambil user id saat ini (dari session atau service auth jika tersedia)
     */
    private function getCurrentUserId()
    {
        $id = session()->get('id');
        if ($id) {
            return $id;
        }

        // kalau pakai service('auth')
        try {
            $user = service('auth')->user();
            return $user->id ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Approve vendor.
     * $id bisa dikirim lewat URL (/approve/6) atau lewat POST field 'id' (AJAX).
     */
    public function approve($id = null)
    {
        // Pastikan request AJAX (sesuai JS fetch Anda yang mengirim X-Requested-With)
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Forbidden: Invalid request',
                ]);
        }

        // Ambil id: prioritas URL param, lalu fallback ke POST['id']
        $posted = $this->request->getPost('id');
        $id = $id ?? $posted;
        $id = (int) $id;

        if ($id <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID vendor tidak valid.',
            ]);
        }

        // Cek vendor ada
        $vendor = $this->vendorProfiles->find($id);
        if (! $vendor) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Vendor tidak ditemukan.',
            ]);
        }

        $status = strtolower($vendor['status'] ?? '');
        if ($status === 'verified') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Vendor sudah disetujui sebelumnya.',
            ]);
        }

        $actionBy = $this->getCurrentUserId();

        try {
            $ok = $this->vendorProfiles->update($id, [
                'status'      => 'verified',
                'approved_at' => date('Y-m-d H:i:s'),
                'action_by'   => $actionBy,
                'updated_at'  => date('Y-m-d H:i:s'),
            ]);

            if (! $ok) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menyetujui vendor. Silakan coba lagi.',
                ]);
            }

            // Optional: insert activity log jika model ada
            if (class_exists(\App\Models\ActivityLogsModel::class)) {
                try {
                    (new \App\Models\ActivityLogsModel())->insert([
                        'user_id'    => $actionBy,
                        'vendor_id'  => $id,
                        'module'     => 'vendor_request',
                        'action'     => 'approve',
                        'description'=> "Menyetujui vendor ID {$id}",
                        'ip_address' => $this->request->getIPAddress(),
                        'user_agent' => $this->request->getUserAgent(),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $e) {
                    // jangan ganggu respons utama kalau log gagal
                    log_message('error', 'ActivityLogs insert failed: ' . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Vendor berhasil disetujui',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Vendor approve error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Terjadi kesalahan server.',
                ]);
        }
    }

    /**
     * Reject vendor.
     * $id bisa dikirim lewat URL (/reject/6) atau lewat POST field 'id' (AJAX).
     */
    public function reject($id = null)
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(403)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Forbidden: Invalid request',
                ]);
        }

        $posted = $this->request->getPost('id');
        $id = $id ?? $posted;
        $id = (int) $id;

        if ($id <= 0) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'ID vendor tidak valid.',
            ]);
        }

        $reason = trim((string) ($this->request->getPost('reason') ?? 'Pengajuan ditolak admin'));

        $vendor = $this->vendorProfiles->find($id);
        if (! $vendor) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Vendor tidak ditemukan.',
            ]);
        }

        $status = strtolower($vendor['status'] ?? '');
        if ($status === 'rejected') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Vendor sudah ditolak sebelumnya.',
            ]);
        }
        if ($status === 'verified') {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Vendor sudah disetujui. Tidak bisa ditolak.',
            ]);
        }

        $actionBy = $this->getCurrentUserId();

        try {
            $ok = $this->vendorProfiles->update($id, [
                'status'           => 'rejected',
                'rejection_reason' => $reason,
                'approved_at'      => date('Y-m-d H:i:s'),
                'action_by'        => $actionBy,
                'updated_at'       => date('Y-m-d H:i:s'),
            ]);

            if (! $ok) {
                return $this->response->setJSON([
                    'status'  => 'error',
                    'message' => 'Gagal menolak vendor. Silakan coba lagi.',
                ]);
            }

            if (class_exists(\App\Models\ActivityLogsModel::class)) {
                try {
                    (new \App\Models\ActivityLogsModel())->insert([
                        'user_id'    => $actionBy,
                        'vendor_id'  => $id,
                        'module'     => 'vendor_request',
                        'action'     => 'reject',
                        'description'=> "Menolak vendor ID {$id} dengan alasan: {$reason}",
                        'ip_address' => $this->request->getIPAddress(),
                        'user_agent' => $this->request->getUserAgent(),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                } catch (\Throwable $e) {
                    log_message('error', 'ActivityLogs insert failed: ' . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Vendor berhasil ditolak',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Vendor reject error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Terjadi kesalahan server.',
                ]);
        }
    }
}
