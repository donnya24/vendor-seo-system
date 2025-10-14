<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'username',
        'status',
        'status_message',
        'active',
        'last_active',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // ==== HAPUS method reset password karena kolom tidak ada ====
    // Kolom reset_token dan reset_expires_at tidak ada di tabel
    
    /**
     * Update status user menjadi active
     */
    public function activateUser($userId)
    {
        return $this->update($userId, [
            'status' => 'active',
            'active' => 1
        ]);
    }
    
    /**
     * Update last_active timestamp
     */
    public function updateLastActive($userId)
    {
        return $this->update($userId, [
            'last_active' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->where('active', 1)
                    ->where('status', 'active')
                    ->findAll();
    }
    
    /**
     * Get users by status
     */
    public function getUsersByStatus($status)
    {
        return $this->where('status', $status)->findAll();
    }
}