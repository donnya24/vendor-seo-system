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
        'tanggal_mulai',
        'tanggal_selesai',
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
     * Digunakan untuk halaman daftar leads
     */
    public function getLeadsWithVendor(int $vendorId, ?string $start = null, ?string $end = null)
    {
        $builder = $this->select('leads.*, vendor_profiles.business_name as vendor_name')
                        ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left')
                        ->where('leads.vendor_id', $vendorId);

        // Jika start dan end diberikan, filter berdasarkan periode
        if (!empty($start) && !empty($end)) {
            $builder->where('leads.tanggal_mulai <=', $end)
                    ->where('leads.tanggal_selesai >=', $start);
        }

        return $builder->orderBy('leads.tanggal_mulai', 'DESC')->paginate(20);
    }

    /**
     * Ambil statistik dashboard vendor berdasarkan rentang tanggal
     * Method ini dikembalikan seperti semula agar tidak mengganggu controller lain
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
     * Method BARU: Ambil total keseluruhan leads untuk satu vendor
     * Digunakan untuk dashboard yang menampilkan total tanpa filter tanggal
     */
    public function getTotalLeadsByVendor(int $vendorId): array
    {
        $row = $this->select("
                    COALESCE(SUM(jumlah_leads_masuk), 0) AS total_leads_masuk,
                    COALESCE(SUM(jumlah_leads_closing), 0) AS total_leads_closing
                ")
                ->where('vendor_id', $vendorId)
                ->first();

        $totalMasuk = (int)($row['total_leads_masuk'] ?? 0);
        $totalClosing = (int)($row['total_leads_closing'] ?? 0);
        
        // Hitung konversi
        $konversi = $totalMasuk > 0 ? round(($totalClosing / $totalMasuk) * 100, 1) : 0;

        return [
            'total' => $totalMasuk, // Untuk kompatibilitas dengan view
            'closed' => $totalClosing, // Untuk kompatibilitas dengan view
            'total_leads_masuk' => $totalMasuk,
            'total_leads_closing' => $totalClosing,
            'konversi' => $konversi,
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