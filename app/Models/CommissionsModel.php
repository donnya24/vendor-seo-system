<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsModel extends Model
{
    protected $table            = 'commissions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    // PERBAIKAN: Hanya kolom yang benar-benar ada di database
    protected $allowedFields = [
        'vendor_id',
        'period_start', 
        'period_end',
        'earning',
        'amount',
        'status',
        'proof',
        'paid_at', 
        'created_at',
        'updated_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Mengambil data komisi beserta nama vendor dari tabel vendor_profiles.
     *
     * @param string|null $month Filter berdasarkan bulan (format YYYY-MM)
     * @return array
     */
    public function getCommissionsWithVendor($month = null)
    {
        $builder = $this->db->table($this->table);
        
        $builder->select('
            commissions.*,
            vendor_profiles.business_name as vendor_name,
            vendor_profiles.owner_name,
            CONCAT(DATE_FORMAT(commissions.period_start, "%M %Y"), " - ", DATE_FORMAT(commissions.period_end, "%M %Y")) as period
        ');
        $builder->join('vendor_profiles', 'vendor_profiles.id = commissions.vendor_id', 'left');

        if ($month) {
            $builder->where("DATE_FORMAT(commissions.period_start,'%Y-%m')", $month);
        }

        $builder->orderBy('commissions.period_start', 'DESC');

        return $builder->get()->getResultArray();
    }
}