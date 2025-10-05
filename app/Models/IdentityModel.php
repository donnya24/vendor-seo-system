<?php

namespace App\Models;

use CodeIgniter\Model;

class IdentityModel extends Model
{
    protected $table = 'auth_identities';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'type', 'secret', 'secret2', 'created_at', 'updated_at'];
    protected $useTimestamps = true; // Aktifkan timestamps otomatis
    protected $returnType = 'array';
    protected $dateFormat = 'datetime';

    /**
     * Mendapatkan identitas berdasarkan user_id dan type
     */
    public function getByUserIdAndType($userId, $type)
    {
        return $this->where('user_id', $userId)
                    ->where('type', $type)
                    ->first();
    }

    /**
     * Mendapatkan email password identity untuk user
     */
    public function getEmailIdentity($userId)
    {
        return $this->getByUserIdAndType($userId, 'email_password');
    }

    /**
     * Update atau buat email password identity untuk user
     */
    public function saveEmailIdentity($userId, $email, $password)
    {
        $identity = $this->getEmailIdentity($userId);
        
        $data = [
            'user_id' => $userId,
            'type' => 'email_password',
            'secret' => $email,
            'secret2' => password_hash($password, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($identity) {
            // Update yang sudah ada
            return $this->update($identity['id'], $data);
        } else {
            // Buat baru
            $data['created_at'] = date('Y-m-d H:i:s');
            return $this->insert($data);
        }
    }
}