<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<?php
/* ================= Data dari controller ================= */
 $uid        = $user['id']        ?? $user['user_id'] ?? '';
 $username   = $user['username']  ?? '';
 $fullname   = $user['fullname']  ?? ($user['name'] ?? '');
 $phone      = $user['phone']     ?? ($user['no_telp'] ?? '');
 $email      = $user['email']     ?? '';

 $backUrl   = site_url('admin/users?tab=seo');
 $actionSEO = site_url('admin/users/'.$uid.'/update?role=seoteam');
?>

<!-- WRAPPER -->
<div class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out"
     :class="(sidebarOpen && (typeof isDesktop==='undefined' || isDesktop)) ? 'md:ml-64' : 'ml-0'"
     x-data="editUsersPage('seo','<?= $uid ?>-seo')">

  <!-- ===== BACKGROUND VIEWER (halaman Management Users di belakang popup) ===== -->
  <div class="absolute inset-0 z-0">
    <iframe src="<?= $backUrl ?>" class="w-full h-full border-0 bg-gray-50"></iframe>
  </div>

  <!-- ================= MODAL: EDIT SEO ================= -->
  <div class="fixed inset-0 z-[999] flex items-start justify-center p-3 sm:p-4"
       x-show="open && modalType==='seo'" x-transition.opacity
       @keydown.escape.prevent.stop="close()" @click.self="close()"
       role="dialog" aria-modal="true" aria-labelledby="dialog-title-seo" x-cloak>
    <div class="absolute inset-0 bg-black/60"></div>

    <div class="relative w-full sm:max-w-lg md:max-w-xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
         x-show="open && modalType==='seo'" x-transition.scale.origin.top>
      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-3.5">
        <div class="flex items-center justify-between">
          <h2 id="dialog-title-seo" class="text-lg sm:text-xl font-bold">Edit SEO</h2>
          <button type="button" class="p-2 hover:bg-white/10 rounded-full" @click="close()" aria-label="Tutup">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>
      </div>

      <!-- Body -->
      <div class="px-5 sm:px-6 py-4 space-y-4 overflow-y-auto">
        <form id="formEditSEO" action="<?= $actionSEO ?>" method="post" class="space-y-4" @submit="submitting=true" data-turbo="false">
          <?= csrf_field() ?>
          <input type="hidden" name="role" value="seoteam">

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-id-badge"></i></span>
              <input name="fullname" value="<?= esc($fullname) ?>" placeholder="Masukkan nama lengkap"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-user"></i></span>
              <input name="username" required value="<?= esc($username) ?>" placeholder="Masukkan username"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-phone"></i></span>
              <input name="phone" value="<?= esc($phone) ?>" placeholder="08xx xxxx xxxx"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" value="<?= esc($email) ?>" placeholder="email@contoh.com"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input id="toggleResetSEO" type="checkbox" class="h-4 w-4 text-blue-600 rounded border-gray-300" x-model="showResetSEO">
            <label for="toggleResetSEO" class="text-sm font-semibold text-gray-700">Ubah password</label>
          </div>

          <template x-if="showResetSEO">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Reset Password <span class="text-gray-400">(opsional)</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-lock"></i></span>
                <input :type="showPassSEO ? 'text' : 'password'" name="password" placeholder="Min. 8 karakter"
                       class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <button type="button" @click="showPassSEO=!showPassSEO" class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="showPassSEO ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
            </div>
          </template>
        </form>
      </div>

      <div class="px-5 sm:px-6 py-3 border-t border-gray-100 bg-white flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold" @click="close()">Batal</button>
        <button form="formEditSEO" type="submit"
                class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm disabled:opacity-60"
                :disabled="submitting">
          <span x-show="!submitting">Simpan Perubahan</span>
          <span x-show="submitting" class="inline-flex items-center gap-2"><i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...</span>
        </button>
      </div>
    </div>
  </div>
</div>

<script>
function editUsersPage(initialType, pageKey) {
  return {
    key: pageKey,
    open: true,
    modalType: initialType, // 'seo'
    submitting: false,

    showResetSEO: false, showPassSEO: false,

    close(){
      // Tutup popup + pulihkan scroll…
      this.open = false;
      document.body.style.overflow = '';
      // …lalu kembali ke halaman Management Users (tanpa halaman putih).
      setTimeout(() => {
        const to = "<?= $backUrl ?>";
        try { history.back(); } catch(e) { window.location.replace(to); return; }
        // Safety fallback jika masih di halaman ini 150ms kemudian:
        setTimeout(() => {
          if (location.pathname.indexOf('/admin/users') === -1) window.location.replace(to);
        }, 150);
      }, 100);
    },

    init(){
      // Pastikan modalType mengikuti hasil server (atasi cache)
      this.$nextTick(() => { this.modalType = 'seo'; this.open = true; });

      // Kunci scroll saat terbuka
      this.$watch('open', v => document.body.style.overflow = v ? 'hidden' : '');

      // Setup riwayat agar tombol BACK menutup modal
      const marker = '#modal';
      if (!location.hash.includes('modal')) {
        history.replaceState({ modalSeed: true }, '', location.href);
        history.pushState({ modalOpen: true }, '', location.href + marker);
      }

      window.addEventListener('popstate', () => {
        if (this.open) {
          this.open = false;
          document.body.style.overflow = '';
          setTimeout(() => window.location.replace("<?= $backUrl ?>"), 80);
        }
      }, { once: true });
    }
  }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>