<?php

namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;
use App\Models\NotificationsModel;
use App\Models\VendorProfilesModel;

class Commissions extends BaseController
{
    private function vendorId(): int {
        $vp = (new VendorProfilesModel())->where('user_id', (int)service('auth')->user()->id)->first();
        return (int)($vp['id'] ?? 0);
    }

    public function index()
    {
        $vid = $this->vendorId();
        $list = (new CommissionsModel())->where('vendor_id',$vid)->orderBy('period_start','DESC')->findAll();
        return view('vendoruser/commissions/index', ['page'=>'Komisi','items'=>$list]);
    }

    // Kirim permintaan verifikasi pembayaran -> catat di notifications
    public function requestPaid()
    {
        $vid = $this->vendorId();
        $amount = (float)$this->request->getPost('amount');
        $note   = trim((string)$this->request->getPost('note'));
        // Buat notifikasi ke admin (user_id null, type commission)
        (new NotificationsModel())->insert([
            'user_id'   => null,
            'vendor_id' => $vid,
            'type'      => 'commission',
            'title'     => 'Konfirmasi Pembayaran Komisi',
            'message'   => 'Vendor mengajukan verifikasi pembayaran: Rp' . number_format($amount,0,',','.') . '. Catatan: ' . $note,
            'is_read'   => 0,
        ]);
        return redirect()->back()->with('success','Permintaan verifikasi terkirim.');
    }
}
