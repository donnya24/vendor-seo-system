<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\LeadsModel;
use App\Models\VendorProfilesModel;
use App\Models\ActivityLogsModel;

class Leads extends BaseController
{
    private $vendorProfile;
    private $isVerified;
    private $vendorId;
    private $activityLogsModel;

    public function __construct()
    {
        $this->activityLogsModel = new ActivityLogsModel();
    }

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? null;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorId;
    }

    private function withVendorData(array $data = [])
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    private function logActivity(string $action, string $description = null, array $additionalData = [])
    {
        try {
            $user = service('auth')->user();
            $data = [
                'user_id'     => $user->id,
                'vendor_id'   => $this->vendorId,
                'module'      => 'leads',
                'action'      => $action,
                'status'      => 'success',
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
            log_message('error', 'Failed to log activity in Leads: ' . $e->getMessage());
        }
    }

    public function index()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $m = new LeadsModel();

        $start = (string) $this->request->getGet('start_date');
        $end   = (string) $this->request->getGet('end_date');

        $m->where('vendor_id', $this->vendorId);

        if ($start !== '' && $end !== '') {
            $m->where('tanggal_mulai >=', $start)
            ->where('tanggal_selesai <=', $end);
        }

        $m->orderBy('tanggal_mulai', 'DESC');

        $this->logActivity('view', 'Melihat daftar leads', [
            'start_date' => $start,
            'end_date' => $end
        ]);

        // ⬇️ gunakan layout master
        return view('vendoruser/layouts/vendor_master', [
            'title'        => 'Laporan Leads',
            // data yang dibutuhkan layout (header/sidebar)
            'vp'           => $this->vendorProfile,
            'isVerified'   => $this->isVerified,

            // view konten utama & datanya
            'content_view' => 'vendoruser/leads/index',
            'content_data' => [
                'page'       => 'Laporan Leads',
                'leads'      => $m->findAll(),
                'start_date' => $start,
                'end_date'   => $end,
            ],
        ]);
    }

    public function create()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
        }

        $this->logActivity('create_form', 'Membuka form tambah leads');

        return view('vendoruser/leads/create', $this->withVendorData([
            'page' => 'Tambah Laporan Leads',
        ]));
    }

    public function show($id)
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada.');
        }

        $m    = new LeadsModel();
        $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

        if (! $lead) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['status'=>'error','message'=>'Data tidak ditemukan']);
            }
            return redirect()->to(site_url('vendoruser/leads'))->with('error','Data tidak ditemukan');
        }

        $this->logActivity('view_detail', "Melihat detail leads ID {$id}", [
            'lead_id' => $id
        ]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['status'=>'success','lead'=>$lead]);
        }

        return view('vendoruser/leads/show', $this->withVendorData(['lead'=>$lead]));
    }

    public function edit($id)
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada.');
        }

        $lead = (new LeadsModel())->where([
            'id'        => (int) $id,
            'vendor_id' => $this->vendorId,
        ])->first();

        if (! $lead) {
            return redirect()->to(site_url('vendoruser/leads'))
                ->with('error', 'Laporan tidak ditemukan.');
        }

        $this->logActivity('edit_form', "Membuka form edit leads ID {$id}", [
            'lead_id' => $id
        ]);

        return view('vendoruser/leads/edit', $this->withVendorData([
            'page' => 'Edit Laporan Leads',
            'lead' => $lead,
        ]));
    }

