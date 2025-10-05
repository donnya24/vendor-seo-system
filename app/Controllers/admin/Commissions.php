<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Commissions extends BaseController
{
    public function index()
    {
        $commissionModel = new CommissionsModel();
        $vendorModel = new VendorProfilesModel();

        // Filter by vendor jika ada
        $vendorId = $this->request->getGet('vendor_id');
        $status = $this->request->getGet('status');

        $builder = $commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name, vendor_profiles.owner_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left');

        if ($vendorId && $vendorId !== 'all') {
            $builder->where('commissions.vendor_id', $vendorId);
        }

        if ($status && $status !== 'all') {
            $builder->where('commissions.status', $status);
        }

        $commissions = $builder->orderBy('commissions.created_at', 'DESC')
            ->paginate(20);

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $vendorModel
            ->select('id, business_name')
            ->where('status', 'verified')
            ->orderBy('business_name', 'ASC')
            ->findAll();

        return view('admin/commissions/index', [
            'title'       => 'Manajemen Komisi Vendor',
            'activeMenu'  => 'commissions',
            'commissions' => $commissions,
            'pager'       => $commissionModel->pager,
            'vendors'     => $vendors,
            'vendorId'    => $vendorId,
            'status'      => $status
        ]);
    }

    public function verify($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        // PERBAIKAN: Update status menjadi 'paid' karena hanya ada unpaid dan paid
        $commissionModel->update($id, [
            'status'      => 'paid',
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_by' => session('user_id'),
            'paid_at'     => date('Y-m-d H:i:s')
        ]);

        $this->logActivity($commission['vendor_id'], 'commissions', 'verify', "Komisi #{$id} telah diverifikasi dan ditandai sebagai dibayar.");

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Komisi berhasil diverifikasi dan ditandai sebagai dibayar.']);
        }
        return redirect()->back()->with('success', 'Komisi telah diverifikasi dan ditandai sebagai dibayar.');
    }

    public function delete($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        $commissionModel->delete($id);

        $this->logActivity(
            $commission['vendor_id'],
            'commissions',
            'delete',
            "Komisi #{$id} telah dihapus."
        );

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Komisi berhasil dihapus.']);
        }
        return redirect()->back()->with('success', 'Komisi telah dihapus.');
    }

    public function bulkAction()
    {
        $action = $this->request->getPost('action');
        $commissionIds = $this->request->getPost('commission_ids');

        if (empty($commissionIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada komisi yang dipilih.']);
        }

        // Jika commission_ids adalah string, convert ke array
        if (is_string($commissionIds)) {
            $commissionIds = json_decode($commissionIds, true) ?? explode(',', $commissionIds);
        }

        $commissionModel = new CommissionsModel();
        $successCount = 0;
        $errorCount = 0;

        foreach ($commissionIds as $id) {
            try {
                $commission = $commissionModel->find($id);
                if (!$commission) {
                    $errorCount++;
                    continue;
                }

                switch ($action) {
                    case 'verify':
                        // PERBAIKAN: Update status menjadi 'paid'
                        $commissionModel->update($id, [
                            'status' => 'paid',
                            'verified_at' => date('Y-m-d H:i:s'),
                            'verified_by' => session('user_id'),
                            'paid_at' => date('Y-m-d H:i:s')
                        ]);
                        break;

                    case 'delete':
                        $commissionModel->delete($id);
                        break;

                    default:
                        $errorCount++;
                        continue 2; // Skip ke komisi berikutnya
                }

                // Log activity
                $this->logActivity(
                    $commission['vendor_id'],
                    'commissions',
                    $action,
                    "Komisi #{$id} telah diproses dengan aksi: {$action}"
                );

                $successCount++;

            } catch (\Exception $e) {
                $errorCount++;
            }
        }

        if ($successCount > 0) {
            $message = "Berhasil memproses {$successCount} komisi.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} komisi gagal diproses.";
            }
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal memproses komisi yang dipilih.']);
        }
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        $vendorId = $vendorId ?? 0;

        $activityLogsModel = new ActivityLogsModel();
        $activityLogsModel->insert([
            'user_id'     => session('user_id'),
            'vendor_id'   => $vendorId,
            'module'      => $module,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $this->request->getIPAddress(),
            'user_agent'  => (string) $this->request->getUserAgent(),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}