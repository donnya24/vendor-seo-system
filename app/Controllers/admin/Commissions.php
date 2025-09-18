<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CommissionsModel;

class Commissions extends BaseController
{
    public function index()
    {
        $m = new CommissionsModel();

        // Filter bulan (opsional)
        $month = $this->request->getGet('month'); // format YYYY-MM
        if ($month) {
            $m->where("DATE_FORMAT(period,'%Y-%m')", $month);
        }

        $rows = $m->orderBy('period','DESC')->findAll();

        return view('admin/commissions/index', [
            'page' => 'Commissions',
            'rows' => $rows,
            'month'=> $month,
        ]);
    }

    public function markPaid($id)
    {
        $note = (string) $this->request->getPost('verify_note');
        (new CommissionsModel())->update($id, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d H:i:s'),
            'verify_note' => $note,
        ]);
        return redirect()->back()->with('success','Commission marked as PAID.');
    }

    /**
     * APPROVE pengajuan vendor (dipakai tombol Setujui di dashboard).
     * - Update di vendor_profiles: status -> verified (opsional: is_verified=1, commission_rate=requested_commission)
     * - Masukkan user ke group 'vendor' jika user_id ada.
     * Response: JSON { ok: true|false, msg?: string }
     */
    public function approve()
    {
        $id = (int)($this->request->getPost('id') ?? 0);
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'msg' => 'ID tidak valid']);
        }

        $db = db_connect();
        $db->transStart();

        // Pastikan pengajuan ada
        $vp = $db->table('vendor_profiles')->where('id',$id)->get()->getRowArray();
        if (!$vp) {
            $db->transComplete();
            return $this->response->setStatusCode(404)->setJSON(['ok'=>false,'msg'=>'Pengajuan tidak ditemukan']);
        }

        // Deteksi kolom vendor_profiles
        $vpFields = $db->getFieldNames('vendor_profiles');

        // Siapkan update status
        $update = [
            'status'     => 'verified',
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if (in_array('is_verified', $vpFields, true)) {
            $update['is_verified'] = 1;
        }
        if (!empty($vp['requested_commission']) && in_array('commission_rate', $vpFields, true)) {
            $update['commission_rate'] = $vp['requested_commission'];
        }
        if (in_array('requested_commission', $vpFields, true)) {
            $update['requested_commission'] = null; // bersihkan permintaan
        }

        $db->table('vendor_profiles')->where('id',$id)->update($update);

        // Pastikan user termasuk group 'vendor' agar muncul di Management User
        if (!empty($vp['user_id'])) {
            $exists = $db->table('auth_groups_users')
                ->where('user_id', (int)$vp['user_id'])
                ->where('group', 'vendor')
                ->countAllResults();

            if (!$exists) {
                $db->table('auth_groups_users')->insert([
                    'user_id' => (int)$vp['user_id'],
                    'group'   => 'vendor',
                ]);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'msg' => 'Gagal menyetujui pengajuan']);
        }

        return $this->response->setJSON(['ok' => true]);
    }

    /**
     * REJECT / Tidak Setuju pengajuan vendor (dipakai tombol Tidak Setuju di dashboard).
     * Requirement: data pengajuan dihapus sehingga hilang dari daftar.
     * Response: JSON { ok: true|false, msg?: string }
     */
    public function reject()
    {
        $id = (int)($this->request->getPost('id') ?? 0);
        if (!$id) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'msg' => 'ID tidak valid']);
        }

        $db = db_connect();
        $db->transStart();

        // Pastikan pengajuan ada
        $found = $db->table('vendor_profiles')->select('id')->where('id',$id)->get()->getRowArray();
        if (!$found) {
            $db->transComplete();
            return $this->response->setStatusCode(404)->setJSON(['ok'=>false,'msg'=>'Pengajuan tidak ditemukan']);
        }

        // Hapus pengajuan sesuai requirement
        $db->table('vendor_profiles')->where('id',$id)->delete();

        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'msg' => 'Gagal menolak pengajuan']);
        }

        return $this->response->setJSON(['ok' => true]);
    }
}
