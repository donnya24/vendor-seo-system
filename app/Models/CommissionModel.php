<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionModel extends Model
{
    protected $table = 'commissions';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id', 'lead_id', 'amount', 'status', 'date_paid'];
    protected $useTimestamps = true;

    // Validasi jika diperlukan
    protected $validationRules = [
        'vendor_id'    => 'required|integer',
        'lead_id'      => 'required|integer',
        'amount'       => 'required|decimal',
        'status'       => 'required|in_list[pending,paid,cancelled]',
        'date_paid'    => 'permit_empty|valid_date',
    ];

    // Mengambil komisi berdasarkan vendor_id
    public function getCommissionsByVendor($vendor_id)
    {
        return $this->where('vendor_id', $vendor_id)->findAll();
    }

    // Mengubah status komisi menjadi 'paid'
    public function markAsPaid($id)
    {
        return $this->update($id, ['status' => 'paid', 'date_paid' => date('Y-m-d')]);
    }
}
