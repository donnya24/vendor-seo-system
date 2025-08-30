<?php
$session = session();
$vp = $vp ?? [];

$status = $vp['status'] ?? 'pending';
$isVerified = ($status === 'verified');

$hasCommissionApproved = !empty($vp['commission_rate']);
$currentCommission     = $vp['commission_rate'] ?? null;
$requestedCommission   = $vp['requested_commission'] ?? null;

$userEmail = service('auth')->user()->email ?? '';

$successMessage = $session->getFlashdata('success');
$errorMessage   = $session->getFlashdata('error');
$errors         = $session->getFlashdata('errors');

$profileImage     = $vp['profile_image'] ?? '';
$profileImagePath = base_url('assets/img/default-avatar.png');
if ($profileImage) {
  $candidate = FCPATH . 'uploads/vendor_profiles/' . $profileImage;
  if (is_file($candidate)) $profileImagePath = base_url('uploads/vendor_profiles/' . $profileImage);
}
?>

<div x-show="$store.ui.modal==='profileEdit'" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.away="$store.ui.modal=null">
    <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white z-10">
      <h3 class="text-lg font-semibold">Edit Profil Vendor</h3>
      <button class="text-gray-500 hover:text-gray-700" @click="$store.ui.modal=null"><i class="fas fa-times"></i></button>
    </div>

    <div class="px-6 py-4 bg-gray-50 border-b">
      <div class="flex items-center justify-between">
        <span class="text-sm font-medium text-gray-600">Status Akun:</span>
        <span class="px-3 py-1 rounded-full text-xs font-semibold 
          <?= $status==='verified' ? 'bg-green-100 text-green-800' : ($status==='inactive' ? 'bg-gray-100 text-gray-800' : ($status==='rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')) ?>">
          <?= ucfirst($status) ?>
        </span>
      </div>
      <?php if (!$isVerified): ?>
        <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
          Akun belum terverifikasi. Anda tetap dapat <b>mengubah pengajuan komisi</b> sampai diverifikasi.
        </div>
      <?php else: ?>
        <div class="mt-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
          Akun sudah terverifikasi. Pengajuan komisi tidak dapat diubah oleh vendor.
        </div>
      <?php endif; ?>
    </div>

    <?php if ($successMessage): ?>
      <div class="px-6 py-4 bg-green-50 border-b text-green-700"><i class="fas fa-check-circle mr-2"></i><?= esc($successMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
      <div class="px-6 py-4 bg-red-50 border-b text-red-700"><i class="fas fa-exclamation-circle mr-2"></i><?= esc($errorMessage) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
      <div class="px-6 py-4 bg-red-50 border-b">
        <?php foreach ($errors as $e): ?>
          <div class="text-red-700 text-sm mb-1"><i class="fas fa-exclamation-circle mr-2"></i><?= esc($e) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form action="<?= site_url('vendoruser/profile/update'); ?>" method="post" enctype="multipart/form-data" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
      <?= csrf_field() ?>

      <!-- Foto profil -->
      <div class="md:col-span-2">
        <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Foto Profil</h4>
        <div class="flex items-center">
          <div class="relative mr-4">
            <img id="profile-image-preview" src="<?= $profileImagePath ?>" class="w-24 h-24 rounded-full object-cover border-2 border-gray-300" alt="Foto Profil">
            <div class="absolute bottom-0 right-0 bg-white rounded-full p-1 border">
              <label for="profile_image" class="cursor-pointer text-blue-600 hover:text-blue-800">
                <i class="fas fa-camera text-lg"></i>
                <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden"
                       onchange="previewImage(this, 'profile-image-preview')">
              </label>
            </div>
          </div>
          <div class="text-sm text-gray-600">
            <p>Klik ikon kamera untuk mengubah foto profil</p>
            <p class="text-xs mt-1">Format: JPG/PNG/GIF/WEBP, maksimal 2MB</p>
            <?php if ($profileImage): ?>
              <button type="button" onclick="removeProfileImage()" class="text-red-600 hover:text-red-800 text-xs mt-2 flex items-center">
                <i class="fas fa-trash mr-1"></i> Hapus foto
              </button>
            <?php endif; ?>
          </div>
        </div>
        <input type="hidden" id="remove_profile_image" name="remove_profile_image" value="0">
      </div>

      <!-- Info akun -->
      <div class="md:col-span-2 mt-2">
        <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Informasi Akun</h4>
      </div>
      <div class="md:col-span-2">
        <label class="block text-sm font-semibold mb-2">Email</label>
        <input type="email" value="<?= esc($userEmail) ?>" class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-600" readonly>
      </div>

      <!-- Bisnis -->
      <div class="md:col-span-2 mt-2">
        <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Informasi Bisnis</h4>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">Nama Bisnis <span class="text-red-500">*</span></label>
        <input name="business_name" value="<?= esc($vp['business_name'] ?? '') ?>" required
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">Nama Pemilik <span class="text-red-500">*</span></label>
        <input name="owner_name" value="<?= esc($vp['owner_name'] ?? '') ?>" required
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
      </div>

      <!-- Kontak -->
      <div class="md:col-span-2 mt-2">
        <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Informasi Kontak</h4>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">No. WhatsApp (Pribadi) <span class="text-red-500">*</span></label>
        <input name="whatsapp_number" value="<?= esc($vp['whatsapp_number'] ?? '') ?>" required
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">No. Telepon Imersa (opsional)</label>
        <input name="phone" value="<?= esc($vp['phone'] ?? '') ?>" class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
      </div>

      <!-- Komisi -->
      <div class="md:col-span-2 mt-2">
        <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Komisi</h4>
      </div>
      <?php if ($isVerified): ?>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold mb-2">Komisi yang disetujui</label>
          <input type="text" value="<?= $hasCommissionApproved ? (float)$currentCommission . '%' : '-' ?>" class="w-full border rounded-lg px-3 py-2 bg-green-50 text-green-800" readonly>
          <p class="text-xs text-gray-500 mt-1">Komisi ditetapkan oleh admin setelah verifikasi.</p>
        </div>
      <?php else: ?>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold mb-2">Ajukan/ubah komisi (%) <span class="text-red-500">*</span></label>
          <div class="flex items-center">
            <input type="number" name="requested_commission" min="1" max="100" step="0.1"
                   value="<?= esc($requestedCommission ?? '') ?>"
                   class="w-28 border rounded-lg px-3 py-2 mr-2" required><span>%</span>
          </div>
          <p class="text-xs text-gray-500 mt-1">Selama status belum <b>verified</b>, Anda bebas mengubah nilai ini.</p>
        </div>
      <?php endif; ?>

      <div class="md:col-span-2 pt-4 border-t mt-4">
        <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" type="submit">Simpan Perubahan</button>
        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 ml-2" @click="$store.ui.modal=null">Tutup</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input, previewId){
  const preview=document.getElementById(previewId);
  const file=input.files&&input.files[0]; if(!file) return;
  if(file.size>2*1024*1024){ alert('Ukuran file terlalu besar. Maks 2MB.'); input.value=''; return; }
  const ok=['image/jpeg','image/png','image/gif','image/webp'];
  if(!ok.includes(file.type)){ alert('Format tidak didukung. JPG/PNG/GIF/WEBP'); input.value=''; return; }
  const r=new FileReader(); r.onload=e=>{ preview.src=e.target.result; document.getElementById('remove_profile_image').value='0'; }; r.readAsDataURL(file);
}
function removeProfileImage(){
  const def="<?= base_url('assets/img/default-avatar.png') ?>";
  document.getElementById('profile-image-preview').src=def;
  const inp=document.getElementById('profile_image'); if(inp) inp.value='';
  document.getElementById('remove_profile_image').value='1';
  Alpine.store('toast')?.show('Foto profil akan dihapus setelah Anda menyimpan perubahan.','info');
}
</script>