public function store()
{
    if (! $this->initVendor()) {
        return $this->respondAjax('error', 'Profil vendor belum ada. Lengkapi profil terlebih dulu.');
    }

    $rules = [
        'tanggal_mulai'       => 'required|valid_date[Y-m-d]',
        'tanggal_selesai'     => 'required|valid_date[Y-m-d]',
        'jumlah_leads_masuk'  => 'required|integer',
        'jumlah_leads_closing'=> 'required|integer',
    ];

    if (! $this->validate($rules)) {
        return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
    }

    $tanggalMulai = $this->request->getPost('tanggal_mulai');
    $tanggalSelesai = $this->request->getPost('tanggal_selesai');
    
    // Validasi bahwa tanggal mulai tidak boleh setelah tanggal selesai
    if (strtotime($tanggalMulai) > strtotime($tanggalSelesai)) {
        return $this->respondAjax('error', 'Tanggal mulai tidak boleh setelah tanggal selesai');
    }

    $data = [
        'vendor_id'           => $this->vendorId,
        'tanggal_mulai'       => $tanggalMulai,
        'tanggal_selesai'     => $tanggalSelesai,
        'jumlah_leads_masuk'  => (int) $this->request->getPost('jumlah_leads_masuk'),
        'jumlah_leads_closing'=> (int) $this->request->getPost('jumlah_leads_closing'),
        'reported_by_vendor'  => $this->vendorId,
        'assigned_at'         => date('Y-m-d H:i:s'),
        'updated_at'          => date('Y-m-d H:i:s'),
    ];

    $leadsModel = new LeadsModel();
    $result = $leadsModel->insert($data);
    $insertId = $leadsModel->getInsertID();

    if ($result) {
        // 🔔 KIRIM NOTIFIKASI KE ADMIN & SEO
        $this->sendLeadsReportNotification($data, 'create');

        $this->logActivity('create', "Menambahkan laporan leads periode {$tanggalMulai} - {$tanggalSelesai}", [
            'lead_id' => $insertId,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'jumlah_leads_masuk' => $data['jumlah_leads_masuk'],
            'jumlah_leads_closing' => $data['jumlah_leads_closing']
        ]);
        return $this->respondAjax('success', 'Laporan leads berhasil ditambahkan.');
    } else {
        return $this->respondAjax('error', 'Gagal menambahkan laporan leads.');
    }
}

public function update($id)
{
    if (! $this->initVendor()) {
        return $this->respondAjax('error', 'Profil vendor belum ada.');
    }

    $m    = new LeadsModel();
    $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

    if (! $lead) {
        return $this->respondAjax('error', 'Laporan tidak ditemukan.');
    }

    $rules = [
        'tanggal_mulai'       => 'required|valid_date[Y-m-d]',
        'tanggal_selesai'     => 'required|valid_date[Y-m-d]',
        'jumlah_leads_masuk'  => 'required|integer',
        'jumlah_leads_closing'=> 'required|integer',
    ];

    if (! $this->validate($rules)) {
        return $this->respondAjax('error', implode('<br>', $this->validator->getErrors()));
    }

    $tanggalMulai = $this->request->getPost('tanggal_mulai');
    $tanggalSelesai = $this->request->getPost('tanggal_selesai');
    
    // Validasi bahwa tanggal mulai tidak boleh setelah tanggal selesai
    if (strtotime($tanggalMulai) > strtotime($tanggalSelesai)) {
        return $this->respondAjax('error', 'Tanggal mulai tidak boleh setelah tanggal selesai');
    }

    $updateData = [
        'tanggal_mulai'       => $tanggalMulai,
        'tanggal_selesai'     => $tanggalSelesai,
        'jumlah_leads_masuk'  => (int) $this->request->getPost('jumlah_leads_masuk'),
        'jumlah_leads_closing'=> (int) $this->request->getPost('jumlah_leads_closing'),
        'updated_at'          => date('Y-m-d H:i:s'),
    ];

    $result = $m->update((int) $id, $updateData);

    if ($result) {
        // 🔔 KIRIM NOTIFIKASI KE ADMIN & SEO
        $this->sendLeadsReportNotification(array_merge($lead, $updateData), 'update');

        $this->logActivity('update', "Memperbarui laporan leads ID {$id}", [
            'lead_id' => $id,
            'tanggal_mulai' => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'jumlah_leads_masuk' => $updateData['jumlah_leads_masuk'],
            'jumlah_leads_closing' => $updateData['jumlah_leads_closing']
        ]);
        return $this->respondAjax('success', 'Laporan leads berhasil diperbarui.');
    } else {
        return $this->respondAjax('error', 'Gagal memperbarui laporan leads.');
    }
}

