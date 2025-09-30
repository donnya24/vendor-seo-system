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
        'jumlah_leads_diproses',
        'jumlah_leads_ditolak',
        'jumlah_leads_closing',
        'reported_by_vendor',
        'assigned_at',
<<<<<<< HEAD
=======
        // Hapus 'status' jika tabel tidak punya kolom ini
>>>>>>> 869b4bc627c145c1f2490a07683852c604bf0f32
        'updated_at',
    ];

    protected $useTimestamps = false;
    protected $createdField  = '';
    protected $updatedField  = 'updated_at';

    /**
<<<<<<< HEAD
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
=======
     * Ambil statistik dashboard vendor berdasarkan rentang tanggal.
     * Jika tabel tidak punya kolom 'status', hanya hitung total.
     */
    public function getDashboardStats(int $vendorId, string $start, string $end): array
    {
        // Cek apakah kolom status ada di tabel
        $db      = $this->db->getFieldNames($this->table);
        $hasStatus = in_array('status', $db, true);

        if ($hasStatus) {
            $row = $this->select("
                        COUNT(*) AS total,
                        COALESCE(SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END), 0)       AS closed,
                        COALESCE(SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END), 0) AS in_progress,
                        COALESCE(SUM(CASE WHEN status = 'new' THEN 1 ELSE 0 END), 0)          AS new_cnt
                    ")
                    ->where('vendor_id', $vendorId)
                    ->where('tanggal >=', $start)
                    ->where('tanggal <=', $end)
                    ->first();

            return [
                'total'       => (int)($row['total'] ?? 0),
                'closed'      => (int)($row['closed'] ?? 0),
                'in_progress' => (int)($row['in_progress'] ?? 0),
                'new_cnt'     => (int)($row['new_cnt'] ?? 0),
            ];
        }

        // Jika kolom status tidak ada, hanya kembalikan total leads
        $total = $this->where('vendor_id', $vendorId)
                      ->where('tanggal >=', $start)
                      ->where('tanggal <=', $end)
                      ->countAllResults();

        return [
            'total'       => $total,
            'closed'      => 0,
            'in_progress' => 0,
            'new_cnt'     => 0,
        ];
    }

    public function getLeadsByVendor(int $vendorId, ?string $start = null, ?string $end = null): array
    {
        $columns = ['id', 'tanggal', 'vendor_id', 'jumlah_leads_masuk', 'jumlah_leads_closing'];

        $dbFields = $this->db->getFieldNames($this->table);
        if (in_array('status', $dbFields, true)) {
            $columns[] = 'status';
        }

        $builder = $this->select(implode(', ', $columns))
                        ->where('vendor_id', $vendorId);

        if (!empty($start) && !empty($end)) {
            $builder->where('tanggal >=', $start)
                    ->where('tanggal <=', $end);
        } elseif (!empty($start)) {
            $builder->where('tanggal', $start);
        }

        return $builder->orderBy('tanggal', 'DESC')->paginate(20);
    }

}
>>>>>>> 869b4bc627c145c1f2490a07683852c604bf0f32
