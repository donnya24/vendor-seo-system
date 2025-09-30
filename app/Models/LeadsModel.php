<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table            = 'leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'vendor_id',
        'tanggal_mulai',       // Tanggal mulai periode laporan
        'tanggal_selesai',     // Tanggal selesai periode laporan
        'jumlah_leads_masuk',
        'jumlah_leads_diproses',
        'jumlah_leads_ditolak',
        'jumlah_leads_closing',
        'reported_by_vendor',
        'assigned_at',
        'updated_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil data leads dengan vendor, bisa difilter rentang tanggal
     */
    public function getLeadsWithVendor(int $vendorId, ?string $start = null, ?string $end = null)
    {
        $builder = $this->select('leads.*, vendor_profiles.business_name as vendor_name')
                        ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
                        ->where('leads.vendor_id', $vendorId);

        if (!empty($start) && !empty($end)) {
            // Ambil data yang overlap dengan periode filter
            $builder->where('leads.tanggal_mulai <=', $end)
                    ->where('leads.tanggal_selesai >=', $start);
        } elseif (!empty($start)) {
            $builder->where('leads.tanggal_mulai', $start);
        }

        return $builder->orderBy('leads.tanggal_mulai', 'DESC')->paginate(20);
    }

    /**
     * Ambil statistik dashboard vendor berdasarkan rentang tanggal
     */
    public function getDashboardStats(int $vendorId, string $start, string $end): array
    {
        $row = $this->select("
                    COUNT(*) AS total,
                    COALESCE(SUM(jumlah_leads_masuk), 0) AS total_leads_masuk,
                    COALESCE(SUM(jumlah_leads_closing), 0) AS total_leads_closing
                ")
                ->where('vendor_id', $vendorId)
                ->where('tanggal_mulai <=', $end)
                ->where('tanggal_selesai >=', $start)
                ->first();

        return [
            'total'               => (int)($row['total'] ?? 0),
            'total_leads_masuk'   => (int)($row['total_leads_masuk'] ?? 0),
            'total_leads_closing' => (int)($row['total_leads_closing'] ?? 0),
        ];
    }

    /**
     * Validasi rentang tanggal
     */
    public function validateDateRange(string $startDate, string $endDate): bool
    {
        return strtotime($startDate) <= strtotime($endDate);
    }
}
