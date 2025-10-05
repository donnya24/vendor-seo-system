<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorAreasModel extends Model
{
    protected $table      = 'vendor_areas';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    // Kolom yang benar sesuai tabel: id, vendor_id, area_id, created_at
    protected $allowedFields = ['vendor_id', 'area_id', 'created_at'];

    // Kita set created_at sendiri di controller
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';

    // Helper opsional
    public function detachAllForVendor(int $vendorId): bool
    {
        return (bool) $this->where('vendor_id', $vendorId)->delete();
    }

    public function attachBatch(int $vendorId, array $areaIds): void
    {
        if (!$areaIds) return;
        $now  = date('Y-m-d H:i:s');
        $rows = [];
        foreach ($areaIds as $aid) {
            $rows[] = ['vendor_id' => $vendorId, 'area_id' => (int)$aid, 'created_at' => $now];
        }
        $this->insertBatch($rows);
    }
}