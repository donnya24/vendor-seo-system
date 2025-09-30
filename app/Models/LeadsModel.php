<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadsModel extends Model
{
    protected $table            = 'leads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    // Sesuaikan dengan struktur tabel yang ada di database
    protected $allowedFields    = [
        'vendor_id',
        'tanggal_mulai',    // Tanggal mulai periode laporan
        'tanggal_selesai',  // Tanggal selesai periode laporan
        'jumlah_leads_masuk',
        'jumlah_leads_closing',
        'reported_by_vendor',
        'assigned_at',
        'updated_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';

    /**
     * Mengambil data leads dengan pagination, filter, dan nama vendor.
     * PERBAIKAN: Memindahkan logika join dan select dari controller ke model.
     */
    public function getLeadsWithVendor(int $vendorId, ?string $start = null, ?string $end = null)
    {
        $builder = $this->select('leads.*, vendor_profiles.business_name as vendor_name')
                        ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
                        ->where('leads.vendor_id', $vendorId);

        if (!empty($start) && !empty($end)) {
            // PERBAIKAN: Logika filter untuk mencari laporan yang overlap dengan periode filter
            $builder->where('leads.tanggal_mulai <=', $end)
                    ->where('leads.tanggal_selesai >=', $start);
        } elseif (!empty($start)) {
            // Jika hanya tanggal mulai yang diberikan
            $builder->where('leads.tanggal_mulai', $start);
        }

        return $builder->orderBy('leads.tanggal_mulai', 'DESC')->paginate(20);
    }

    /**
     * Ambil statistik dashboard vendor berdasarkan rentang tanggal.
     */
    public function getDashboardStats(int $vendorId, string $start, string $end): array
    {
        $row = $this->select("
                    COUNT(*) AS total,
                    COALESCE(SUM(jumlah_leads_masuk), 0) AS total_leads_masuk,
                    COALESCE(SUM(jumlah_leads_closing), 0) AS total_leads_closing
                ")
                ->where('vendor_id', $vendorId)
                // Cari overlap antara periode filter dengan periode laporan
                ->where('tanggal_mulai <=', $end)
                ->where('tanggal_selesai >=', $start)
                ->first();

        return [
            'total'             => (int)($row['total'] ?? 0),
            'total_leads_masuk' => (int)($row['total_leads_masuk'] ?? 0),
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