<?php
 $session = session();
 $vp = $vp ?? [];

 $status = $vp['status'] ?? 'pending';
 $isVerified = ($status === 'verified');

 $hasCommissionApproved = !empty($vp['commission_rate']);
 $currentCommission     = $vp['commission_rate'] ?? null;
 $requestedCommission   = $vp['requested_commission'] ?? null;
 $requestedCommissionNominal = $vp['requested_commission_nominal'] ?? null;
 $commissionType        = $vp['commission_type'] ?? 'percent';

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

  <?php if ($status === 'rejected' && !empty($vp['rejection_reason'])): ?>
    <div class="mt-2 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
      <b>Alasan Ditolak:</b><br>
      <?= esc($vp['rejection_reason']) ?>
    </div>
  <?php elseif ($status === 'inactive' && !empty($vp['inactive_reason'])): ?>
    <div class="mt-2 p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-700">
      <b>Alasan Nonaktif:</b><br>
      <?= esc($vp['inactive_reason']) ?>
    </div>
  <?php endif; ?>

  <?php if (!$isVerified && $status !== 'rejected' && $status !== 'inactive'): ?>
    <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-700">
      Akun belum terverifikasi. Anda tetap dapat <b>mengubah pengajuan komisi</b> sampai diverifikasi.
    </div>
  <?php elseif ($isVerified): ?>
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
            <?php if (!empty($profileImage)): ?>
              <img id="profile-image-preview" src="<?= $profileImagePath ?>" class="w-24 h-24 rounded-full object-cover border-2 border-gray-300" alt="Foto Profil">
            <?php else: ?>
              <div id="profile-image-preview" class="w-24 h-24 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 border-2 border-gray-300">
                <i class="fas fa-user text-3xl"></i>
              </div>
            <?php endif; ?>

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
            <?php if (!empty($profileImage)): ?>
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
          <label class="block text-sm font-semibold mb-2">Komisi yang diajukan vendor</label>
          <?php 
            $displayValue = '-';
            if ($commissionType === 'percent' && $requestedCommission) {
                $displayValue = (float)$requestedCommission . '%';
            } elseif ($commissionType === 'nominal' && $requestedCommissionNominal) {
                $displayValue = 'Rp ' . number_format($requestedCommissionNominal, 0, ',', '.');
            }
          ?>
          <input type="text" value="<?= $displayValue ?>" class="w-full border rounded-lg px-3 py-2 bg-yellow-50 text-yellow-800" readonly>
          <p class="text-xs text-gray-500 mt-1">Nilai ini adalah pengajuan awal Anda dan tidak bisa diubah setelah diverifikasi.</p>
        </div>
      <?php else: ?>
        <div class="md:col-span-2">
          <label class="block text-sm font-semibold mb-2">Jenis Komisi <span class="text-red-500">*</span></label>
          <div class="flex space-x-4">
            <label class="inline-flex items-center">
              <input type="radio" name="commission_type" value="percent" class="form-radio" 
                     <?= ($commissionType === 'percent') ? 'checked' : '' ?>
                     onchange="toggleCommissionInput()">
              <span class="ml-2">Persen (%)</span>
            </label>
            <label class="inline-flex items-center">
              <input type="radio" name="commission_type" value="nominal" class="form-radio"
                     <?= ($commissionType === 'nominal') ? 'checked' : '' ?>
                     onchange="toggleCommissionInput()">
              <span class="ml-2">Nominal (Rp)</span>
            </label>
          </div>
        </div>

        <div class="md:col-span-2" id="percent-input" style="<?= ($commissionType === 'nominal') ? 'display:none' : '' ?>">
          <label class="block text-sm font-semibold mb-2">Ajukan/ubah komisi (%) <span class="text-red-500">*</span></label>
          <div class="flex items-center">
            <input type="number" name="requested_commission" min="1" max="100" step="0.1"
                   value="<?= esc($requestedCommission ?? '') ?>"
                   class="w-28 border rounded-lg px-3 py-2 mr-2" <?= ($commissionType === 'percent') ? 'required' : '' ?>><span>%</span>
          </div>
        </div>

        <div class="md:col-span-2" id="nominal-input" style="<?= ($commissionType === 'percent') ? 'display:none' : '' ?>">
          <label class="block text-sm font-semibold mb-2">Ajukan/ubah komisi (Rp) <span class="text-red-500">*</span></label>
          <div class="flex items-center">
            <span class="mr-2">Rp</span>
            <input type="text" name="requested_commission_nominal" id="nominal-field"
                   value="<?= $requestedCommissionNominal ? number_format($requestedCommissionNominal, 0, ',', '.') : '' ?>"
                   class="w-40 border rounded-lg px-3 py-2" <?= ($commissionType === 'nominal') ? 'required' : '' ?>>
          </div>
          <p class="text-xs text-gray-500 mt-1">Contoh: 200.000, 1.000.000, 10.000.000</p>
        </div>

        <p class="text-xs text-gray-500 mt-1 md:col-span-2">Selama status belum <b>verified</b>, Anda bebas mengubah nilai ini.</p>
      <?php endif; ?>

      <div class="md:col-span-2 pt-4 border-t mt-4">
        <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" type="submit">Simpan Perubahan</button>
        <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 ml-2" @click="$store.ui.modal=null">Tutup</button>
      </div>
    </form>
  </div>
