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
        'google_id',        // ← TAMBAHKAN INI
        'google_profile',   // ← TAMBAHKAN INI
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

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

    /**
     * Find user by Google ID
     */
    public function findByGoogleId($googleId)
    {
        return $this->where('google_id', $googleId)->first();
    }

    /**
     * Find users without Google ID (email/password users)
     */
    public function findNonGoogleUsers()
    {
        return $this->where('google_id IS NULL', null, false)
                    ->orWhere('google_id', '')
                    ->findAll();
    }
}