<?php
namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;

class Vendor_verify extends BaseController
{
    protected $vendorModel;

    public function __construct()
    {
        $this->vendorModel = new VendorProfilesModel();
    }

    public function index()
    {
        $vendors = $this->vendorModel->findAll();
        
        // Log aktivitas view vendor list
        log_activity_auto('view', "Melihat daftar vendor untuk verifikasi", [
            'module' => 'vendor_verify',
            'vendors_count' => count($vendors)
        ]);

        return view('seo/vendor_verify/index', [
            'vendors'    => $vendors,
            'title'      => 'Daftar Vendor',
            'activeMenu' => 'vendor'
        ]);
    }

    public function approve($id)
    {
        $user   = service('auth')->user();
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            // Log aktivitas gagal approve vendor
            log_activity_auto('approve', "Gagal menyetujui vendor - tidak ditemukan", [
                'module' => 'vendor_verify',
                'status' => 'failed',
                'vendor_id' => $id
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Vendor tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
        }

        $this->vendorModel->update($id, [
            'status'      => 'verified',
            'approved_at' => date('Y-m-d H:i:s'),
            'action_by'   => $user->id
        ]);

        // Log aktivitas berhasil approve vendor
        log_activity_auto('approve', "Menyetujui vendor: {$vendor['business_name']}", [
            'module' => 'vendor_verify',
            'status' => 'success',
            'vendor_id' => $id,
            'vendor_name' => $vendor['business_name'],
            'previous_status' => $vendor['status'] ?? 'unknown'
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Vendor berhasil disetujui.']);
        }

        return redirect()->back()->with('success', 'Vendor berhasil disetujui.');
    }

    public function reject($id)
    {
        $user   = service('auth')->user();
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            // Log aktivitas gagal reject vendor
            log_activity_auto('reject', "Gagal menolak vendor - tidak ditemukan", [
                'module' => 'vendor_verify',
                'status' => 'failed',
                'vendor_id' => $id
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Vendor tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
        }

        $this->vendorModel->update($id, [
            'status'      => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'action_by'   => $user->id
        ]);

        // Log aktivitas berhasil reject vendor
        log_activity_auto('reject', "Menolak vendor: {$vendor['business_name']}", [
            'module' => 'vendor_verify',
            'status' => 'success',
            'vendor_id' => $id,
            'vendor_name' => $vendor['business_name'],
            'previous_status' => $vendor['status'] ?? 'unknown'
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Vendor berhasil ditolak.']);
        }

        return redirect()->back()->with('success', 'Vendor berhasil ditolak.');
    }

    public function pending($id)
    {
        $user   = service('auth')->user();
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            // Log aktivitas gagal set pending vendor
            log_activity_auto('pending', "Gagal mengembalikan vendor ke status pending - tidak ditemukan", [
                'module' => 'vendor_verify',
                'status' => 'failed',
                'vendor_id' => $id
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Vendor tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
        }

        $this->vendorModel->update($id, [
            'status'      => 'pending',
            'action_by'   => $user->id,
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        // Log aktivitas berhasil set pending vendor
        log_activity_auto('pending', "Mengembalikan vendor ke status pending: {$vendor['business_name']}", [
            'module' => 'vendor_verify',
            'status' => 'success',
            'vendor_id' => $id,
            'vendor_name' => $vendor['business_name'],
            'previous_status' => $vendor['status'] ?? 'unknown'
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Vendor berhasil dikembalikan ke status pending.']);
        }

        return redirect()->back()->with('success', 'Vendor berhasil dikembalikan ke status pending.');
    }

    public function detail($id) // Changed from view() to detail()
    {
        $vendor = $this->vendorModel->find($id);

        if (!$vendor) {
            // Log aktivitas gagal view vendor detail
            log_activity_auto('view', "Gagal melihat detail vendor - tidak ditemukan", [
                'module' => 'vendor_verify',
                'status' => 'failed',
                'vendor_id' => $id
            ]);

            return redirect()->back()->with('error', 'Vendor tidak ditemukan.');
        }

        // Log aktivitas view vendor detail
        log_activity_auto('view', "Melihat detail vendor: {$vendor['business_name']}", [
            'module' => 'vendor_verify',
            'status' => 'success',
            'vendor_id' => $id,
            'vendor_name' => $vendor['business_name'],
            'vendor_status' => $vendor['status'] ?? 'unknown'
        ]);

        return view('seo/vendor_verify/view', [
            'vendor'     => $vendor,
            'title'      => 'Detail Vendor',
            'activeMenu' => 'vendor'
        ]);
    }

    public function bulkAction()
    {
        $user = service('auth')->user();
        $action = $this->request->getPost('action');
        $vendorIds = $this->request->getPost('vendor_ids');

        if (empty($vendorIds) || !is_array($vendorIds)) {
            // Log aktivitas bulk action gagal
            log_activity_auto('bulk_action', "Gagal melakukan aksi bulk - vendor tidak dipilih", [
                'module' => 'vendor_verify',
                'status' => 'failed',
                'action' => $action
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Tidak ada vendor yang dipilih.']);
            }
            return redirect()->back()->with('error', 'Tidak ada vendor yang dipilih.');
        }

        $successCount = 0;
        $validActions = ['approve', 'reject', 'pending'];

        if (!in_array($action, $validActions)) {
            // Log aktivitas bulk action gagal - aksi tidak valid
            log_activity_auto('bulk_action', "Gagal melakukan aksi bulk - aksi tidak valid", [
                'module' => 'vendor_verify',
                'status' => 'failed',
                'action' => $action,
                'vendor_count' => count($vendorIds)
            ]);

            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Aksi tidak valid.']);
            }
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        }

        foreach ($vendorIds as $vendorId) {
            $vendor = $this->vendorModel->find($vendorId);
            if ($vendor) {
                $updateData = [
                    'action_by'  => $user->id,
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                if ($action === 'approve') {
                    $updateData['status'] = 'verified';
                    $updateData['approved_at'] = date('Y-m-d H:i:s');
                } elseif ($action === 'reject') {
                    $updateData['status'] = 'rejected';
                    $updateData['rejected_at'] = date('Y-m-d H:i:s');
                } elseif ($action === 'pending') {
                    $updateData['status'] = 'pending';
                }

                $this->vendorModel->update($vendorId, $updateData);
                $successCount++;

                // Log individual vendor action
                log_activity_auto($action, "Aksi bulk {$action} vendor: {$vendor['business_name']}", [
                    'module' => 'vendor_verify',
                    'status' => 'success',
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor['business_name'],
                    'bulk_action' => true
                ]);
            }
        }

        // Log summary bulk action
        log_activity_auto('bulk_action', "Berhasil melakukan aksi bulk {$action} pada {$successCount} vendor", [
            'module' => 'vendor_verify',
            'status' => 'success',
            'action' => $action,
            'total_vendors' => count($vendorIds),
            'success_count' => $successCount,
            'failed_count' => count($vendorIds) - $successCount
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => "Berhasil {$action} {$successCount} vendor.",
                'processed_count' => $successCount
            ]);
        }

        return redirect()->back()->with('success', "Berhasil {$action} {$successCount} vendor.");
    }
}