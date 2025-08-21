<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorProfilesModel extends Model
{
    protected $table            = 'vendor_profiles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    // Ikuti persis kolom di DB saat ini
    protected $allowedFields    = [
        'user_id',
        'business_name',
        'owner_name',
        'phone',
        'whatsapp_number',
        'status',
        'is_verified',
        'created_at',
        'updated_at',
    ];

    // Otomatis set created_at & updated_at
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi dasar
    protected $validationRules = [
        'user_id'         => 'permit_empty|is_natural_no_zero',
        'business_name'   => 'required|min_length[3]|max_length[150]',
        'owner_name'      => 'required|min_length[3]|max_length[100]',
        'phone'           => 'required|max_length[30]',
        'whatsapp_number' => 'permit_empty|max_length[30]',
        'status'          => 'in_list[active,inactive,pending]',
        'is_verified'     => 'in_list[0,1]',
    ];

    /**
     * Ambil profil berdasarkan user_id (akun Shield).
     */
    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Scope: hanya vendor aktif.
     */
    public function scopeActive()
    {
        return $this->where('status', 'active');
    }

    /**
     * Scope: hanya vendor terverifikasi.
     */
    public function scopeVerified()
    {
        return $this->where('is_verified', 1);
    }

    /**
     * Pencarian sederhana by nama bisnis / owner.
     */
    public function search(string $q)
    {
        return $this->groupStart()
                        ->like('business_name', $q)
                        ->orLike('owner_name', $q)
                    ->groupEnd();
    }
}
