<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationsModel extends Model
{
    protected $table         = 'notifications';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields = [
        'user_id', 'admin_id', 'vendor_id', 'seo_id', 'type', 
        'title', 'message', 'is_read', 'read_at',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation rules
    protected $validationRules = [
        'user_id' => 'required|numeric',
        'admin_id' => 'permit_empty|numeric',
        'vendor_id' => 'permit_empty|numeric',
        'seo_id' => 'permit_empty|numeric',
        'title'   => 'required|max_length[255]',
        'message' => 'required',
        'type'    => 'permit_empty|max_length[50]'
    ];

    protected $validationMessages = [
        'user_id' => [
            'required' => 'User ID harus diisi',
            'numeric'  => 'User ID harus berupa angka'
        ],
        'title' => [
            'required' => 'Judul notifikasi harus diisi',
            'max_length' => 'Judul notifikasi maksimal 255 karakter'
        ],
        'message' => [
            'required' => 'Pesan notifikasi harus diisi'
        ]
    ];

    /**
     * Get notifications with related data (LENGKAP)
     */
    public function getWithRelations($limit = 50)
    {
        return $this->select('notifications.*, 
                             u.username as user_username,
                             a.name as admin_name, a.id as admin_profile_id,
                             v.business_name as vendor_name, v.owner_name as vendor_owner,
                             s.name as seo_name, s.id as seo_profile_id')
                   ->join('users u', 'u.id = notifications.user_id', 'left')
                   ->join('admin_profiles a', 'a.id = notifications.admin_id', 'left')
                   ->join('vendor_profiles v', 'v.id = notifications.vendor_id', 'left')
                   ->join('seo_profiles s', 's.id = notifications.seo_id', 'left')
                   ->orderBy('notifications.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get notifications by admin_id (dari admin_profiles)
     */
    public function getByAdminId($adminId, $limit = 20)
    {
        return $this->where('admin_id', $adminId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get notifications by seo_id (dari seo_profiles)
     */
    public function getBySeoId($seoId, $limit = 20)
    {
        return $this->where('seo_id', $seoId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get notifications by vendor_id
     */
    public function getByVendorId($vendorId, $limit = 20)
    {
        return $this->where('vendor_id', $vendorId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get commission change notifications
     */
    public function getCommissionChangeNotifications($limit = 50)
    {
        return $this->where('type', 'commission_change')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get notifications for specific user group (Admin/Seoteam) dengan relasi profil
     */
    public function getForUserGroup($group, $limit = 20)
    {
        $db = \Config\Database::connect();
        
        if ($group === 'admin') {
            return $db->table('notifications n')
                     ->select('n.*, u.username, a.name as admin_name, a.id as admin_profile_id')
                     ->join('users u', 'u.id = n.user_id')
                     ->join('admin_profiles a', 'a.id = n.admin_id')
                     ->join('auth_groups_users agu', 'agu.user_id = u.id')
                     ->where('agu.group', $group)
                     ->orderBy('n.created_at', 'DESC')
                     ->limit($limit)
                     ->get()
                     ->getResultArray();
        } else if ($group === 'seoteam') {
            return $db->table('notifications n')
                     ->select('n.*, u.username, s.name as seo_name, s.id as seo_profile_id')
                     ->join('users u', 'u.id = n.user_id')
                     ->join('seo_profiles s', 's.id = n.seo_id')
                     ->join('auth_groups_users agu', 'agu.user_id = u.id')
                     ->where('agu.group', $group)
                     ->orderBy('n.created_at', 'DESC')
                     ->limit($limit)
                     ->get()
                     ->getResultArray();
        }
        
        return [];
    }

    /**
     * Get unread notifications count for user
     */
    public function unreadCountForUser(int $userId): int
    {
        return $this->where(['user_id' => $userId, 'is_read' => 0])->countAllResults();
    }

    /**
     * Get unread notifications for user
     */
    public function getUnreadForUser(int $userId, int $limit = 10)
    {
        return $this->where(['user_id' => $userId, 'is_read' => 0])
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Get all notifications for user
     */
    public function getForUser(int $userId, int $limit = 20)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): bool
    {
        try {
            log_message('info', "Marking notification {$id} as read");
            $result = $this->update($id, [
                'is_read' => 1, 
                'read_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($result) {
                log_message('info', "Notification {$id} marked as read successfully");
            } else {
                log_message('error', "Failed to mark notification {$id} as read");
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', "Error marking notification {$id} as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead(int $userId): bool
    {
        try {
            log_message('info', "Marking all notifications as read for user {$userId}");
            $result = $this->where('user_id', $userId)
                          ->where('is_read', 0)
                          ->set([
                              'is_read' => 1,
                              'read_at' => date('Y-m-d H:i:s'),
                              'updated_at' => date('Y-m-d H:i:s')
                          ])
                          ->update();

            if ($result) {
                log_message('info', "Successfully marked all notifications as read for user {$userId}");
            } else {
                log_message('error', "Failed to mark all notifications as read for user {$userId}");
            }

            return $result;
        } catch (\Exception $e) {
            log_message('error', "Error marking all notifications as read for user {$userId}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Create notification for multiple users dengan relasi profil
     */
    public function createForUsersWithProfiles(array $usersData, string $title, string $message, string $type = 'general'): bool
    {
        try {
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            log_message('info', "Creating notifications for " . count($usersData) . " users: {$title}");

            foreach ($usersData as $user) {
                $notificationData = [
                    'user_id' => $user['user_id'],
                    'admin_id' => $user['admin_id'] ?? null,
                    'seo_id' => $user['seo_id'] ?? null,
                    'vendor_id' => $user['vendor_id'] ?? null,
                    'type' => $type,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];

                $notifications[] = $notificationData;
            }

            $result = $this->insertBatch($notifications);
            
            if ($result) {
                log_message('info', "Successfully created " . count($usersData) . " notifications with profile relations");
            } else {
                log_message('error', "Failed to create batch notifications with profile relations");
            }

            return $result;
        } catch (\Throwable $e) {
            log_message('error', 'Failed to create notifications with profiles: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create commission change notification for all Admin and Seoteam users dengan relasi profil
     */
    public function createCommissionChangeNotificationWithProfiles($vendorId, $vendorName, $oldCommission, $newCommission)
    {
        try {
            $db = \Config\Database::connect();
            
            // Get all Admin users dengan admin_profiles
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id, agu.group, u.username, ap.id as admin_id, NULL as seo_id')
                ->join('users u', 'u.id = agu.user_id')
                ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
                ->where('agu.group', 'admin')
                ->get()
                ->getResultArray();

            // Get all Seoteam users dengan seo_profiles
            $seoteamUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id, agu.group, u.username, NULL as admin_id, sp.id as seo_id')
                ->join('users u', 'u.id = agu.user_id')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                ->where('agu.group', 'seoteam')
                ->get()
                ->getResultArray();

            $targetUsers = array_merge($adminUsers, $seoteamUsers);

            if (empty($targetUsers)) {
                log_message('error', 'No Admin or Seoteam users found for commission change notification');
                return false;
            }

            $notifications = [];
            $now = date('Y-m-d H:i:s');

            foreach ($targetUsers as $user) {
                $notifications[] = [
                    'user_id' => $user['user_id'],
                    'admin_id' => $user['admin_id'],
                    'seo_id' => $user['seo_id'],
                    'vendor_id' => $vendorId,
                    'type' => 'commission_change',
                    'title' => 'Pengajuan Komisi Baru',
                    'message' => "Vendor <strong>{$vendorName}</strong> mengajukan perubahan komisi dari {$oldCommission} menjadi {$newCommission}. Silakan review pengajuan ini.",
                    'is_read' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            $result = $this->insertBatch($notifications);
            
            if ($result) {
                log_message('info', "Successfully created commission change notifications for " . count($targetUsers) . " users with profile relations");
            } else {
                log_message('error', "Failed to create commission change notifications with profile relations");
            }

            return $result;

        } catch (\Throwable $e) {
            log_message('error', 'Error creating commission change notifications with profiles: ' . $e->getMessage());
            return false;
        }
    }
}