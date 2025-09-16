<?php
// app/Controllers/Vendoruser/ActivityLogs.php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\ActivityLogsModel;
use App\Models\VendorProfilesModel;

class ActivityLogs extends BaseController
{
    private $vendorProfile;
    private $vendorId;
    private $isVerified;

    private function initVendor(): bool
    {
        $user = service('auth')->user();
        $this->vendorProfile = (new VendorProfilesModel())
            ->where('user_id', (int) $user->id)
            ->first();

        $this->vendorId   = $this->vendorProfile['id'] ?? 0;
        $this->isVerified = ($this->vendorProfile['status'] ?? '') === 'verified';

        return (bool) $this->vendorId;
    }

    private function withVendorData(array $data = []): array
    {
        return array_merge($data, [
            'vp'         => $this->vendorProfile,
            'isVerified' => $this->isVerified,
        ]);
    }

    public function index()
    {
        if (! $this->initVendor()) {
            return redirect()->to(site_url('vendoruser/dashboard'))
                ->with('error', 'Profil vendor belum ada. Lengkapi profil terlebih dahulu.');
        }

        $userId = service('auth')->user()->id;

        $logs = (new ActivityLogsModel())
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->findAll();

        // Render via layout master
        return view('vendoruser/layouts/vendor_master', $this->withVendorData([
            'title'        => 'Activity Logs',
            'content_view' => 'vendoruser/activity_logs/index',
            'content_data' => [
                'page' => 'Activity Logs',
                'logs' => $logs,
            ],
        ]));
    }

}
