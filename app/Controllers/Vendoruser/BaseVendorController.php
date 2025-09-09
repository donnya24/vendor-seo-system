<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;

class BaseVendorController extends BaseController
{
    protected $vp;          // data profil vendor
    protected $isVerified;  // status verifikasi akun vendor
    protected $vendorId;    // ID vendor

    protected function initializeVendor(): void
    {
        $auth = service('auth');
        $user = $auth->user();

        // Ambil profil vendor
        $this->vp = (new VendorProfilesModel())
            ->where('user_id', (int)$user->id)
            ->first();

        $this->vendorId   = $this->vp['id'] ?? null;
        $this->isVerified = ($this->vp['status'] ?? '') === 'verified';
    }

    protected function render(string $view, array $data = [])
    {
        // Pastikan vendor info selalu ikut dikirim ke view
        $this->initializeVendor();

        $data['vp']         = $this->vp;
        $data['isVerified'] = $this->isVerified;
        $data['vendorId']   = $this->vendorId;

        return view($view, $data);
    }

    /**
     * Catat aktivitas vendor
     *
     * @param string $module     Modul/Bagian yang melakukan aksi (misal: commissions)
     * @param string $action     Nama aksi (create/update/delete)
     * @param string $description Deskripsi aktivitas
     */
    protected function logActivity(string $module, string $action, string $description)
    {
        if (!function_exists('activity')) {
            // fallback: buat helper di App/Helpers/activity_helper.php
            helper('activity');
        }

        activity([
            'user_id'    => service('auth')->user()->id ?? null,
            'vendor_id'  => $this->vendorId,
            'module'     => $module,
            'action'     => $action,
            'description'=> $description,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
        ]);
    }
}
