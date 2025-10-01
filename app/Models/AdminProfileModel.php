<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminProfileModel extends Model
{
    protected $table         = 'admin_profiles';
    protected $primaryKey    = 'id';
    protected $allowedFields = [
        'user_id',
        'name',
        'email',
        'phone',
        'profile_image',
        'status',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $returnType    = 'array';

    // Method untuk mendapatkan admin profile berdasarkan user_id
    public function getAdminProfile($userId = null)
    {
        if ($userId === null) {
            $userId = session()->get('user_id');
        }
        
        return $this->where('user_id', $userId)->first();
    }
}