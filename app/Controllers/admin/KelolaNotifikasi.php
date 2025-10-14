<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use App\Models\NotificationsModel;
use App\Models\NotificationsUserStateModel;
use App\Models\SeoProfilesModel;
use App\Models\VendorProfilesModel;
use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class KelolaNotifikasi extends BaseAdminController
{
    protected $notificationsModel;
    protected $userStateModel;
    protected $seoProfilesModel;
    protected $vendorProfilesModel;
    protected $userModel;

    public function __construct()
    {
        $this->notificationsModel = new NotificationsModel();
        $this->userStateModel = new NotificationsUserStateModel();
        $this->seoProfilesModel = new SeoProfilesModel();
        $this->vendorProfilesModel = new VendorProfilesModel();
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
            $allUsers = $this->userModel->findAll();

            $data = [
                'title' => 'Buat Notifikasi Baru',
                'seoProfiles' => $seoProfiles,
                'vendorProfiles' => $vendorProfiles,
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
                'type' => 'required|in_list[commission,announcement,system]',
                'is_read' => 'permit_empty|in_list[0,1]',
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

            $data = [
                'user_id' => $this->request->getPost('user_id'),
                'title' => $this->request->getPost('title'),
                'message' => $this->request->getPost('message'),
                'type' => $this->request->getPost('type'),
                'is_read' => $this->request->getPost('is_read') ? 1 : 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->notificationsModel->insert($data)) {
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

            // Get SEO profiles dan Vendor profiles untuk dropdown
            $seoProfiles = $this->seoProfilesModel->findAll();
            $vendorProfiles = $this->vendorProfilesModel->findAll();
            $allUsers = $this->userModel->findAll();

            $data = [
                'title' => 'Edit Notifikasi',
                'notification' => $notification,
                'seoProfiles' => $seoProfiles,
                'vendorProfiles' => $vendorProfiles,
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
                'type' => 'required|in_list[commission,announcement,system]',
                'is_read' => 'permit_empty|in_list[0,1]',
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

            $data = [
                'user_id' => $this->request->getPost('user_id'),
                'title' => $this->request->getPost('title'),
                'message' => $this->request->getPost('message'),
                'type' => $this->request->getPost('type'),
                'is_read' => $this->request->getPost('is_read') ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->notificationsModel->update($id, $data)) {
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
            $allUsers = $this->userModel->findAll();

            $data = [
                'seoProfiles' => $seoProfiles,
                'vendorProfiles' => $vendorProfiles,
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
            if ($this->notificationsModel->emptyTable()) {
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
            if ($this->userStateModel->emptyTable()) {
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
            return [
                'total_notifications' => $this->notificationsModel->countAll(),
                'unread_count' => $this->notificationsModel->where('is_read', 0)->countAllResults(),
                'user_state_count' => $this->userStateModel->countAll(),
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
                ->select('n.*, u.username, 
                         sp.name as seo_name, 
                         vp.business_name as vendor_name')
                ->join('users u', 'u.id = n.user_id', 'left')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left');

            // Apply filter
            switch ($filter) {
                case 'read':
                    $builder->where('n.is_read', 1);
                    break;
                case 'unread':
                    $builder->where('n.is_read', 0);
                    break;
                case 'vendor':
                    $builder->where('vp.business_name IS NOT NULL');
                    break;
                case 'seo':
                    $builder->where('sp.name IS NOT NULL');
                    break;
            }

            // Apply search
            if (!empty($search)) {
                $builder->groupStart()
                       ->like('sp.name', $search)
                       ->orLike('vp.business_name', $search)
                       ->orLike('u.username', $search)
                       ->orLike('n.title', $search)
                       ->orLike('n.message', $search)
                       ->orLike('n.type', $search)
                       ->groupEnd();
            }

            return $builder->orderBy('n.created_at', 'DESC')
                          ->get()
                          ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error in getFilteredNotifications: ' . $e->getMessage());
            return [];
        }
    }

    private function getUserStates($filter)
    {
        try {
            $db = \Config\Database::connect();
            
            $builder = $db->table('notification_user_state nus')
                ->select('nus.*, n.title, n.message, n.type, u.username, 
                         sp.name as seo_name, vp.business_name as vendor_name')
                ->join('notifications n', 'n.id = nus.notification_id')
                ->join('users u', 'u.id = nus.user_id')
                ->join('seo_profiles sp', 'sp.user_id = u.id', 'left')
                ->join('vendor_profiles vp', 'vp.user_id = u.id', 'left');

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