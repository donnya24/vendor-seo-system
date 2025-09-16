<?php

namespace App\Controllers\Seo;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\ActivityLogsModel;

class Commissions extends BaseController
{
    public function index()
    {
        // Ambil vendor_id dari query string / session, default = 1
        $vendorId = (int) (
            $this->request->getGet('vendor_id')
            ?? session('vendor_id')
            ?? 1
        );

        $commissionModel = new CommissionsModel();

        $commissions = $commissionModel
            ->select('commissions.*, vendor_profiles.business_name as vendor_name')
            ->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left')
            ->where('commissions.vendor_id', $vendorId)
            ->orderBy('commissions.period_start', 'DESC')
            ->paginate(20);

        return view('seo/commissions/index', [
            'title'       => 'Komisi Vendor',
            'activeMenu'  => 'commissions',
            'commissions' => $commissions,
            'pager'       => $commissionModel->pager,
            'vendorId'    => $vendorId,
        ]);
    }

    public function approve($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        $commissionModel->update($id, [
            'status'      => 'paid',
            'approved_at' => date('Y-m-d H:i:s'),
            'paid_at'     => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity(
            $commission['vendor_id'],
            'commissions',
            'approve',
            "Komisi #{$id} telah diverifikasi & dibayar."
        );

        return redirect()->back()->with('msg', 'Komisi telah diverifikasi & dibayar.');
    }

    public function reject($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if (!$commission) {
            return redirect()->back()->with('error', 'Komisi tidak ditemukan.');
        }

        $commissionModel->update($id, [
            'status'      => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
        ]);

        $this->logActivity(
            $commission['vendor_id'],
            'commissions',
            'reject',
            "Komisi #{$id} telah ditolak."
        );

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

        $this->logActivity(
            $commission['vendor_id'],
            'commissions',
            'markAsPaid',
            "Komisi #{$id} telah dibayar."
        );

        return redirect()->back()->with('msg', 'Komisi telah dibayar.');
    }

    public function delete($id)
    {
        $commissionModel = new CommissionsModel();
        $commission = $commissionModel->find($id);

        if ($commission) {
            $commissionModel->delete($id);

            $this->logActivity(
                $commission['vendor_id'],
                'commissions',
                'delete',
                "Komisi #{$id} telah dihapus."
            );

            if (!$this->request->isAJAX()) {
                return redirect()->back()->with('msg', 'Komisi telah dihapus.');
            }
        }

        return $this->response->setJSON(['ok' => true]);
    }

    private function logActivity($vendorId, $module, $action, $description)
    {
        $vendorId = $vendorId ?? 0;

        $userId = session('user_id');
        if (! $userId) {
            // fallback: admin SEO mungkin tidak terdaftar di tabel users vendor
            $userId = null;
        }

        (new ActivityLogsModel())->insert([
            'user_id'     => $userId, // null = aman buat FK
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
