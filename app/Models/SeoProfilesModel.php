<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoProfilesModel extends Model
{
    protected $table         = 'seo_profiles';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id',
        'name',
        'phone',
        'profile_image',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = false; // handle manual
    protected $returnType    = 'array';
}