<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadModel extends Model
{
    protected $table = 'leads';
    protected $primaryKey = 'id';
    protected $allowedFields = ['vendor_id', 'service_id', 'area_id', 'customer_name', 'status', 'distribution_status', 'assigned_at', 'assigned_by'];
    protected $useTimestamps = true;

    // Validasi jika diperlukan
    protected $validationRules = [
        'vendor_id'      => 'required|integer',
        'service_id'     => 'required|integer',
        'area_id'        => 'required|integer',
        'customer_name'  => 'required|string|max_length[100]',
        'status'         => 'required|in_list[0,1,2]', // 0 - Pending, 1 - Assigned, 2 - Closed
        'assigned_by'    => 'permit_empty|integer',
    ];

    // Mengambil data leads berdasarkan vendor_id
    public function getLeadsByVendor($vendor_id)
    {
        return $this->where('vendor_id', $vendor_id)->findAll();
    }
}
