<?php
namespace App\Models;

use CodeIgniter\Model;

class VendorProfilesModel extends Model
{
    protected $table            = 'vendor_profiles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useTimestamps    = true; 
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $allowedFields = [
        'user_id',
        'business_name',
        'owner_name',
        'phone',
        'whatsapp_number',
        'status',
        'profile_image',
        'requested_commission',
        'rejection_reason',
        'inactive_reason', 
        'approved_at',
        'action_by'
    ];

    protected $validationRules = [
        'user_id'             => 'permit_empty|integer',
        'business_name'       => 'required|string|max_length[150]',
        'owner_name'          => 'required|string|max_length[100]',
        'phone'               => 'required|string|max_length[30]',
        'whatsapp_number'     => 'permit_empty|string|max_length[30]',
        'status'              => 'required|in_list[verified,rejected,inactive,pending]',
        'profile_image'       => 'permit_empty|string|max_length[255]',
        'requested_commission'=> 'permit_empty|decimal',
        'rejection_reason'    => 'permit_empty|string',
        'inactive_reason'     => 'permit_empty|string', 
        'approved_at'         => 'permit_empty|valid_date',
        'action_by'           => 'permit_empty|integer'
    ];
}
