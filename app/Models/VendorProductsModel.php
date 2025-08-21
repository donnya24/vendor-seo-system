<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorProductsModel extends Model
{
    protected $table            = 'vendor_products';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'vendor_id',
        'product_name',
        'description',
        'price',
        'created_at',
        'updated_at',
    ];

    // created_at / updated_at diisi otomatis
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validasi sesuai tipe kolom (price: DECIMAL(12,2))
    protected $validationRules = [
        'vendor_id'    => 'required|is_natural_no_zero',
        'product_name' => 'required|min_length[3]|max_length[150]',
        'description'  => 'permit_empty|string',
        'price'        => 'permit_empty|decimal',
    ];

    /**
     * Ambil semua produk milik vendor tertentu.
     */
    public function listByVendor(int $vendorId): array
    {
        return $this->where('vendor_id', $vendorId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Pencarian produk per vendor.
     */
    public function searchByVendor(int $vendorId, string $q): array
    {
        return $this->where('vendor_id', $vendorId)
                    ->groupStart()
                        ->like('product_name', $q)
                        ->orLike('description', $q)
                    ->groupEnd()
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
