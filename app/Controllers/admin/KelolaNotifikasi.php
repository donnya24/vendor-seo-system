<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NotificationsModel;
use App\Models\ActivityLogsModel;
use App\Models\AdminProfileModel;
use App\Models\VendorProfilesModel;
use App\Models\SeoProfilesModel;
use App\Config\AdminHeaderTrait;

class KelolaNotifikasi extends BaseController
{
    use AdminHeaderTrait;
    
    protected $db;
    protected $notificationsModel;
    protected $activityLogsModel;
    protected $adminProfileModel;
    protected $vendorProfilesModel;
    protected $seoProfilesModel;

    public function __construct()
    {
        $this->db = db_connect();
        $this->notificationsModel = new NotificationsModel();
        $this->activityLogsModel = new ActivityLogsModel();
        $this->adminProfileModel = new AdminProfileModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->seoProfilesModel = new SeoProfilesModel();

        // Load helpers
        helper(['url', 'form']);
    }

    protected function currentUser(): ?\CodeIgniter\Shield\Entities\User
    {
        return service('auth')->user();
    }

    protected function currentAdminId(): int
    {
        $user = $this->currentUser();
        if (!$user) return 0;

        $ap = $this->adminProfileModel
            ->where('user_id', (int) $user->id)
            ->first();

        return (int)($ap['id'] ?? 0);
    }

