<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\NotificationsModel;
use App\Models\NotificationsUserStateModel;
use App\Models\SeoProfilesModel;
use App\Models\VendorProfilesModel;
use App\Models\AdminProfileModel;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class KelolaNotifikasi extends BaseAdminController
{
    protected $notificationsModel;
    protected $userStateModel;
    protected $seoProfilesModel;
    protected $vendorProfilesModel;
    protected $adminProfileModel;
    protected $userModel;

    public function __construct()
    {
        $this->notificationsModel = new NotificationsModel();
        $this->userStateModel = new NotificationsUserStateModel();
        $this->seoProfilesModel = new SeoProfilesModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
        $this->adminProfileModel = new AdminProfileModel();
        $this->userModel = new ShieldUserModel();
    }

    public function index()
    {
        try {
            // Load common data for header
            $commonData = $this->loadCommonData();
            
            $filter = $this->request->getGet('filter') ?? '';
            $search = $this->request->getGet('search') ?? '';

            $notifications = $this->getFilteredNotifications($filter, $search);

            $data = [
                'title' => 'Kelola Notifikasi',
                'notifications' => $notifications,
                'filter' => $filter,
                'search' => $search,
                'totalNotifications' => count($notifications),
                'stats' => $this->getStats(),
                'page' => 'Kelola Notifikasi',
            ];

            return view('admin/kelola_notifikasi/index', array_merge($data, $commonData));
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data notifikasi');
        }
    }

    public function create()
    {
        try {
            // Load common data for header
            $commonData = $this->loadCommonData();
            
            // Get SEO profiles dan Vendor profiles untuk dropdown
            $seoProfiles = $this->seoProfilesModel->findAll();
            $vendorProfiles = $this->vendorProfilesModel->findAll();
            $adminProfiles = $this->adminProfileModel->findAll();
            $allUsers = $this->userModel->findAll();

            $data = [
                'title' => 'Buat Notifikasi Baru',
                'seoProfiles' => $seoProfiles,
                'vendorProfiles' => $vendorProfiles,
                'adminProfiles' => $adminProfiles,
                'allUsers' => $allUsers,
                'userModel' => $this->userModel // Pass userModel to view
            ];

            // Check if it's an AJAX request untuk modal
            if ($this->request->isAJAX()) {
                return view('admin/kelola_notifikasi/create_modal', $data);
            }

            return view('admin/kelola_notifikasi/create', array_merge($data, $commonData));
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::create: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat form notifikasi');
        }
    }

    public function store()
    {
        try {
            $validation = \Config\Services::validation();
            
            $validation->setRules([
                'user_id' => 'required|numeric',
                'title' => 'required|max_length[255]',
                'message' => 'required|max_length[1000]',
                'type' => 'required|in_list[system,announcement,commission]',
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Validasi gagal',
                        'errors' => $validation->getErrors()
                    ]);
                }
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Get user type
            $user = $this->userModel->find($this->request->getPost('user_id'));
            $userType = $user ? $user->user_type : 'user';
            
            // Prepare notification data
            $notificationData = [
                'title' => $this->request->getPost('title'),
                'message' => $this->request->getPost('message'),
                'type' => $this->request->getPost('type'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Set user_id based on user type
            if ($userType === 'admin') {
                $notificationData['admin_id'] = $this->request->getPost('user_id');
            } elseif ($userType === 'vendor') {
                $notificationData['vendor_id'] = $this->request->getPost('user_id');
            } elseif ($userType === 'seo') {
                $notificationData['seo_id'] = $this->request->getPost('user_id');
            } else {
                $notificationData['user_id'] = $this->request->getPost('user_id');
            }

            // Insert notification
            $notificationId = $this->notificationsModel->insert($notificationData);
            
            if ($notificationId) {
                // Create notification user state
                $userStateData = [
                    'notification_id' => $notificationId,
                    'user_id' => $this->request->getPost('user_id'),
                    'is_read' => 0,
                    'hidden' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                
                $this->userStateModel->insert($userStateData);
                
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Notifikasi berhasil dikirim'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('success', 'Notifikasi berhasil dikirim');
            } else {
                $errorMessage = 'Gagal mengirim notifikasi';
                log_message('error', $errorMessage);
                
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $errorMessage
                    ]);
                }
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::store: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan notifikasi');
        }
    }

    public function edit($id = null)
    {
        try {
            // Load common data for header
            $commonData = $this->loadCommonData();
            
            if ($id === null) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status' => 'error',
                    'message' => 'ID notifikasi diperlukan'
                ]);
            }

            // Get notification with user state
            $notification = $this->getNotificationWithUserState($id);
            
            if (!$notification) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Notifikasi tidak ditemukan'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Notifikasi tidak ditemukan');
            }

            // Get SEO profiles dan Vendor profiles untuk dropdown
            $seoProfiles = $this->seoProfilesModel->findAll();
            $vendorProfiles = $this->vendorProfilesModel->findAll();
            $adminProfiles = $this->adminProfileModel->findAll();
            $allUsers = $this->userModel->findAll();

            $data = [
                'title' => 'Edit Notifikasi',
                'notification' => $notification,
                'seoProfiles' => $seoProfiles,
                'vendorProfiles' => $vendorProfiles,
                'adminProfiles' => $adminProfiles,
                'allUsers' => $allUsers,
                'userModel' => $this->userModel // Pass userModel to view
            ];

            // Check if it's an AJAX request
            if ($this->request->isAJAX()) {
                return view('admin/kelola_notifikasi/edit_modal', $data);
            }

            return view('admin/kelola_notifikasi/edit', array_merge($data, $commonData));
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::edit: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat form edit notifikasi');
        }
    }

    public function update($id = null)
    {
        try {
            if ($id === null) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'ID notifikasi diperlukan'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'ID notifikasi diperlukan');
            }

            $notification = $this->notificationsModel->find($id);
            
            if (!$notification) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Notifikasi tidak ditemukan'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Notifikasi tidak ditemukan');
            }

            $validation = \Config\Services::validation();
            
            $validation->setRules([
                'user_id' => 'required|numeric',
                'title' => 'required|max_length[255]',
                'message' => 'required|max_length[1000]',
                'type' => 'required|in_list[system,announcement,commission]',
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Validasi gagal',
                        'errors' => $validation->getErrors()
                    ]);
                }
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Get user type
            $user = $this->userModel->find($this->request->getPost('user_id'));
            $userType = $user ? $user->user_type : 'user';
            
            // Prepare notification data
            $notificationData = [
                'title' => $this->request->getPost('title'),
                'message' => $this->request->getPost('message'),
                'type' => $this->request->getPost('type'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];
            
            // Set user_id based on user type
            if ($userType === 'admin') {
                $notificationData['admin_id'] = $this->request->getPost('user_id');
                $notificationData['user_id'] = null;
                $notificationData['vendor_id'] = null;
                $notificationData['seo_id'] = null;
            } elseif ($userType === 'vendor') {
                $notificationData['vendor_id'] = $this->request->getPost('user_id');
                $notificationData['user_id'] = null;
                $notificationData['admin_id'] = null;
                $notificationData['seo_id'] = null;
            } elseif ($userType === 'seo') {
                $notificationData['seo_id'] = $this->request->getPost('user_id');
                $notificationData['user_id'] = null;
                $notificationData['admin_id'] = null;
                $notificationData['vendor_id'] = null;
            } else {
                $notificationData['user_id'] = $this->request->getPost('user_id');
                $notificationData['admin_id'] = null;
                $notificationData['vendor_id'] = null;
                $notificationData['seo_id'] = null;
            }

            if ($this->notificationsModel->update($id, $notificationData)) {
                // Update user state if user changed
                $userState = $this->userStateModel->where('notification_id', $id)->first();
                if ($userState && $userState['user_id'] != $this->request->getPost('user_id')) {
                    $this->userStateModel->update($userState['id'], [
                        'user_id' => $this->request->getPost('user_id')
                    ]);
                }
                
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Notifikasi berhasil diperbarui'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('success', 'Notifikasi berhasil diperbarui');
            } else {
                $errorMessage = 'Gagal memperbarui notifikasi';
                log_message('error', $errorMessage);
                
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => $errorMessage
                    ]);
                }
                return redirect()->back()->withInput()->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::update: ' . $e->getMessage());
            
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Internal server error: ' . $e->getMessage()
                ]);
            }
            
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui notifikasi');
        }
    }

    public function getUsers()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(405)->setJSON([
                    'status' => 'error',
                    'message' => 'Method not allowed'
                ]);
            }

            // Get SEO profiles dan Vendor profiles untuk dropdown
            $seoProfiles = $this->seoProfilesModel->findAll();
            $vendorProfiles = $this->vendorProfilesModel->findAll();
            $adminProfiles = $this->adminProfileModel->findAll();
            $allUsers = $this->userModel->findAll();

            $data = [
                'seoProfiles' => $seoProfiles,
                'vendorProfiles' => $vendorProfiles,
                'adminProfiles' => $adminProfiles,
                'allUsers' => $allUsers
            ];

            return $this->response->setJSON($data);

        } catch (\Exception $e) {
            log_message('error', 'Error in getUsers: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'status' => 'error',
                'message' => 'Internal server error: ' . $e->getMessage()
            ]);
        }
    }

    public function delete($id = null)
    {
        try {
            if ($id === null) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'status' => 'error',
                        'message' => 'ID notifikasi diperlukan'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'ID notifikasi diperlukan');
            }

            $notification = $this->notificationsModel->find($id);
            
            if (!$notification) {
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(404)->setJSON([
                        'status' => 'error',
                        'message' => 'Notifikasi tidak ditemukan'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Notifikasi tidak ditemukan');
            }

            // Delete user state first
            $this->userStateModel->where('notification_id', $id)->delete();
            
            // Then delete notification
            if ($this->notificationsModel->delete($id)) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Notifikasi berhasil dihapus'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('success', 'Notifikasi berhasil dihapus');
            } else {
                log_message('error', 'Failed to delete notification with ID: ' . $id);
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menghapus notifikasi'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Gagal menghapus notifikasi');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::delete: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menghapus notifikasi'
                ]);
            }
            return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Terjadi kesalahan saat menghapus notifikasi');
        }
    }

    public function deleteAll()
    {
        try {
            // Get all notification IDs
            $notifications = $this->notificationsModel->findAll();
            
            // Delete all user states first
            foreach ($notifications as $notification) {
                $this->userStateModel->where('notification_id', $notification['id'])->delete();
            }
            
            // Then delete all notifications
            if ($this->notificationsModel->truncate()) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Semua notifikasi berhasil dihapus'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('success', 'Semua notifikasi berhasil dihapus');
            } else {
                log_message('error', 'Failed to delete all notifications');
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menghapus semua notifikasi'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Gagal menghapus semua notifikasi');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::deleteAll: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menghapus semua notifikasi'
                ]);
            }
            return redirect()->to(base_url('admin/kelola-notifikasi'))->with('error', 'Terjadi kesalahan saat menghapus semua notifikasi');
        }
    }

    public function userState()
    {
        try {
            // Load common data for header
            $commonData = $this->loadCommonData();
            
            $filter = $this->request->getGet('filter') ?? 'all';
            
            $data = [
                'title' => 'User Notification State',
                'filter' => $filter,
                'userStates' => $this->getUserStates($filter),
                'seoProfiles' => $this->seoProfilesModel->findAll(),
                'vendorProfiles' => $this->vendorProfilesModel->findAll(),
                'stats' => $this->getStats(),
                'page' => 'User Notification State',
            ];

            return view('admin/kelola_notifikasi/user_state', array_merge($data, $commonData));
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::userState: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data user state');
        }
    }

    public function deleteAllUserState()
    {
        try {
            if ($this->userStateModel->truncate()) {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'success',
                        'message' => 'Semua data user state berhasil dihapus'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi/user-state'))->with('success', 'Semua data user state berhasil dihapus');
            } else {
                log_message('error', 'Failed to delete all user state data');
                if ($this->request->isAJAX()) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'status' => 'error',
                        'message' => 'Gagal menghapus semua data user state'
                    ]);
                }
                return redirect()->to(base_url('admin/kelola-notifikasi/user-state'))->with('error', 'Gagal menghapus semua data user state');
            }
        } catch (\Exception $e) {
            log_message('error', 'Error in KelolaNotifikasi::deleteAllUserState: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setStatusCode(500)->setJSON([
                    'status' => 'error',
                    'message' => 'Terjadi kesalahan saat menghapus semua data user state'
                ]);
            }
            return redirect()->to(base_url('admin/kelola-notifikasi/user-state'))->with('error', 'Terjadi kesalahan saat menghapus semua data user state');
        }
    }

    private function getStats()
    {
        try {
            $db = \Config\Database::connect();
            
            // Total notifications
            $totalNotifications = $db->table('notifications')->countAllResults();
            
            // Unread count from notification_user_state
            $unreadCount = $db->table('notification_user_state')
                              ->where('is_read', 0)
                              ->countAllResults();
            
            // User state count (hidden notifications)
            $userStateCount = $db->table('notification_user_state')
                                 ->where('hidden', 1)
                                 ->countAllResults();
            
            return [
                'total_notifications' => $totalNotifications,
                'unread_count' => $unreadCount,
                'user_state_count' => $userStateCount,
            ];
        } catch (\Exception $e) {
            log_message('error', 'Error in getStats: ' . $e->getMessage());
            return [
                'total_notifications' => 0,
                'unread_count' => 0,
                'user_state_count' => 0,
            ];
        }
    }

    private function getFilteredNotifications($filter = '', $search = '')
    {
        try {
            $db = \Config\Database::connect();
            
            $builder = $db->table('notifications n')
                ->select('n.*, nus.is_read, nus.read_at, nus.hidden, nus.hidden_at,
                         u.username, u.display_name, u.user_type,
                         ap.name as admin_name, 
                         vp.business_name as vendor_name, 
                         sp.name as seo_name')
                ->join('notification_user_state nus', 'n.id = nus.notification_id', 'left')
                ->join('users u', 'u.id = nus.user_id', 'left')
                ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
                ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left');

            // Apply filter
            switch ($filter) {
                case 'read':
                    $builder->where('nus.is_read', 1);
                    break;
                case 'unread':
                    $builder->where('nus.is_read', 0);
                    break;
                case 'system':
                    $builder->where('n.type', 'system');
                    break;
                case 'announcement':
                    $builder->where('n.type', 'announcement');
                    break;
                case 'vendor':
                    $builder->where('u.user_type', 'vendor');
                    break;
                case 'seo':
                    $builder->where('u.user_type', 'seo');
                    break;
            }

            // Apply search
            if (!empty($search)) {
                $builder->groupStart()
                    ->like('u.username', $search)
                    ->orLike('u.display_name', $search)
                    ->orLike('ap.name', $search)
                    ->orLike('vp.business_name', $search)
                    ->orLike('sp.name', $search)
                    ->orLike('n.title', $search)
                    ->orLike('n.message', $search)
                    ->orLike('n.type', $search)
                    ->groupEnd();
            }

            $results = $builder->orderBy('n.created_at', 'DESC')
                            ->get()
                            ->getResultArray();
            
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'Error in getFilteredNotifications: ' . $e->getMessage());
            return [];
        }
    }

    private function getNotificationWithUserState($id)
    {
        try {
            $db = \Config\Database::connect();
            
            $notification = $db->table('notifications n')
                ->select('n.*, nus.is_read, nus.read_at, nus.hidden, nus.hidden_at, nus.user_id as state_user_id,
                         u.username, u.display_name, u.user_type,
                         ap.name as admin_name, 
                         vp.business_name as vendor_name, 
                         sp.name as seo_name')
                ->join('notification_user_state nus', 'n.id = nus.notification_id', 'left')
                ->join('users u', 'u.id = nus.user_id', 'left')
                ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
                ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                ->where('n.id', $id)
                ->get()
                ->getRowArray();
            
            return $notification;
        } catch (\Exception $e) {
            log_message('error', 'Error in getNotificationWithUserState: ' . $e->getMessage());
            return null;
        }
    }

    private function getUserStates($filter)
    {
        try {
            $db = \Config\Database::connect();
            
            // Query yang sudah berfungsi dengan benar
            $builder = $db->table('notification_user_state nus')
                ->select('nus.*, n.title, n.message, n.type, u.username, 
                         ap.name as admin_name, 
                         vp.business_name as vendor_name, 
                         sp.name as seo_name')
                ->join('notifications n', 'n.id = nus.notification_id')
                ->join('users u', 'u.id = nus.user_id')
                ->join('admin_profiles ap', 'ap.user_id = u.id', 'left')
                ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left');

            switch ($filter) {
                case 'read':
                    $builder->where('nus.is_read', 1);
                    break;
                case 'unread':
                    $builder->where('nus.is_read', 0);
                    break;
                case 'hidden':
                    $builder->where('nus.hidden', 1);
                    break;
                default:
                    // For specific user filters
                    if (str_starts_with($filter, 'seo_')) {
                        $seoId = str_replace('seo_', '', $filter);
                        $builder->where('sp.id', $seoId);
                    } elseif (str_starts_with($filter, 'vendor_')) {
                        $vendorId = str_replace('vendor_', '', $filter);
                        $builder->where('vp.id', $vendorId);
                    }
                    break;
            }

            return $builder->orderBy('nus.created_at', 'DESC')
                          ->get()
                          ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error in getUserStates: ' . $e->getMessage());
            return [];
        }
    }
}