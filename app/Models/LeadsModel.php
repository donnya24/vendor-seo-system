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
     * Ambil data leads dengan vendor, bisa difilter vendor dan/atau rentang tanggal
     * Sekarang mendukung:
     * - Hanya vendor saja
     * - Hanya periode saja (start saja, end saja, atau keduanya)
     * - Vendor dan periode
     * - Semua data (tanpa filter)
     */
    public function getLeadsWithVendor(?int $vendorId = null, ?string $start = null, ?string $end = null)
    {
        $builder = $this->select('leads.*, vendor_profiles.business_name as vendor_name')
                        ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id', 'left');

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('leads.vendor_id', $vendorId);
        }

        // Filter tanggal - lebih fleksibel
        $this->applyDateFilter($builder, $start, $end);

        return $builder->orderBy('leads.tanggal_mulai', 'DESC')->paginate(20);
    }

    /**
     * Helper method untuk apply filter tanggal yang fleksibel
     */
    private function applyDateFilter(&$builder, ?string $start = null, ?string $end = null)
    {
        // Jika kedua tanggal diisi
        if (!empty($start) && !empty($end)) {
            $builder->groupStart()
                    ->where('leads.tanggal_mulai >=', $start)
                    ->where('leads.tanggal_mulai <=', $end)
                    ->orGroupStart()
                    ->where('leads.tanggal_selesai >=', $start)
                    ->where('leads.tanggal_selesai <=', $end)
                    ->orGroupStart()
                    ->where('leads.tanggal_mulai <=', $start)
                    ->where('leads.tanggal_selesai >=', $end)
                    ->groupEnd()
                    ->groupEnd()
                    ->groupEnd();
        }
        // Jika hanya start date yang diisi
        elseif (!empty($start)) {
            $builder->where('leads.tanggal_selesai >=', $start);
        }
        // Jika hanya end date yang diisi
        elseif (!empty($end)) {
            $builder->where('leads.tanggal_mulai <=', $end);
        }
        // Jika tidak ada filter tanggal, tampilkan semua
    }

    /**
     * Ambil statistik dashboard vendor berdasarkan rentang tanggal
     */
    public function getDashboardStats(int $vendorId, string $start, string $end): array
    {
        $builder = $this->select("
                    COUNT(*) AS total,
                    COALESCE(SUM(jumlah_leads_masuk), 0) AS total_leads_masuk,
                    COALESCE(SUM(jumlah_leads_closing), 0) AS total_leads_closing
                ")
                ->where('vendor_id', $vendorId);

        $this->applyDateFilter($builder, $start, $end);

        $row = $builder->first();

        return [
            'total'               => (int)($row['total'] ?? 0),
            'total_leads_masuk'   => (int)($row['total_leads_masuk'] ?? 0),
            'total_leads_closing' => (int)($row['total_leads_closing'] ?? 0),
        ];
    }

    /**
     * Ambil total keseluruhan leads untuk satu vendor atau semua vendor
     */
    public function getTotalLeadsByVendor(?int $vendorId = null): array
    {
        $builder = $this->select("
                    COALESCE(SUM(jumlah_leads_masuk), 0) AS total_leads_masuk,
                    COALESCE(SUM(jumlah_leads_closing), 0) AS total_leads_closing
                ");

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('vendor_id', $vendorId);
        }

        $row = $builder->first();

        $totalMasuk = (int)($row['total_leads_masuk'] ?? 0);
        $totalClosing = (int)($row['total_leads_closing'] ?? 0);
        
        // Hitung konversi
        $konversi = $totalMasuk > 0 ? round(($totalClosing / $totalMasuk) * 100, 1) : 0;

        return [
            'total' => $totalMasuk,
            'closed' => $totalClosing,
            'total_leads_masuk' => $totalMasuk,
            'total_leads_closing' => $totalClosing,
            'konversi' => $konversi,
        ];
    }

    /**
     * Ambil statistik summary untuk halaman leads dengan filter
     */
    public function getLeadsSummary(?int $vendorId = null, ?string $start = null, ?string $end = null): array
    {
        $builder = $this->select('
            SUM(jumlah_leads_masuk) as total_masuk,
            SUM(jumlah_leads_closing) as total_closing,
            COUNT(*) as total_periode
        ');

        // Filter vendor jika dipilih
        if (!empty($vendorId)) {
            $builder->where('vendor_id', $vendorId);
        }

        // Filter tanggal
        $this->applyDateFilter($builder, $start, $end);

        $result = $builder->get()->getRowArray();

        $totalMasuk = (int)($result['total_masuk'] ?? 0);
        $totalClosing = (int)($result['total_closing'] ?? 0);
        $conversionRate = $totalMasuk > 0 ? ($totalClosing / $totalMasuk) * 100 : 0;

        return [
            'total_masuk' => $totalMasuk,
            'total_closing' => $totalClosing,
            'total_periode' => (int)($result['total_periode'] ?? 0),
            'conversion_rate' => round($conversionRate, 1)
        ];
    }

    /**
     * Ambil daftar vendor yang memiliki data leads
     */
    public function getVendorsWithLeads(): array
    {
        return $this->select('DISTINCT(vendor_profiles.id), vendor_profiles.business_name')
                    ->join('vendor_profiles', 'vendor_profiles.id = leads.vendor_id')
                    ->orderBy('vendor_profiles.business_name', 'ASC')
                    ->findAll();
    }

    /**
     * Validasi rentang tanggal
     */
    public function validateDateRange(string $startDate, string $endDate): bool
    {
        return strtotime($startDate) <= strtotime($endDate);
    }

    /**
     * Cek apakah vendor memiliki data leads
     */
    public function vendorHasLeads(int $vendorId): bool
    {
        return $this->where('vendor_id', $vendorId)->countAllResults() > 0;
    }
}