    public function index()
    {
        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        // Get filters
        $search = $this->request->getGet('search');
        $filter = $this->request->getGet('filter');
        $type   = $this->request->getGet('type');

        // Build query untuk notifications dengan perbaikan join
        $builder = $this->db->table('notifications n')
            ->select("
                n.*,
                u.username,
                agu.group as user_group,
                ap.name as admin_name,
                vp.business_name as vendor_name,
                sp.name as seo_name
            ")
            ->join('users u', 'u.id = n.user_id', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
            ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left')
            ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
            ->orderBy('n.created_at', 'DESC');

        // Apply filters
        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('ap.name', $search)
                ->orLike('vp.business_name', $search)
                ->orLike('sp.name', $search)
                ->orLike('n.title', $search)
                ->orLike('n.message', $search)
                ->orLike('n.type', $search)
                ->groupEnd();
        }

        if (!empty($filter)) {
            switch ($filter) {
                case 'read':
                    $builder->where('n.is_read', 1);
                    break;
                case 'unread':
                    $builder->where('n.is_read', 0);
                    break;
                case 'system':
                    $builder->where('n.type', 'system');
                    break;
                case 'announcement':
                    $builder->where('n.type', 'announcement');
                    break;
                case 'vendor':
                    $builder->where('n.vendor_id IS NOT NULL OR agu.group = "vendor"');
                    break;
                case 'seo':
                    $builder->where('n.seo_id IS NOT NULL OR agu.group = "seoteam"');
                    break;
            }
        }

        $notifications = $builder->get()->getResultArray();

        // Proses setiap notifikasi untuk memastikan informasi user lengkap
        foreach ($notifications as &$notif) {
            // Jika admin_id sudah terisi tapi admin_name kosong, ambil dari admin_profiles
            if (!empty($notif['admin_id']) && empty($notif['admin_name'])) {
                $adminProfile = $this->adminProfileModel->find($notif['admin_id']);
                if ($adminProfile) {
                    $notif['admin_name'] = $adminProfile['name'];
                }
            }
            
            // Jika vendor_id sudah terisi tapi vendor_name kosong, ambil dari vendor_profiles
            if (!empty($notif['vendor_id']) && empty($notif['vendor_name'])) {
                $vendorProfile = $this->vendorProfilesModel->find($notif['vendor_id']);
                if ($vendorProfile) {
                    $notif['vendor_name'] = $vendorProfile['business_name'];
                }
            }
            
            // Jika seo_id sudah terisi tapi seo_name kosong, ambil dari seo_profiles
            if (!empty($notif['seo_id']) && empty($notif['seo_name'])) {
                $seoProfile = $this->seoProfilesModel->find($notif['seo_id']);
                if ($seoProfile) {
                    $notif['seo_name'] = $seoProfile['name'];
                }
            }
            
            // Jika hanya user_id yang terisi, cek grup user dan ambil nama profilnya
            if (empty($notif['admin_name']) && empty($notif['vendor_name']) && empty($notif['seo_name']) && !empty($notif['user_group'])) {
                if ($notif['user_group'] === 'admin') {
                    $adminProfile = $this->adminProfileModel->getByUserId($notif['user_id']);
                    if ($adminProfile) {
                        $notif['admin_name'] = $adminProfile['name'];
                    }
                } elseif ($notif['user_group'] === 'vendor') {
                    $vendorProfile = $this->vendorProfilesModel->getByUserId($notif['user_id']);
                    if ($vendorProfile) {
                        $notif['vendor_name'] = $vendorProfile['business_name'];
                    }
                } elseif ($notif['user_group'] === 'seoteam') {
                    $seoProfile = $this->seoProfilesModel->getByUserId($notif['user_id']);
                    if ($seoProfile) {
                        $notif['seo_name'] = $seoProfile['name'];
                    }
                }
            }
        }

        // Get stats
        $stats = [
            'total_notifications' => $this->db->table('notifications')->countAllResults(),
            'unread_count' => $this->db->table('notifications')->where('is_read', 0)->countAllResults(),
            'user_state_count' => $this->db->table('notification_user_state')->where('hidden', 1)->countAllResults()
        ];

        $commonData = $this->loadCommonData();
        $headerNotifications = $commonData['notifications'];
        $headerUnread = $commonData['unread'];
        unset($commonData['notifications']);
        unset($commonData['unread']);

        $this->logActivity($userId, null, 'view_notification_management', 'success', 'Melihat kelola notifikasi', [
            'notifications_count' => count($notifications),
            'filters' => compact('search', 'filter')
        ]);

        $viewData = array_merge($commonData, [
            'title' => 'Kelola Notifikasi',
            'notifications' => $notifications,
            'headerNotifications' => $headerNotifications,
            'headerUnread' => $headerUnread,
            'totalNotifications' => count($notifications),
            'search' => $search,
            'filter' => $filter,
            'type' => $type,
            'stats' => $stats,
        ]);

        return view('admin/kelola_notifikasi/index', $viewData);
    }


    public function userState()
    {
        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        // Get filters
        $search = $this->request->getGet('search');
        $filter = $this->request->getGet('filter');

        // Get hidden notifications from notification_user_state
        $builder = $this->db->table('notification_user_state nus')
            ->select("
                nus.*,
                n.title,
                n.message,
                n.type,
                n.created_at,
                u.username,
                agu.group as user_group,
                ap.name as admin_name,
                vp.business_name as vendor_name,
                sp.name as seo_name
            ")
            ->join('notifications n', 'n.id = nus.notification_id')
            ->join('users u', 'u.id = n.user_id', 'left')
            ->join('auth_groups_users agu', 'agu.user_id = u.id', 'left')
            ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
            ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left')
            ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
            ->where('nus.hidden', 1);

        // Apply filters
        if (!empty($search)) {
            $builder->groupStart()
                ->like('u.username', $search)
                ->orLike('ap.name', $search)
                ->orLike('vp.business_name', $search)
                ->orLike('sp.name', $search)
                ->orLike('n.title', $search)
                ->orLike('n.message', $search)
                ->orLike('n.type', $search)
                ->groupEnd();
        }

        if (!empty($filter)) {
            switch ($filter) {
                case 'read':
                    $builder->where('nus.is_read', 1);
                    break;
                case 'unread':
                    $builder->where('nus.is_read', 0);
                    break;
                case 'hidden':
                    // Already filtered by hidden=1
                    break;
            }
        }

        $builder->orderBy('nus.hidden_at', 'DESC');

        $hiddenNotifications = $builder->get()->getResultArray();

        // Proses setiap notifikasi untuk memastikan informasi user lengkap
        foreach ($hiddenNotifications as &$notif) {
            // Jika admin_id sudah terisi tapi admin_name kosong, ambil dari admin_profiles
            if (!empty($notif['admin_id']) && empty($notif['admin_name'])) {
                $adminProfile = $this->adminProfileModel->find($notif['admin_id']);
                if ($adminProfile) {
                    $notif['admin_name'] = $adminProfile['name'];
                }
            }
            
            // Jika vendor_id sudah terisi tapi vendor_name kosong, ambil dari vendor_profiles
            if (!empty($notif['vendor_id']) && empty($notif['vendor_name'])) {
                $vendorProfile = $this->vendorProfilesModel->find($notif['vendor_id']);
                if ($vendorProfile) {
                    $notif['vendor_name'] = $vendorProfile['business_name'];
                }
            }
            
            // Jika seo_id sudah terisi tapi seo_name kosong, ambil dari seo_profiles
            if (!empty($notif['seo_id']) && empty($notif['seo_name'])) {
                $seoProfile = $this->seoProfilesModel->find($notif['seo_id']);
                if ($seoProfile) {
                    $notif['seo_name'] = $seoProfile['name'];
                }
            }
            
            // Jika hanya user_id yang terisi, cek grup user dan ambil nama profilnya
            if (empty($notif['admin_name']) && empty($notif['vendor_name']) && empty($notif['seo_name']) && !empty($notif['user_group'])) {
                if ($notif['user_group'] === 'admin') {
                    $adminProfile = $this->adminProfileModel->getByUserId($notif['user_id']);
                    if ($adminProfile) {
                        $notif['admin_name'] = $adminProfile['name'];
                    }
                } elseif ($notif['user_group'] === 'vendor') {
                    $vendorProfile = $this->vendorProfilesModel->getByUserId($notif['user_id']);
                    if ($vendorProfile) {
                        $notif['vendor_name'] = $vendorProfile['business_name'];
                    }
                } elseif ($notif['user_group'] === 'seoteam') {
                    $seoProfile = $this->seoProfilesModel->getByUserId($notif['user_id']);
                    if ($seoProfile) {
                        $notif['seo_name'] = $seoProfile['name'];
                    }
                }
            }
        }

        // Get SEO profiles for filter
        $seoProfiles = $this->db->table('seo_profiles')
            ->select('id, name')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();

        // Get stats
        $stats = [
            'total_notifications' => $this->db->table('notifications')->countAllResults(),
            'unread_count' => $this->db->table('notifications')->where('is_read', 0)->countAllResults(),
            'user_state_count' => $this->db->table('notification_user_state')->where('hidden', 1)->countAllResults()
        ];

        $commonData = $this->loadCommonData();
        $headerNotifications = $commonData['notifications'];
        $headerUnread = $commonData['unread'];
        unset($commonData['notifications']);
        unset($commonData['unread']);

        $this->logActivity($userId, null, 'view_notification_user_state', 'success', 'Melihat user notification state', [
            'hidden_count' => count($hiddenNotifications)
        ]);

        $viewData = array_merge($commonData, [
            'title' => 'Notifikasi Terhapus',
            'notifications' => $hiddenNotifications,
            'headerNotifications' => $headerNotifications,
            'headerUnread' => $headerUnread,
            'totalNotifications' => count($hiddenNotifications),
            'search' => $search,
            'filter' => $filter,
            'seoProfiles' => $seoProfiles,
            'stats' => $stats,
        ]);

        return view('admin/kelola_notifikasi/user_state', $viewData);
    }

    public function create()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        // Get data for dropdown
        $seoProfiles = $this->db->table('seo_profiles')
            ->select('id, name, user_id')
            ->orderBy('name', 'ASC')
            ->get()
            ->getResultArray();
            
        $vendorProfiles = $this->db->table('vendor_profiles')
            ->select('id, business_name, owner_name, user_id')
            ->orderBy('business_name', 'ASC')
            ->get()
            ->getResultArray();

        // Return modal form HTML
        $html = view('admin/kelola_notifikasi/create_modal', [
            'seoProfiles' => $seoProfiles,
            'vendorProfiles' => $vendorProfiles,
            'userModel' => model('UserModel')
        ]);
        
        return $this->response->setJSON([
            'status' => 'success',
            'html' => $html,
            'csrf_token' => csrf_hash(),
            'csrf_name' => csrf_token()
        ]);
    }

    public function store()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $validation = \Config\Services::validation();
        $validation->setRules([
            'user_id' => 'required',
            'title'   => 'required|max_length[255]',
            'message' => 'required',
            'type'    => 'required|in_list[commission,announcement,system]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $validation->getErrors(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $data = $this->request->getPost();
        $targetUserId = $data['user_id'];

        try {
            // Check if sending to all users except admin
            if ($targetUserId === 'all') {
                // Get all users except admin
                $users = $this->db->table('users u')
                    ->select('u.id, agu.group')
                    ->join('auth_groups_users agu', 'agu.user_id = u.id')
                    ->where('agu.group !=', 'admin')
                    ->get()
                    ->getResultArray();
                
                $successCount = 0;
                
                foreach ($users as $userProfile) {
                    // Prepare notification data for each user
                    $notificationData = [
                        'user_id' => $userProfile['id'],
                        'type' => $data['type'],
                        'title' => $data['title'],
                        'message' => $data['message'],
                        'is_read' => isset($data['is_read']) ? 1 : 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    // Set appropriate IDs based on user group
                    if ($userProfile['group'] === 'seoteam') {
                        $seoProfile = $this->seoProfilesModel->getByUserId($userProfile['id']);
                        $notificationData['seo_id'] = $seoProfile['id'] ?? null;
                    } elseif ($userProfile['group'] === 'vendor') {
                        $vendorProfile = $this->vendorProfilesModel->getByUserId($userProfile['id']);
                        $notificationData['vendor_id'] = $vendorProfile['id'] ?? null;
                    }

                    // Insert notification for each user
                    $this->db->table('notifications')->insert($notificationData);
                    $successCount++;
                }
                
                $this->logActivity($userId, null, 'create_bulk_notification', 'success', 'Membuat notifikasi untuk semua user (kecuali admin)', [
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'success_count' => $successCount
                ]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => "Notifikasi berhasil dikirim ke {$successCount} user",
                    'csrf_token' => csrf_hash(),
                    'csrf_name' => csrf_token()
                ]);
            } else {
                // Original code for single user
                // Get user profile info
                $userProfile = $this->db->table('users u')
                    ->select('u.id, u.username, agu.group')
                    ->join('auth_groups_users agu', 'agu.user_id = u.id')
                    ->where('u.id', $targetUserId)
                    ->get()
                    ->getRowArray();

                if (!$userProfile) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'User tidak ditemukan',
                        'csrf_token' => csrf_hash(),
                        'csrf_name' => csrf_token()
                    ]);
                }

                // Prepare notification data
                $notificationData = [
                    'user_id' => $targetUserId,
                    'type' => $data['type'],
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'is_read' => isset($data['is_read']) ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];

                // Set appropriate IDs based on user group
                if ($userProfile['group'] === 'admin') {
                    $adminProfile = $this->adminProfileModel->getByUserId($targetUserId);
                    $notificationData['admin_id'] = $adminProfile['id'] ?? null;
                } elseif ($userProfile['group'] === 'seoteam') {
                    $seoProfile = $this->seoProfilesModel->getByUserId($targetUserId);
                    $notificationData['seo_id'] = $seoProfile['id'] ?? null;
                } elseif ($userProfile['group'] === 'vendor') {
                    $vendorProfile = $this->vendorProfilesModel->getByUserId($targetUserId);
                    $notificationData['vendor_id'] = $vendorProfile['id'] ?? null;
                }

                // Insert notification
                $this->db->table('notifications')->insert($notificationData);

                $this->logActivity($userId, null, 'create_notification', 'success', 'Membuat notifikasi baru', [
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'target_user' => $targetUserId
                ]);

                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Notifikasi berhasil dibuat',
                    'csrf_token' => csrf_hash(),
                    'csrf_name' => csrf_token()
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to create notification: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal membuat notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    public function edit($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $notification = $this->db->table('notifications')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$notification) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        // Return modal form HTML
        $html = view('admin/kelola_notifikasi/edit_modal', ['notification' => $notification]);
        return $this->response->setJSON([
            'status' => 'success',
            'html' => $html,
            'csrf_token' => csrf_hash(),
            'csrf_name' => csrf_token()
        ]);
    }

    public function update($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $notification = $this->db->table('notifications')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$notification) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $validation = \Config\Services::validation();
        $validation->setRules([
            'title'   => 'required|max_length[255]',
            'message' => 'required',
            'type'    => 'required|in_list[commission,announcement,system]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors'  => $validation->getErrors(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $data = $this->request->getPost();

        try {
            $this->db->table('notifications')
                ->where('id', $id)
                ->update([
                    'title' => $data['title'],
                    'message' => $data['message'],
                    'type' => $data['type'],
                    'is_read' => isset($data['is_read']) ? 1 : 0,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

            $this->logActivity($userId, null, 'edit_notification', 'success', 'Mengedit notifikasi', [
                'notification_id' => $id,
                'title' => $data['title']
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notifikasi berhasil diupdate',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to update notification: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengupdate notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    // Untuk admin, hapus langsung dari database
    public function delete($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        $notification = $this->db->table('notifications')
            ->where('id', $id)
            ->get()
            ->getRowArray();

        if (!$notification) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        try {
            // Hapus langsung dari database untuk admin
            $this->db->table('notifications')->where('id', $id)->delete();
            
            // Hapus juga dari notification_user_state jika ada
            $this->db->table('notification_user_state')->where('notification_id', $id)->delete();

            $this->logActivity($userId, null, 'delete_notification', 'success', 'Menghapus notifikasi', [
                'notification_id' => $id,
                'title' => $notification['title']
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notifikasi berhasil dihapus',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete notification: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    // Untuk admin, hapus langsung dari database
    public function deleteSelected()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        // Ambil data dari POST, bukan JSON
        $ids = $this->request->getPost('ids');
        
        if (empty($ids)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak ada notifikasi yang dipilih',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        // Jika ids adalah string JSON, decode dulu
        if (is_string($ids)) {
            $ids = json_decode($ids);
        }
        
        if (!is_array($ids) || empty($ids)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Format ID tidak valid',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        try {
            $successCount = 0;
            $errorCount = 0;

            foreach ($ids as $id) {
                $notification = $this->db->table('notifications')
                    ->where('id', $id)
                    ->get()
                    ->getRowArray();

                if (!$notification) {
                    $errorCount++;
                    continue;
                }

                // Hapus langsung dari database untuk admin
                $this->db->table('notifications')->where('id', $id)->delete();
                
                // Hapus juga dari notification_user_state jika ada
                $this->db->table('notification_user_state')->where('notification_id', $id)->delete();

                $successCount++;
            }

            $this->logActivity($userId, null, 'delete_selected_notifications', 'success', 'Menghapus notifikasi yang dipilih', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_ids' => count($ids)
            ]);

            if ($errorCount > 0) {
                return $this->response->setJSON([
                    'status' => 'warning',
                    'message' => "{$successCount} notifikasi berhasil dihapus, {$errorCount} gagal",
                    'csrf_token' => csrf_hash(),
                    'csrf_name' => csrf_token()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "{$successCount} notifikasi berhasil dihapus",
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete selected notifications: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus notifikasi yang dipilih: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    public function restore($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        try {
            $this->db->table('notification_user_state')
                ->where('notification_id', $id)
                ->where('user_id', $userId)
                ->update([
                    'hidden' => 0,
                    'hidden_at' => null
                ]);

            $this->logActivity($userId, null, 'restore_notification', 'success', 'Mengembalikan notifikasi', [
                'notification_id' => $id
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notifikasi berhasil dikembalikan',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to restore notification: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengembalikan notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    public function deleteAll()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        try {
            // Hapus langsung dari database untuk admin
            $this->db->table('notifications')->emptyTable();
            
            // Hapus juga dari notification_user_state
            $this->db->table('notification_user_state')->emptyTable();

            $this->logActivity($userId, null, 'delete_all_notifications', 'success', 'Menghapus semua notifikasi', []);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Semua notifikasi berhasil dihapus',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to delete all notifications: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus semua notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    public function restoreAll()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        try {
            $this->db->table('notification_user_state')
                ->where('user_id', $userId)
                ->update([
                    'hidden' => 0,
                    'hidden_at' => null
                ]);

            $this->logActivity($userId, null, 'restore_all_notifications', 'success', 'Mengembalikan semua notifikasi', []);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Semua notifikasi berhasil dikembalikan',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to restore all notifications: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal mengembalikan semua notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    public function deletePermanent($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        try {
            // Delete from notification_user_state
            $this->db->table('notification_user_state')
                ->where('notification_id', $id)
                ->delete();

            // Delete from notifications
            $this->db->table('notifications')
                ->where('id', $id)
                ->delete();

            $this->logActivity($userId, null, 'delete_permanent_notification', 'success', 'Menghapus permanen notifikasi', [
                'notification_id' => $id
            ]);

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Notifikasi berhasil dihapus permanen',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to permanently delete notification: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus permanen notifikasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    // Perbaikan method deleteSelectedPermanent
    public function deleteSelectedPermanent()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $user   = $this->currentUser();
        $userId = (int) ($user?->id ?? 0);

        // Ambil data dari POST, bukan JSON
        $ids = $this->request->getPost('ids');
        
        if (empty($ids)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Tidak ada notifikasi yang dipilih',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        // Jika ids adalah string JSON, decode dulu
        if (is_string($ids)) {
            $ids = json_decode($ids);
        }
        
        if (!is_array($ids) || empty($ids)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Format ID tidak valid',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        try {
            $successCount = 0;
            $errorCount = 0;

            foreach ($ids as $id) {
                // Check if notification exists
                $notification = $this->db->table('notifications')
                    ->where('id', $id)
                    ->get()
                    ->getRowArray();

                if (!$notification) {
                    $errorCount++;
                    continue;
                }

                // Delete from notification_user_state
                $this->db->table('notification_user_state')
                    ->where('notification_id', $id)
                    ->delete();

                // Delete from notifications
                $this->db->table('notifications')
                    ->where('id', $id)
                    ->delete();

                $successCount++;
            }

            $this->logActivity($userId, null, 'delete_selected_permanent_notifications', 'success', 'Menghapus permanen notifikasi yang dipilih', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_ids' => count($ids)
            ]);

            if ($errorCount > 0) {
                return $this->response->setJSON([
                    'status' => 'warning',
                    'message' => "{$successCount} notifikasi berhasil dihapus permanen, {$errorCount} gagal",
                    'csrf_token' => csrf_hash(),
                    'csrf_name' => csrf_token()
                ]);
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => "{$successCount} notifikasi berhasil dihapus permanen",
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Failed to permanently delete selected notifications: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Gagal menghapus permanen notifikasi yang dipilih: ' . $e->getMessage(),
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }
    }

    public function getNotification($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $notification = $this->db->table('notifications n')
            ->select("
                n.*,
                u.username,
                ap.name as admin_name,
                vp.business_name as vendor_name,
                sp.name as seo_name
            ")
            ->join('users u', 'u.id = n.user_id', 'left')
            ->join('admin_profiles ap', 'ap.user_id = u.id AND n.admin_id IS NOT NULL', 'left')
            ->join('vendor_profiles vp', 'vp.user_id = u.id AND n.vendor_id IS NOT NULL', 'left')
            ->join('seo_profiles sp', 'sp.user_id = u.id AND n.seo_id IS NOT NULL', 'left')
            ->where('n.id', $id)
            ->get()
            ->getRowArray();

        if (!$notification) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Notifikasi tidak ditemukan',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $notification,
            'csrf_token' => csrf_hash(),
            'csrf_name' => csrf_token()
        ]);
    }

    public function getUserList()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Method not allowed',
                'csrf_token' => csrf_hash(),
                'csrf_name' => csrf_token()
            ]);
        }

        $users = $this->db->table('users u')
            ->select('
                u.id,
                u.username,
                agu.group,
                CASE 
                    WHEN agu.group = "admin" THEN ap.name
                    WHEN agu.group = "seoteam" THEN sp.name
                    WHEN agu.group = "vendor" THEN vp.business_name
                    ELSE u.username
                END as display_name
            ')
            ->join('auth_groups_users agu', 'agu.user_id = u.id')
            ->join('admin_profiles ap', 'ap.user_id = u.id AND agu.group = "admin"', 'left')
            ->join('seo_profiles sp', 'sp.user_id = u.id AND agu.group = "seoteam"', 'left')
            ->join('vendor_profiles vp', 'vp.user_id = u.id AND agu.group = "vendor"', 'left')
            ->orderBy('agu.group', 'ASC')
            ->orderBy('display_name', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 'success',
            'users' => $users,
            'csrf_token' => csrf_hash(),
            'csrf_name' => csrf_token()
        ]);
    }

    private function logActivity($userId = null, $adminId = null, $action = null, $status = null, $description = null, $additionalData = [])
    {
        try {
            $data = [
                'user_id'     => $userId,
                'admin_id'    => $adminId,
                'module'      => 'kelola_notifikasi',
                'action'      => $action,
                'status'      => $status,
                'description' => $description,
                'ip_address'  => $this->request->getIPAddress(),
                'user_agent'  => $this->request->getUserAgent(),
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            if (!empty($additionalData)) {
                $data['additional_data'] = json_encode($additionalData);
            }
            $this->activityLogsModel->insert($data);
        } catch (\Throwable $e) {
            log_message('error', 'Failed to log activity in KelolaNotifikasi: ' . $e->getMessage());
        }
    }
}