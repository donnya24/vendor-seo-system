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
}
