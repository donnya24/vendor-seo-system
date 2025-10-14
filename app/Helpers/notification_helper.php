<?php

use App\Models\NotificationsModel;
use App\Models\UserModel;

if (!function_exists('create_commission_notification')) {
    /**
     * Helper untuk membuat notifikasi komisi perubahan untuk semua Admin dan Seoteam dengan relasi profil
     */
    function create_commission_notification($vendorId, $vendorName, $oldCommission, $newCommission)
    {
        $notificationModel = new NotificationsModel();
        
        return $notificationModel->createCommissionChangeNotificationWithProfiles($vendorId, $vendorName, $oldCommission, $newCommission);
    }
}

if (!function_exists('get_admin_seoteam_users_with_profiles')) {
    /**
     * Helper untuk mendapatkan semua user Admin dan Seoteam dengan relasi profil
     */
    function get_admin_seoteam_users_with_profiles()
    {
        $db = \Config\Database::connect();
        
        // Admin users dengan admin_profiles
        $adminUsers = $db->table('auth_groups_users agu')
                 ->select('agu.user_id, agu.group, u.email, u.username, ap.id as admin_id, ap.name as admin_name')
                 ->join('users u', 'u.id = agu.user_id')
                 ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
                 ->where('agu.group', 'admin')
                 ->get()
                 ->getResultArray();

        // Seoteam users dengan seo_profiles
        $seoteamUsers = $db->table('auth_groups_users agu')
                 ->select('agu.user_id, agu.group, u.email, u.username, sp.id as seo_id, sp.name as seo_name')
                 ->join('users u', 'u.id = agu.user_id')
                 ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                 ->where('agu.group', 'seoteam')
                 ->get()
                 ->getResultArray();

        return array_merge($adminUsers, $seoteamUsers);
    }
}

if (!function_exists('create_notification_for_group_with_profiles')) {
    /**
     * Helper untuk membuat notifikasi untuk grup tertentu dengan relasi profil
     */
    function create_notification_for_group_with_profiles($group, $title, $message, $linkUrl = '', $type = 'general')
    {
        $notificationModel = new NotificationsModel();
        $db = \Config\Database::connect();
        
        if ($group === 'admin') {
            $targetUsers = $db->table('auth_groups_users agu')
                             ->select('agu.user_id, ap.id as admin_id')
                             ->join('admin_profiles ap', 'ap.user_id = agu.user_id')
                             ->where('agu.group', $group)
                             ->get()
                             ->getResultArray();
        } else if ($group === 'seoteam') {
            $targetUsers = $db->table('auth_groups_users agu')
                             ->select('agu.user_id, sp.id as seo_id')
                             ->join('seo_profiles sp', 'sp.user_id = agu.user_id')
                             ->where('agu.group', $group)
                             ->get()
                             ->getResultArray();
        } else {
            return false;
        }
        
        if (empty($targetUsers)) {
            log_message('error', "No users found for group: {$group}");
            return false;
        }

        return $notificationModel->createForUsersWithProfiles($targetUsers, $title, $message, $linkUrl, $type);
    }
}

if (!function_exists('format_commission_value')) {
    /**
     * Helper untuk format nilai komisi
     */
    function format_commission_value($type, $value)
    {
        if (empty($value) || $value == 0) {
            return 'Belum diatur';
        }
        
        if ($type === 'percent') {
            return number_format((float)$value, 1) . '%';
        } else {
            return 'Rp ' . number_format((float)$value, 0, ',', '.');
        }
    }
}

if (!function_exists('get_user_profile_id')) {
    /**
     * Helper untuk mendapatkan profile ID berdasarkan user_id dan group
     */
    function get_user_profile_id($userId, $group)
    {
        $db = \Config\Database::connect();
        
        if ($group === 'admin') {
            $result = $db->table('admin_profiles')
                        ->select('id')
                        ->where('user_id', $userId)
                        ->get()
                        ->getRow();
            return $result ? $result->id : null;
        } else if ($group === 'seoteam') {
            $result = $db->table('seo_profiles')
                        ->select('id')
                        ->where('user_id', $userId)
                        ->get()
                        ->getRow();
            return $result ? $result->id : null;
        }
        
        return null;
    }
}