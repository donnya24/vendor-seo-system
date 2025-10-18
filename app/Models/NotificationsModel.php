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

    // Validation rules yang lebih fleksibel
    protected $validationRules = [
        'user_id' => 'permit_empty|numeric',
        'admin_id' => 'permit_empty|numeric',
        'vendor_id' => 'permit_empty|numeric',
        'seo_id' => 'permit_empty|numeric',
        'type' => 'permit_empty|max_length[50]',
        'title' => 'required|max_length[255]',
        'message' => 'required'
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Judul notifikasi harus diisi',
            'max_length' => 'Judul notifikasi maksimal 255 karakter'
        ],
        'message' => [
            'required' => 'Pesan notifikasi harus diisi'
        ]
    ];

    /**
     * Get notifications with related data
     */
    public function getWithRelations($limit = 50)
    {
        $db = \Config\Database::connect();
        
        return $db->table('notifications n')
                ->select('n.*, 
                        u.username,
                        CASE 
                            WHEN n.admin_id IS NOT NULL THEN ap.name
                            WHEN n.seo_id IS NOT NULL THEN sp.name
                            WHEN n.vendor_id IS NOT NULL THEN vp.business_name
                            ELSE u.username
                        END as display_name,
                        CASE 
                            WHEN n.admin_id IS NOT NULL THEN "admin"
                            WHEN n.seo_id IS NOT NULL THEN "seo"
                            WHEN n.vendor_id IS NOT NULL THEN "vendor"
                            ELSE "user"
                        END as user_type')
                ->join('users u', 'u.id = n.user_id', 'left')
                ->join('admin_profiles ap', 'ap.user_id = u.id AND n.admin_id IS NOT NULL', 'left')
                ->join('vendor_profiles vp', 'vp.user_id = u.id AND n.vendor_id IS NOT NULL', 'left')
                ->join('seo_profiles sp', 'sp.user_id = u.id AND n.seo_id IS NOT NULL', 'left')
                ->orderBy('n.created_at', 'DESC')
                ->limit($limit)
                ->get()
                ->getResultArray();
    }

    /**
     * Get notifications by type (system atau announcement)
     */
    public function getByType($type, $limit = 50)
    {
        return $this->where('type', $type)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get system notifications
     */
    public function getSystemNotifications($limit = 50)
    {
        return $this->where('type', 'system')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get announcement notifications
     */
    public function getAnnouncementNotifications($limit = 50)
    {
        return $this->where('type', 'announcement')
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get notifications by admin_id
     */
    public function getByAdminId($adminId, $limit = 20)
    {
        return $this->where('admin_id', $adminId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get notifications by seo_id
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
    public function createForUsersWithProfiles(array $usersData, string $title, string $message, string $type = 'system'): bool
    {
        try {
            $notifications = [];
            $now = date('Y-m-d H:i:s');

            log_message('info', "Creating notifications for " . count($usersData) . " users: {$title}");

            foreach ($usersData as $user) {
                $notificationData = [
                    'user_id' => $user['user_id'] ?? null,
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
     * Create commission system notification
     */
    public function createCommissionSystemNotification($vendorId, $vendorName, $period, $amount, $action = 'create')
    {
        try {
            $db = \Config\Database::connect();
            
            // Get admin users
            $adminUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'admin')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();

            // Get SEO users
            $seoUsers = $db->table('auth_groups_users agu')
                ->select('agu.user_id')
                ->join('users u', 'u.id = agu.user_id')
                ->where('agu.group', 'seo')
                ->where('u.active', 1)
                ->get()
                ->getResultArray();

            $notifications = [];
            $now = date('Y-m-d H:i:s');

            $title = $action === 'create' ? 'Komisi Baru Diterima' : 'Komisi Diperbarui';
            $message = $action === 'create' 
                ? "📝 Vendor {$vendorName} mengirim komisi baru periode {$period} sebesar Rp {$amount}"
                : "✏️ Vendor {$vendorName} memperbarui komisi periode {$period} sebesar Rp {$amount}";

            $message .= "\n\nDetail Komisi:";
            $message .= "\n• Vendor: {$vendorName}";
            $message .= "\n• Periode: {$period}";
            $message .= "\n• Jumlah: Rp {$amount}";
            $message .= "\n• Status: Unpaid";

            // Untuk admin
            foreach ($adminUsers as $admin) {
                $notifications[] = [
                    'user_id' => null,
                    'vendor_id' => $vendorId,
                    'admin_id' => $admin['user_id'],
                    'seo_id' => null,
                    'type' => 'system',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            // Untuk SEO
            foreach ($seoUsers as $seo) {
                $notifications[] = [
                    'user_id' => null,
                    'vendor_id' => $vendorId,
                    'admin_id' => null,
                    'seo_id' => $seo['user_id'],
                    'type' => 'system',
                    'title' => $title,
                    'message' => $message,
                    'is_read' => 0,
                    'read_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now
                ];
            }

            $result = $this->insertBatch($notifications);
            
            if ($result) {
                log_message('info', "Successfully created commission system notifications for vendor {$vendorId}");
            } else {
                log_message('error', "Failed to create commission system notifications");
            }

            return $result;

        } catch (\Exception $e) {
            log_message('error', "Error creating commission system notification: " . $e->getMessage());
            return false;
        }
    }
}