<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorProfilesModel extends Model
{
    protected $table         = 'vendor_profiles';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id','business_name','owner_name','name','phone','wa_number',
        'imersa_wa_number','status','is_verified','commission_rate',
        'created_at','updated_at'
    ];
}