public function delete($id)
{
    if (! $this->initVendor()) {
        return $this->respondAjax('error', 'Profil vendor belum ada.');
    }

    $m    = new LeadsModel();
    $lead = $m->where(['id' => (int) $id, 'vendor_id' => $this->vendorId])->first();

    if (! $lead) {
        return $this->respondAjax('error', 'Laporan tidak ditemukan.');
    }

    $result = $m->delete((int) $id);

    if ($result) {
        // 🔔 KIRIM NOTIFIKASI KE ADMIN & SEO
        $this->sendLeadsReportNotification($lead, 'delete');

        $this->logActivity('delete', "Menghapus laporan leads ID {$id}", [
            'lead_id' => $id,
            'tanggal_mulai' => $lead['tanggal_mulai'],
            'tanggal_selesai' => $lead['tanggal_selesai']
        ]);
        return $this->respondAjax('success', 'Laporan leads berhasil dihapus.');
    } else {
        return $this->respondAjax('error', 'Gagal menghapus laporan leads.');
    }
}

    private function respondAjax(string $status, string $message)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status'  => $status,
                'message' => $message
            ]);
        }

        $type = $status === 'success' ? 'success' : 'error';
        return redirect()->back()->with($type, $message);
    }

public function deleteMultiple()
{
    if (! $this->initVendor()) {
        return $this->respondAjax('error', 'Profil vendor belum ada.');
    }

    $ids = $this->request->getJSON(true)['ids'] ?? [];

    if (empty($ids)) {
        return $this->respondAjax('error', 'Tidak ada data terpilih.');
    }

    $m = new LeadsModel();
    $leadsToDelete = $m->where('vendor_id', $this->vendorId)
                      ->whereIn('id', $ids)
                      ->findAll();

    $deleted = $m->where('vendor_id', $this->vendorId)
                ->whereIn('id', $ids)
                ->delete();

    if ($deleted) {
        // 🔔 KIRIM NOTIFIKASI UNTUK SETIAP LEADS YANG DIHAPUS
        foreach ($leadsToDelete as $lead) {
            $this->sendLeadsReportNotification($lead, 'delete');
        }

        $this->logActivity('delete_multiple', "Menghapus multiple laporan leads", [
            'lead_ids' => $ids,
            'count' => count($ids)
        ]);
        return $this->respondAjax('success', 'Data terpilih berhasil dihapus.');
    }

    return $this->respondAjax('error', 'Gagal menghapus data terpilih.');
}

    /**
 * Kirim notifikasi laporan leads ke Admin & SEO
 */
private function sendLeadsReportNotification($leadsData, $action = 'create')
{
    try {
        $db = \Config\Database::connect();
        
        $vendorName = $this->vendorProfile['business_name'] ?? 'Vendor Tidak Dikenal';
        $ownerName = $this->vendorProfile['owner_name'] ?? 'Tidak Dikenal';
        
        // Format periode
        $period = date('d/m/Y', strtotime($leadsData['tanggal_mulai'])) . ' - ' . date('d/m/Y', strtotime($leadsData['tanggal_selesai']));
        
        // Tentukan action text
        $actionText = '';
        switch ($action) {
            case 'create':
                $actionText = 'mengirim laporan leads baru';
                break;
            case 'update':
                $actionText = 'memperbarui laporan leads';
                break;
            case 'delete':
                $actionText = 'menghapus laporan leads';
                break;
            default:
                $actionText = 'melakukan aksi pada laporan leads';
        }

        $title = 'Laporan Leads Vendor';
        $message = "Vendor {$vendorName} (Pemilik: {$ownerName}) {$actionText} untuk periode {$period} dengan {$leadsData['jumlah_leads_masuk']} leads masuk dan {$leadsData['jumlah_leads_closing']} leads closing.";

        // 1. Kirim ke Admin
        $adminUsers = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'admin')
            ->get()
            ->getResultArray();

        foreach ($adminUsers as $admin) {
            $db->table('notifications')->insert([
                'user_id' => $admin['user_id'],
                'vendor_id' => $this->vendorId,
                'seo_id' => null,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        // 2. Kirim ke SEO
        $seoUsers = $db->table('auth_groups_users')
            ->select('user_id')
            ->where('group', 'seoteam')
            ->get()
            ->getResultArray();

        foreach ($seoUsers as $seo) {
            $db->table('notifications')->insert([
                'user_id' => $seo['user_id'],
                'vendor_id' => $this->vendorId,
                'seo_id' => $seo['user_id'],
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'is_read' => 0,
                'read_at' => null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        log_message('info', "Notifikasi laporan leads berhasil dikirim: {$vendorName} - {$action}");

    } catch (\Throwable $e) {
        log_message('error', 'Gagal mengirim notifikasi laporan leads: ' . $e->getMessage());
    }
}
}