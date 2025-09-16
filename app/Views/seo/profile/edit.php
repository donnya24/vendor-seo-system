<?php
// ====== Fallback agar form SELALU ter-prefill ======
$sp        = $sp        ?? null;
$userEmail = $userEmail ?? null;

$auth = service('auth');
$user = $auth ? $auth->user() : null;

if ((!is_array($sp) || !$sp) && $user) {
    $db = db_connect();
    $sp = $db->table('seo_profiles')
        ->where('user_id', (int)$user->id)
        ->get()
        ->getRowArray() ?? [];
}

// Normalisasi + default saat profil kosong
$sp = is_array($sp) ? $sp : [];
$sp['name']          = $sp['name']          ?? '';
$sp['phone']         = $sp['phone']         ?? '';
$sp['status']        = $sp['status']        ?? 'pending';
$sp['profile_image'] = $sp['profile_image'] ?? '';

if ((!is_string($userEmail) || $userEmail === '') && $user) {
    $db  = isset($db) ? $db : db_connect();
    $row = $db->table('auth_identities')
        ->select('secret')
        ->where('user_id', (int)$user->id)
        ->whereIn('type', ['email', 'email_password'])
        ->orderBy('id', 'desc')
        ->get()
        ->getRowArray();
    $userEmail = $row['secret'] ?? '';
}

// ====== Foto profil (pakai placeholder div jika tidak ada foto) ======
$defaultAvatar    = base_url('assets/img/default-avatar.png');
$profileImage     = $sp['profile_image'];
$hasProfileImage  = false;
$profileImagePath = $defaultAvatar;

if ($profileImage) {
    $disk = FCPATH . 'uploads/seo_profiles/' . $profileImage;
    if (is_file($disk)) {
        $hasProfileImage  = true;
        $profileImagePath = base_url('uploads/seo_profiles/' . $profileImage);
    }
}

// Helper value dengan dukungan old()
if (! function_exists('v')) {
    function v(string $key, $fallback = '') { return old($key, $fallback); }
}
?>

<form action="<?= site_url('seo/profile/update'); ?>" method="post" enctype="multipart/form-data" class="space-y-4">
  <?= csrf_field() ?>

  <!-- Status ringkas -->
  <div class="flex items-center gap-2">
    <span class="text-sm text-gray-600">Status Akun:</span>
    <?php
      $st = strtolower((string)$sp['status']);
      $badge = 'bg-yellow-100 text-yellow-800';
      if ($st === 'active')   $badge = 'bg-green-100 text-green-800';
      if ($st === 'rejected') $badge = 'bg-red-100 text-red-800';
      if ($st === 'inactive') $badge = 'bg-gray-100 text-gray-800';
    ?>
    <span class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $badge ?>"><?= esc(ucfirst($sp['status'])) ?></span>
  </div>

  <!-- Foto Profil -->
  <div>
    <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Foto Profil</h4>
    <div class="flex items-center gap-4">
      <div class="relative w-24 h-24">
        <?php if ($hasProfileImage): ?>
          <img id="seo-profile-preview"
               src="<?= esc($profileImagePath) ?>"
               class="w-24 h-24 rounded-full object-cover border-2 border-gray-300"
               alt="Foto Profil">
        <?php else: ?>
          <!-- Placeholder div (akan diganti IMG saat pilih file) -->
          <div id="seo-profile-preview"
               class="w-24 h-24 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 border-2 border-gray-300">
            <i class="fas fa-user text-3xl"></i>
          </div>
        <?php endif; ?>

        <!-- Upload -->
        <label class="absolute bottom-0 right-0 bg-white rounded-full p-1 border cursor-pointer" title="Ubah foto">
          <i class="fas fa-camera text-blue-600"></i>
          <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden"
                 onchange="previewImage(this, 'seo-profile-preview'); document.getElementById('remove_profile_image').value='0'">
        </label>
      </div>

      <div class="text-sm text-gray-600">
        <p>Klik ikon kamera untuk ubah foto</p>
        <p class="text-xs mt-1">Format: JPG/PNG/GIF/WEBP, maks 2MB</p>

        <?php if ($hasProfileImage): ?>
          <!-- Hapus foto -->
          <button type="button"
                  class="mt-2 inline-flex items-center gap-2 text-red-600 hover:text-red-800"
                  onclick="removeProfilePhoto('seo-profile-preview','profile_image','remove_profile_image')">
            <i class="fas fa-trash"></i> Hapus foto
          </button>
        <?php endif; ?>
      </div>
    </div>

    <!-- Flag hapus -->
    <input type="hidden" id="remove_profile_image" name="remove_profile_image" value="0">
  </div>

  <!-- Informasi Akun -->
  <div>
    <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Informasi Akun</h4>
    <label class="block text-sm font-semibold mb-2">Email</label>
    <input type="email" value="<?= esc($userEmail ?? '') ?>"
           class="w-full border rounded-lg px-3 py-2 bg-gray-100 text-gray-600" readonly>
  </div>

  <!-- Informasi Profil -->
  <div>
    <h4 class="text-md font-semibold text-gray-800 mb-3 border-b pb-2">Informasi Profil</h4>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-semibold mb-2">Nama</label>
        <input type="text" name="name"
               value="<?= esc(v('name', $sp['name'])) ?>"
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600" required>
      </div>
      <div>
        <label class="block text-sm font-semibold mb-2">No. Telepon</label>
        <input type="text" name="phone"
               value="<?= esc(v('phone', $sp['phone'])) ?>"
               class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-200 focus:border-blue-600">
      </div>
    </div>
  </div>

  <!-- Tombol -->
  <div class="flex justify-end gap-2 pt-4 border-t mt-4">
    <button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700" type="submit">Simpan</button>
    <button type="button" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200" @click="$store.ui.modal=null">Tutup</button>
  </div>
</form>

<script>
function previewImage(input, previewId) {
  let preview = document.getElementById(previewId);
  const file = input.files && input.files[0];
  if (!file) return;

  if (file.size > 2 * 1024 * 1024) { alert('Maksimal ukuran 2MB'); input.value = ''; return; }
  const allowed = ['image/jpeg','image/png','image/gif','image/webp'];
  if (!allowed.includes(file.type)) { alert('Format tidak didukung'); input.value = ''; return; }

  if (preview && preview.tagName.toLowerCase() !== 'img') {
    const img = document.createElement('img');
    img.id = previewId;
    img.alt = 'Foto Profil';
    img.className = 'w-24 h-24 rounded-full object-cover border-2 border-gray-300';
    preview.replaceWith(img);
    preview = img;
  }

  const reader = new FileReader();
  reader.onload = e => { preview.src = e.target.result; };
  reader.readAsDataURL(file);
}

function removeProfilePhoto(previewId, fileInputId, hiddenFlagId) {
  const current = document.getElementById(previewId);
  const fileInp = document.getElementById(fileInputId);
  const flag    = document.getElementById(hiddenFlagId);

  if (current) {
    const div = document.createElement('div');
    div.id = previewId;
    div.className = 'w-24 h-24 flex items-center justify-center rounded-full bg-gray-200 text-gray-500 border-2 border-gray-300';
    div.innerHTML = '<i class="fas fa-user text-3xl"></i>';
    current.replaceWith(div);
  }
  if (fileInp) fileInp.value = '';
  if (flag)    flag.value = '1';
}
</script>
