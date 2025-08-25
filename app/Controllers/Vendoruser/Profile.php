<?php
namespace App\Controllers\Vendoruser;

use App\Controllers\BaseController;
use App\Models\VendorProfilesModel;

class Profile extends BaseController
{
    public function edit()
    {
        $user = service('auth')->user();
        $vp   = (new VendorProfilesModel())
                    ->where('user_id', (int) $user->id)
                    ->first();

        return view('vendoruser/profile/edit', [
            'page' => 'Profil',
            'vp'   => $vp,
        ]);
    }

    public function update()
    {
        $user = service('auth')->user();
        $vpM  = new VendorProfilesModel();
        $vp   = $vpM->where('user_id', (int) $user->id)->first();

        // Ambil commission rate (dukung nama alternatif commission_rate_used)
        $rateRaw = $this->request->getPost('commission_rate');
        if ($rateRaw === null || $rateRaw === '') {
            $rateRaw = $this->request->getPost('commission_rate_used');
        }

        // Normalisasi & validasi (0–100), boleh kosong (null)
        $commissionRate = null;
        if ($rateRaw !== null && $rateRaw !== '') {
            $commissionRate = (float) str_replace(',', '.', (string) $rateRaw);
            if ($commissionRate < 0 || $commissionRate > 100) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Komisi harus antara 0–100%.');
            }
        }

        $data = [
            'business_name'   => $this->request->getPost('business_name'),
            'owner_name'      => $this->request->getPost('owner_name'),
            'whatsapp_number' => $this->request->getPost('whatsapp_number'),
            'phone'           => $this->request->getPost('phone') ?: null,
            'commission_rate' => $commissionRate, // ⬅️ ditambahkan
            'updated_at'      => date('Y-m-d H:i:s'),
        ];

        // Update kalau ada baris; kalau belum ada, buat baru
        if ($vp) {
            $vpM->update((int) $vp['id'], $data);
        } else {
            $data['user_id']    = (int) $user->id;
            $data['status']     = 'pending';
            $data['is_verified']= 0;
            $data['created_at'] = date('Y-m-d H:i:s');
            $vpM->insert($data);
        }

        return redirect()->to(site_url('vendor/profile'))
            ->with('success', 'Profil diperbarui.');
    }
}
