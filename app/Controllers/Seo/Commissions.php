<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\VendorProfilesModel;

class Commissions extends BaseController
{
    public function index()
    {
        // Ambil filter dari query string
        $vendorId = $this->request->getGet('vendor_id');
        $vendorId = $vendorId ? (int) $vendorId : null;
        
        $status = $this->request->getGet('status');

        $commissionModel = new CommissionsModel();
        $vendorModel = new VendorProfilesModel();

        // Ambil daftar vendor untuk dropdown filter
        $vendors = $vendorModel->findAll();

        $query = $commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left')
            ->orderBy('commissions.period_start', 'DESC');

        // Filter berdasarkan vendor_id jika dipilih
        if (!empty($vendorId)) {
            $query->where('commissions.vendor_id', $vendorId);
        }

        // Filter berdasarkan status jika dipilih
        if (!empty($status) && in_array($status, ['paid', 'unpaid'])) {
            $query->where('commissions.status', $status);
        }

        $commissions = $query->paginate(20);

        // Log aktivitas view commissions
        if (!empty($vendorId)) {
            $logMessage = !empty($status) 
                ? "Melihat daftar komisi vendor {$vendorId} dengan status {$status}"
                : "Melihat daftar komisi vendor {$vendorId}";
            
            log_crud_activity('read', 'komisi vendor', $vendorId, [
                'module' => 'commissions',
                'vendor_id' => $vendorId,
                'status_filter' => $status
            ]);
        } else {
            $logMessage = !empty($status) 
                ? "Melihat daftar komisi semua vendor dengan status {$status}"
                : "Melihat daftar komisi semua vendor";
            
            log_activity_auto('view', $logMessage, [
                'module' => 'commissions',
                'status_filter' => $status
            ]);
        }

        return view('seo/commissions/index', [
            'title'       => 'Komisi Vendor',
            'activeMenu'  => 'commissions',
            'commissions' => $commissions,
            'pager'       => $commissionModel->pager,
            'vendorId'    => $vendorId,
            'status'      => $status,
            'vendors'     => $vendors,
        ]);
    }

    public function approve($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        $commissionModel->update($id, [
            'status'      => 'paid',
            'approved_at' => date('Y-m-d H:i:s'),
            'paid_at'     => date('Y-m-d H:i:s'),
        ]);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('approve', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah diverifikasi & dibayar", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id']
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('msg', 'Komisi telah diverifikasi & dibayar.');
    }

    public function reject($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Komisi tidak ditemukan.']);
            }
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        $commissionModel->update($id, [
            'status'      => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
        ]);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('reject', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah ditolak", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id']
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }
        return redirect()->back()->with('msg', 'Komisi telah ditolak.');
    }

    public function markAsPaid($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        $commissionModel->update($id, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
        ]);

        // Gunakan helper log_activity_auto untuk action khusus
        log_activity_auto('mark_as_paid', "Komisi #{$id} untuk vendor {$commission['vendor_id']} telah ditandai sebagai dibayar", [
            'module' => 'commissions',
            'vendor_id' => $commission['vendor_id']
        ]);

        return redirect()->back()->with('msg', 'Komisi telah dibayar.');
    }

    public function delete($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if ($commission) {
            $vendorId = $commission['vendor_id'];
            $commissionModel->delete($id);

            // Gunakan helper CRUD untuk delete
            log_crud_activity('delete', 'komisi', $id, [
                'module' => 'commissions',
                'vendor_id' => $vendorId
            ]);

            if (!$this->request->isAJAX()) {
                return redirect()->back()->with('msg', 'Komisi telah dihapus.');
            }
        }

        return $this->response->setJSON(['ok' => true]);
    }
}