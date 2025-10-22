<?php

namespace App\Config;

use App\Models\NotificationsModel;
use App\Models\AdminProfileModel;

trait AdminHeaderTrait
{
    protected $notificationsModel;
    protected $adminProfileModel;
    
    /**
     * Load common data needed for admin header
     */
    protected function loadCommonData()
    {
        // Get current user
        $user = service('auth')->user();
        $userId = $user ? $user->id : null;
        
        // Load notifications
        $notifications = [];
        $unreadCount = 0;
        
        if ($userId) {
            $this->notificationsModel = new NotificationsModel();
            $this->adminProfileModel = new AdminProfileModel();
            
            $notifications = $this->notificationsModel->getForUser($userId, 20);
            $unreadCount = $this->notificationsModel->unreadCountForUser($userId);
            
            // Format notifications for header
            $formattedNotifications = [];
            foreach ($notifications as $notification) {
                $formattedNotifications[] = [
                    'id' => $notification['id'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'is_read' => $notification['is_read'],
                    'date' => date('d M Y H:i', strtotime($notification['created_at'])),
                    'created_at' => $notification['created_at']
                ];
            }
            $notifications = $formattedNotifications;
        }
        
        // Get admin profile
        $adminProfile = $this->adminProfileModel->where('user_id', $userId)->first();
        
        // Determine admin name
        $adminName = $adminProfile['name'] ?? 
                    ($user->username ?? 
                    (session('user_name') ?? 
                    'Admin'));
        
        // Profile image
        $profileImage = $adminProfile['profile_image'] ?? '';
        $profileOnDisk = $profileImage ? (FCPATH . 'uploads/admin_profiles/' . $profileImage) : '';
        $profileImagePath = ($profileImage && is_file($profileOnDisk))
            ? base_url('uploads/admin_profiles/' . $profileImage)
            : base_url('assets/img/default-avatar.png');
        
        return [
            'notifications' => $notifications,
            'unread' => $unreadCount,
            'adminName' => $adminName,
            'profileImage' => $profileImage,
            'profileImagePath' => $profileImagePath,
            'profileOnDisk' => $profileOnDisk,
            'user' => $user
        ];
    }
}