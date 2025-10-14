<?php

namespace App\Models;

use CodeIgniter\Model;

class SeoProfilesModel extends Model
{
    protected $table            = 'seo_profiles';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'user_id',
        'name',
        'phone',
        'profile_image',
        'status',
        'created_at',
        'updated_at'
    ];

    // ✅ Lebih aman: timestamps otomatis CI4 akan set created_at & updated_at
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    protected $returnType       = 'array';
    protected $dateFormat       = 'datetime';

    /**
     * ✅ Ambil profil SEO berdasarkan user_id
     */
    public function getByUserId($userId)
    {
        return $this->where('user_id', (int)$userId)->first();
    }

    /**
     * ✅ Ambil atau buat profil SEO berdasarkan user_id
     * Perbaikan:
     * - Gunakan primary key (`id`) saat update, bukan `user_id`
     * - Hindari insert duplikat
     * - Pastikan timestamp otomatis aktif
     */
    public function getOrCreateByUserId($userId, $data = [])
    {
        $profile = $this->getByUserId($userId);

        if ($profile) {
            // Update profil yang ada (pakai id, bukan user_id)
            $this->update($profile['id'], $data);
            return $this->find($profile['id']);
        } else {
            // Buat profil baru
            $data['user_id'] = (int)$userId;
            $this->insert($data);
            return $this->find($this->insertID());
        }
    }
}
