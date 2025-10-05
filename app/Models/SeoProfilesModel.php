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
    protected $useTimestamps = true; // Aktifkan timestamps otomatis
    protected $returnType    = 'array';
    protected $dateFormat    = 'datetime';

    /**
     * Mendapatkan profil SEO berdasarkan user_id
     */
    public function getByUserId($userId)
    {
        return $this->where('user_id', $userId)->first();
    }

    /**
     * Mendapatkan profil SEO berdasarkan user_id atau buat baru jika belum ada
     */
    public function getOrCreateByUserId($userId, $data = [])
    {
        $profile = $this->getByUserId($userId);
        
        if ($profile) {
            // Update profil yang ada
            $this->update($userId, $data);
            return $profile;
        } else {
            // Buat profil baru
            $data['user_id'] = $userId;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            $this->insert($data);
            return $this->find($this->insertID());
        }
    }
}