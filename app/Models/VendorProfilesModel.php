<?php
namespace App\Models;

use CodeIgniter\Model;

class VendorProfilesModel extends Model
{
    protected $table            = 'vendor_profiles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'user_id',
        'business_name',
        'owner_name',
        'whatsapp_number',
        'phone',
        'status',
        'profile_image', // Ubah dari 'img' menjadi 'profile_image'
        'commission_rate',
        'requested_commission',
        'rejection_reason',
        'updated_at',
    ];
}