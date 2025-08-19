<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'username',
        'email',
        'name',
        'status',
        'status_message',
        'active',
        'last_active',
        'created_at',
        'updated_at',
        'deleted_at',
        'password_hash',
        'reset_token',
        'reset_expires_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // ==== Tambahan untuk reset password ====
    public function setResetToken($email, $token, $expires)
    {
        return $this->where('email', $email)
                    ->set([
                        'reset_token' => $token,
                        'reset_expires_at' => $expires
                    ])
                    ->update();
    }

    public function getUserByResetToken($token)
    {
        return $this->where('reset_token', $token)
                    ->where('reset_expires_at >=', date('Y-m-d H:i:s'))
                    ->first();
    }

    public function clearResetToken($userId)
    {
        return $this->update($userId, [
            'reset_token' => null,
            'reset_expires_at' => null
        ]);
    }
}
