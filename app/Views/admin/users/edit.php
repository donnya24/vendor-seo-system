<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<?php
/* ================= Helpers normalisasi ================= */
if (!function_exists('norm_list')) {
  function norm_list($src) {
    $out = [];
    if (is_string($src)) $src = preg_split('/[,\s]+/', $src, -1, PREG_SPLIT_NO_EMPTY);
    foreach ((array)$src as $it) {
      if (is_string($it)) $name = $it;
      elseif (is_array($it) && isset($it['name'])) $name = $it['name'];
      elseif (is_object($it) && isset($it->name)) $name = $it->name;
      else continue;
      $out[] = strtolower(trim($name));
    }
    return $out;
  }
}
if (!function_exists('canon_role')) {
  function canon_role($v) {
    $v = strtolower(trim((string)$v));
    if (in_array($v, ['seoteam','seo','seo_team','team_seo'], true)) return 'seoteam';
    if (in_array($v, ['vendor','vend'], true)) return 'vendor';
    if ($v === 'admin') return 'admin';
    return null;
  }
}
if (!function_exists('first_nonnull')) {
  function first_nonnull(...$vals) { foreach ($vals as $v) if ($v !== null && $v !== '') return $v; return null; }
}

/* ================= Data dari controller ================= */
$uid        = $user['id']        ?? $user['user_id'] ?? '';
$username   = $user['username']  ?? '';
$fullname   = $user['fullname']  ?? '';
$phone      = $user['phone']     ?? ($user['no_telp'] ?? '');
$email      = $user['email']     ?? '';

$userRole1  = canon_role($user['role'] ?? null);
$userRoles2 = norm_list($user['roles']  ?? []);
$userGroups = norm_list($user['groups'] ?? []);
$groupsGlob = norm_list($groups        ?? []);

/* ---- vendor profile (jika ada) ---- */
$vp               = $vendorProfile ?? [];
$vendorStatus     = strtolower($vp['status'] ?? $vp['vendor_status'] ?? 'active');
$vendorIsVerified = (int)($vp['is_verified'] ?? 0) === 1;
$commissionRate   = is_numeric($vp['commission_rate'] ?? null) ? (float)$vp['commission_rate'] : null;

/* ================ Deteksi role (prioritas) ================ */
// 1) Query ?role= (PALING UTAMA)
$roleQ = isset($_GET['role']) ? canon_role($_GET['role']) : null;

// 2) Dari user.role
$roleFromUser = $userRole1;

// 3) Dari daftar roles / groups / groups global
$roleFromLists = null;
if (in_array('seoteam', $userRoles2, true) || in_array('seoteam', $userGroups, true) || in_array('seoteam', $groupsGlob, true)) {
  $roleFromLists = 'seoteam';
} elseif (in_array('vendor', $userRoles2, true) || in_array('vendor', $userGroups, true) || in_array('vendor', $groupsGlob, true)) {
  $roleFromLists = 'vendor';
} elseif (in_array('admin',  $userRoles2, true) || in_array('admin',  $userGroups, true) || in_array('admin',  $groupsGlob, true)) {
  $roleFromLists = 'admin';
}

// 4) Heuristik vendorProfile — hanya jika memang ada data bermakna
$hasVendorProfile = is_array($vp) && (
  (isset($vp['vendor_status'])   && $vp['vendor_status']   !== '' && $vp['vendor_status'] !== null) ||
  (isset($vp['status'])          && $vp['status']          !== '' && $vp['status']        !== null) ||
  (isset($vp['is_verified'])     && ((int)$vp['is_verified'] === 1)) ||
  (isset($vp['commission_rate']) && $vp['commission_rate'] !== '' && $vp['commission_rate'] !== null)
);
$roleFromHeur = $hasVendorProfile ? 'vendor' : null;

// 5) Gabung prioritas (fallback NETRAL → 'seoteam' supaya tidak selalu vendor)
$roleCanonical = first_nonnull($roleQ, $roleFromUser, $roleFromLists, $roleFromHeur, 'seoteam');

// Admin diarahkan ke SEO (file ini hanya pisahkan SEO vs Vendor)
if ($roleCanonical === 'admin') $roleCanonical = 'seoteam';

/* ======== PISAHKAN DATA UNTUK MASING-MASING FORM ======== */
$isSEO    = ($roleCanonical === 'seoteam');
$isVendor = ($roleCanonical === 'vendor');

/* dataset SEO (form 1) — kosong bila bukan SEO supaya tidak bawa data vendor */
$seoFullname  = $isSEO ? $fullname : '';
$seoPhone     = $isSEO ? $phone    : '';
$seoEmail     = $isSEO ? $email    : '';
$usernameSEO  = ''; // SEO: paksa isi username baru

/* dataset Vendor (form 2) */
$venFullname  = $isVendor ? $fullname : '';
$venPhone     = $isVendor ? $phone    : '';
$venEmail     = $isVendor ? $email    : '';
$usernameVEN  = $isVendor ? $username : '';

if (!$isVendor) { // normalkan nilai vendor saat buka SEO
  $vendorStatus     = 'active';
  $vendorIsVerified = false;
  $commissionRate   = null;
}

/* Alpine modal: modal pertama mengikuti role */
$modalType = $isSEO ? 'seo' : 'vendor';

$backUrl   = site_url('admin/users');
$actionSEO = site_url('admin/users/'.$uid.'/update?role=seoteam');
$actionVEN = site_url('admin/users/'.$uid.'/update?role=vendor');
$pageKey   = $uid . '-' . $modalType;
?>