</div>

<script>
function previewImage(input, previewId) {
  try {
    let preview = document.getElementById(previewId);
    const file = input.files && input.files[0];
    if (!file) return;
    if (file.size > 2 * 1024 * 1024) { alert('Ukuran file terlalu besar. Maks 2MB.'); input.value = ''; return; }
    const allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowed.includes(file.type)) { alert('Format tidak didukung. Hanya JPG, PNG, GIF, atau WEBP.'); input.value = ''; return; }

    const reader = new FileReader();
    reader.onload = e => {
      if (preview.tagName.toLowerCase() !== 'img') {
        const img = document.createElement('img');
        img.id = previewId; img.className = "w-24 h-24 rounded-full object-cover border-2 border-gray-300";
        preview.replaceWith(img); preview = img;
      }
      preview.src = e.target.result;
      document.getElementById('remove_profile_image').value = '0';
    };
    reader.readAsDataURL(file);
  } catch (err) { console.error('Preview image error:', err); }
}

function removeProfileImage() {
  try {
    const preview = document.getElementById('profile-image-preview');
    const input = document.getElementById('profile_image');
    const newDiv = document.createElement('div');
    newDiv.id = 'profile-image-preview';
    newDiv.className = "w-24 h-24 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 border-2 border-gray-300";
    newDiv.innerHTML = '<i class="fas fa-user text-3xl"></i>';
    preview.replaceWith(newDiv);
    if (input) input.value = '';
    document.getElementById('remove_profile_image').value = '1';
  } catch (err) { console.error('Remove profile image error:', err); }
}

function toggleCommissionInput() {
  const percentInput = document.getElementById('percent-input');
  const nominalInput = document.getElementById('nominal-input');
  const percentRadio = document.querySelector('input[name="commission_type"][value="percent"]');
  const nominalRadio = document.querySelector('input[name="commission_type"][value="nominal"]');

  if (percentRadio.checked) {
    percentInput.style.display = 'block';
    nominalInput.style.display = 'none';
    // Remove required attribute from nominal input
    document.querySelector('input[name="requested_commission_nominal"]').removeAttribute('required');
    // Add required attribute to percent input
    document.querySelector('input[name="requested_commission"]').setAttribute('required', 'required');
  } else {
    percentInput.style.display = 'none';
    nominalInput.style.display = 'block';
    // Remove required attribute from percent input
    document.querySelector('input[name="requested_commission"]').removeAttribute('required');
    // Add required attribute to nominal input
    document.querySelector('input[name="requested_commission_nominal"]').setAttribute('required', 'required');
  }
}

// Format nominal input
document.addEventListener('DOMContentLoaded', function() {
  const nominalField = document.getElementById('nominal-field');
  if (nominalField) {
    nominalField.addEventListener('input', function(e) {
      // Remove all non-digit characters
      let value = e.target.value.replace(/[^\d]/g, '');
      // Format with thousand separators
      if (value) {
        value = parseInt(value).toLocaleString('id-ID');
      }
      e.target.value = value;
    });
    
    // Handle paste event to clean up the value
    nominalField.addEventListener('paste', function(e) {
      setTimeout(function() {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value) {
          value = parseInt(value).toLocaleString('id-ID');
        }
        e.target.value = value;
      }, 1);
    });
  }
});
</script>

<!-- SweetAlert2 toast dari flashdata (PROFIL) -->
<script>
  const PROF_SUCCESS = <?= json_encode($successMessage ?? null, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
  const PROF_ERROR   = <?= json_encode($errorMessage   ?? null, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
  const PROF_ERRORS  = <?= json_encode(array_values($errors ?? []), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;

  document.addEventListener('DOMContentLoaded', () => {
    if (PROF_SUCCESS && window.swalToast) swalToast('success', PROF_SUCCESS);
    if (PROF_ERROR   && window.swalToast) swalToast('error',   PROF_ERROR);
    (PROF_ERRORS || []).forEach(msg => { if (window.swalToast) swalToast('error', msg); });
  });
</script>