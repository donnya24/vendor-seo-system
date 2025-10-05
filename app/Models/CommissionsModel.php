<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionsModel extends Model
{
    protected $table         = 'commissions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    
    protected $allowedFields = [
        'vendor_id',
        'period_start',
        'period_end',
        'earning',      
        'amount',
        'status',
        'proof',
        'paid_at',
        'rejected_at',
        'created_at',
        'updated_at',
        'verify_note',
    ];

    /**
     * Mengambil data komisi beserta nama vendor dari tabel vendor_profiles.
     *
     * @param string|null $month Filter berdasarkan bulan (format YYYY-MM)
     * @return array
     */
    public function getCommissionsWithVendor($month = null)
    {
        $builder = $this->db->table($this->table);
        
        // --- PERBAIKAN: Join dengan tabel vendor_profiles dan ambil kolom nama vendor ---
        // Saya asumsikan tabelnya bernama 'vendor_profiles' dan kolom namanya 'business_name'.
        // Jika berbeda, sesuaikan nama tabel dan kolom ini.
        $builder->select('
            commissions.*,
            vendor_profiles.business_name as vendor_name,
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