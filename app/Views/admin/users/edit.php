<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<?php
  // Data dari controller
  $uid       = $user['id']        ?? '';
  $username  = $user['username']  ?? '';
  $fullname  = $user['fullname']  ?? '';
  $phone     = $user['phone']     ?? ($user['no_telp'] ?? '');
  $email     = $user['email']     ?? '';
  $groupsArr = array_map('strtolower', (array)($groups ?? []));

  // 1) Deteksi role dari daftar grup (tidak hanya index 0)
  $roleDetected = 'vendor';
  if (in_array('seoteam', $groupsArr, true)) { $roleDetected = 'seoteam'; }
  elseif (in_array('vendor', $groupsArr, true)) { $roleDetected = 'vendor'; }
  elseif (in_array('admin', $groupsArr, true)) { $roleDetected = 'admin'; }

  // 2) Izinkan override lewat query (?role=seoteam|vendor|admin) untuk konsistensi heading
  $roleFromUrl = isset($_GET['role']) ? strtolower($_GET['role']) : null;
  if (in_array($roleFromUrl, ['admin','seoteam','vendor'], true)) {
    $roleKey = $roleFromUrl;
  } else {
    $roleKey = $roleDetected;
  }

  // 3) Heading & isian username
  $roleTitle = $roleKey === 'seoteam' ? 'Edit SEO'
             : ($roleKey === 'vendor' ? 'Edit Vendor' : 'Edit Admin');

  // Kosongkan username khusus SEO agar user wajib mengisi
  $usernameValue = ($roleKey === 'seoteam') ? '' : $username;

  $backUrl = site_url('admin/users');
?>

<div class="flex-1 flex flex-col min-h-screen bg-gray-50"
     :class="sidebarOpen && (typeof isDesktop==='undefined' || isDesktop) ? 'md:ml-64' : 'md:ml-0'"
     x-data="editUserModal()">

  <!-- Overlay -->
  <div class="fixed inset-0 z-[999] flex items-start justify-center p-3 sm:p-4"
       x-show="open" x-transition.opacity
       @keydown.escape.prevent.stop="close()" @click.self="close()"
       aria-labelledby="dialog-title" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Modal card -->
    <div class="relative w-full sm:max-w-lg md:max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden
                max-h[90vh] max-h-[90vh] flex flex-col"
         x-show="open" x-transition.scale.origin.top>
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-3.5">
        <div class="flex items-center justify-between">
          <h2 id="dialog-title" class="text-lg sm:text-xl font-bold"><?= esc($roleTitle) ?></h2>
          <button type="button" class="p-2 hover:bg-white/10 rounded-full" @click="close()" aria-label="Tutup">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="px-5 sm:px-6 py-4 space-y-4 overflow-y-auto">
        <form id="editUserForm"
              action="<?= site_url('admin/users/'.$uid.'/update'); ?>"
              method="post" class="space-y-4" @submit="submitting=true">
          <?= csrf_field() ?>

          <!-- Nama -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fa-regular fa-id-badge"></i>
              </span>
              <input name="fullname" value="<?= esc($fullname) ?>" placeholder="Masukkan nama lengkap"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <!-- Username -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fa-regular fa-user"></i>
              </span>
              <input name="username" required value="<?= esc($usernameValue) ?>"
                     placeholder="Masukkan username"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <!-- Telepon -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fa-solid fa-phone"></i>
              </span>
              <input name="phone" value="<?= esc($phone) ?>" placeholder="08xx xxxx xxxx"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fa-regular fa-envelope"></i>
              </span>
              <input type="email" name="email" value="<?= esc($email) ?>" placeholder="email@contoh.com"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <!-- Role (tetap bisa diubah jika controller mengizinkan) -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <i class="fa-solid fa-layer-group"></i>
              </span>
              <select name="role"
                      class="appearance-none w-full pl-10 pr-9 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <option value="admin"   <?= $roleKey==='admin'?'selected':''; ?>>admin</option>
                <option value="seoteam" <?= $roleKey==='seoteam'?'selected':''; ?>>seoteam</option>
                <option value="vendor"  <?= $roleKey==='vendor'?'selected':''; ?>>vendor</option>
              </select>
              <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                <i class="fa-solid fa-chevron-down text-xs"></i>
              </span>
            </div>
          </div>

          <!-- Toggle reset password -->
          <div class="flex items-center gap-2 pt-2">
            <input id="toggleReset" type="checkbox" class="h-4 w-4 text-blue-600 rounded border-gray-300" x-model="showReset">
            <label for="toggleReset" class="text-sm font-semibold text-gray-700">Ubah password</label>
          </div>

          <template x-if="showReset">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Reset Password <span class="text-gray-400">(opsional)</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock"></i>
                </span>
                <input :type="showPass ? 'text' : 'password'" name="password" placeholder="Biarkan kosong jika tidak diganti"
                       class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <button type="button" @click="showPass=!showPass"
                        class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="showPass ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
              <p class="text-xs text-gray-500 mt-1">Gunakan minimal 8 karakter dengan kombinasi huruf & angka.</p>
            </div>
          </template>
        </form>
      </div>

      <!-- Footer -->
      <div class="px-5 sm:px-6 py-3 border-t border-gray-100 bg-white flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold" @click="close()">Batal</button>
        <button form="editUserForm" type="submit"
                class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="submitting">
          <span x-show="!submitting">Simpan Perubahan</span>
          <span x-show="submitting" class="inline-flex items-center gap-2">
            <i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...
          </span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function editUserModal() {
  return {
    open: true,
    showPass: false,
    showReset: false,
    submitting: false,
    close() {
      if (window.history.length > 1) { window.history.back(); }
      else { window.location.href = "<?= $backUrl ?>"; }
    },
    init() { this.$watch('open', v => document.body.style.overflow = v ? 'hidden' : ''); }
  }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>