<!-- WRAPPER -->
<div class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out"
     :class="(sidebarOpen && (typeof isDesktop==='undefined' || isDesktop)) ? 'md:ml-64' : 'ml-0'"
     x-data="editUsersPage('<?= esc($modalType) ?>','<?= esc($pageKey) ?>')">

  <!-- ================= MODAL: EDIT SEO (Form 1) ================= -->
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
              <input name="fullname" value="<?= esc($seoFullname) ?>" placeholder="Masukkan nama lengkap"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-user"></i></span>
              <input name="username" required value="<?= esc($usernameSEO) ?>" placeholder="Masukkan username baru"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-phone"></i></span>
              <input name="phone" value="<?= esc($seoPhone) ?>" placeholder="08xx xxxx xxxx"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" value="<?= esc($seoEmail) ?>" placeholder="email@contoh.com"
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

  <!-- ================= MODAL: EDIT VENDOR (Form 2) ================= -->
  <div class="fixed inset-0 z-[999] flex items-start justify-center p-3 sm:p-4"
       x-show="open && modalType==='vendor'" x-transition.opacity
       @keydown.escape.prevent.stop="close()" @click.self="close()"
       role="dialog" aria-modal="true" aria-labelledby="dialog-title-vendor" x-cloak>
    <div class="absolute inset-0 bg-black/60"></div>

    <div class="relative w-full sm:max-w-xl md:max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
         x-show="open && modalType==='vendor'" x-transition.scale.origin.top>
      <div class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white px-5 py-3.5">
        <div class="flex items-center justify-between">
          <h2 id="dialog-title-vendor" class="text-lg sm:text-xl font-bold">Edit Vendor</h2>
          <button type="button" class="p-2 hover:bg-white/10 rounded-full" @click="close()" aria-label="Tutup">
            <i class="fa-solid fa-xmark text-xl"></i>
          </button>
        </div>
      </div>

      <div class="px-5 sm:px-6 py-4 space-y-4 overflow-y-auto">
        <form id="formEditVendor" action="<?= $actionVEN ?>" method="post" class="space-y-4" @submit="submitting=true" data-turbo="false">
          <?= csrf_field() ?>
          <input type="hidden" name="role" value="vendor">

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-id-badge"></i></span>
              <input name="fullname" value="<?= esc($venFullname) ?>" placeholder="Masukkan nama lengkap"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-user"></i></span>
              <input name="username" required value="<?= esc($usernameVEN) ?>" placeholder="Masukkan username"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-phone"></i></span>
              <input name="phone" value="<?= esc($venPhone) ?>" placeholder="08xx xxxx xxxx"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-envelope"></i></span>
              <input type="email" name="email" value="<?= esc($venEmail) ?>" placeholder="email@contoh.com"
                     class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            </div>
          </div>

          <!-- Field khusus vendor -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-circle-check"></i></span>
                <select name="vendor_status"
                        class="appearance-none w-full pl-10 pr-9 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                  <option value="active"    <?= $vendorStatus==='active'?'selected':''; ?>>Active</option>
                  <option value="suspended" <?= $vendorStatus==='suspended'?'selected':''; ?>>Suspended</option>
                  <option value="pending"   <?= $vendorStatus==='pending'?'selected':''; ?>>Pending</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                  <i class="fa-solid fa-chevron-down text-xs"></i>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Verified</label>
              <label class="inline-flex items-center gap-2 h-[42px] px-3 rounded-lg border border-gray-300 cursor-pointer">
                <input type="checkbox" name="is_verified" value="1" <?= $vendorIsVerified ? 'checked' : '' ?> class="h-4 w-4 text-blue-600 rounded border-gray-300">
                <span class="text-sm text-gray-700">Terverifikasi</span>
              </label>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Komisi (%)</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-percent"></i></span>
                <input type="number" step="0.01" min="0" max="100" name="commission_rate"
                       value="<?= $commissionRate !== null ? esc($commissionRate) : '' ?>" placeholder="cth: 10"
                       class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input id="toggleResetVEN" type="checkbox" class="h-4 w-4 text-blue-600 rounded border-gray-300" x-model="showResetVEN">
            <label for="toggleResetVEN" class="text-sm font-semibold text-gray-700">Ubah password</label>
          </div>

          <template x-if="showResetVEN">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Reset Password <span class="text-gray-400">(opsional)</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-lock"></i></span>
                <input :type="showPassVEN ? 'text' : 'password'" name="password" placeholder="Min. 8 karakter"
                       class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <button type="button" @click="showPassVEN=!showPassVEN" class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="showPassVEN ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
            </div>
          </template>
        </form>
      </div>

      <div class="px-5 sm:px-6 py-3 border-t border-gray-100 bg-white flex items-center justify-end gap-2">
        <button type="button" class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold" @click="close()">Batal</button>
        <button form="formEditVendor" type="submit"
                class="px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-sm disabled:opacity-60"
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
    modalType: initialType, // 'seo' | 'vendor'
    submitting: false,

    showResetSEO: false, showPassSEO: false,
    showResetVEN: false, showPassVEN: false,

    close(){
      if (history.length > 1) history.back();
      else location.href = "<?= $backUrl ?>";
    },
    init(){
      // Pastikan modalType mengikuti hasil server (mengatasi cache Turbo)
      this.$nextTick(() => { this.modalType = <?= json_encode($modalType) ?>; this.open = true; });
      this.$watch('open', v => document.body.style.overflow = v ? 'hidden' : '');
    }
  }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>
