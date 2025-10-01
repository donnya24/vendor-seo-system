<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap"
     class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out"
     :class="(sidebarOpen && (typeof isDesktop==='undefined' || isDesktop)) ? 'md:ml-64' : 'ml-0'"
     x-data="newUserForm()"
     x-init="onInit()">

  <!-- HEADER -->
  <div class="px-4 md:px-6 pt-4 md:pt-6 max-w-screen-lg mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600">
            <i class="fa-solid fa-user-plus"></i>
          </span>
          <h1 class="text-xl md:text-2xl font-bold text-gray-900">Add Vendor</h1>
        </div>
        <p class="text-xs md:text-sm text-gray-500 mt-1">Buat akun baru untuk Vendor</p>
      </div>

      <a href="<?= site_url('admin/users?tab=vendor'); ?>"
         class="inline-flex items-center gap-2 bg-white/80 border border-gray-200 hover:bg-white text-gray-700 font-medium text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
        <i class="fa-solid fa-arrow-left text-[11px]"></i> Kembali
      </a>
    </div>

    <!-- Flash -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mt-3 p-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
      <script>try{ localStorage.removeItem('userMgmtHidden_v5'); }catch(e){}</script>
    <?php elseif (session()->getFlashdata('error')): ?>
      <div class="mt-3 p-3 rounded-lg bg-rose-50 text-rose-800 text-sm border border-rose-200">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()): ?>
      <div class="mt-3 p-3 rounded-lg bg-amber-50 text-amber-800 text-sm border border-amber-200">
        <ul class="list-disc list-inside">
          <?php foreach ($validation->getErrors() as $err): ?>
            <li><?= esc($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

  <!-- MAIN -->
  <main class="flex-1 px-4 md:px-6 pb-10 mt-3 max-w-screen-lg mx-auto w-full">
    <section class="relative overflow-hidden rounded-2xl border border-gray-100 shadow-sm bg-white">
      <div class="h-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700"></div>

      <div class="px-4 md:px-6 py-3 bg-white">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600">
            <i class="fa-solid fa-users"></i>
          </span>
          <h2 class="text-sm md:text-base font-semibold text-gray-800">Form Vendor Baru</h2>
        </div>
      </div>

      <div class="px-4 md:px-6 pb-5 pt-2">
        <div class="mb-4 text-[12px] text-gray-500">
          Lengkapi data berikut. Bidang bertanda <span class="text-red-500 font-semibold">*</span> wajib diisi.
        </div>

        <form action="<?= site_url('admin/users/store'); ?>" method="post" data-turbo="false" @submit="beforeSubmit">
          <?= csrf_field() ?>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Fullname -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-id-badge text-sm"></i>
                </span>
                <input name="fullname" required placeholder="Masukkan nama lengkap" value="<?= old('fullname') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Username -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-user text-sm"></i>
                </span>
                <input name="username" required placeholder="Masukkan username" value="<?= old('username') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-phone text-sm"></i>
                </span>
                <input name="phone" placeholder="08xx xxxx xxxx" value="<?= old('phone') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-envelope text-sm"></i>
                </span>
                <input type="email" name="email" required placeholder="email@contoh.com" value="<?= old('email') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Role tetap vendor -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-layer-group text-sm"></i>
                </span>
                <select disabled class="appearance-none w-full bg-gray-50 text-gray-700 pl-10 pr-9 py-2.5 text-sm rounded-xl border border-gray-200 cursor-not-allowed">
                  <option value="vendor" selected>vendor</option>
                </select>
                <input type="hidden" name="role" value="vendor">
                <input type="hidden" name="groups[]" value="vendor">
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                  <i class="fa-solid fa-lock text-xs"></i>
                </span>
              </div>
            </div>

            <!-- Password -->
            <div x-data="{show:false}">
              <label class="block text-xs font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock text-sm"></i>
                </span>
                <input :type="show ? 'text' : 'password'" name="password" required minlength="8" x-ref="pass"
                       placeholder="Minimal 8 karakter"
                       class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="new-password">
                <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
              <p class="text-[11px] text-gray-500 mt-1">Gunakan kombinasi huruf, angka, dan simbol.</p>
            </div>

            <!-- Konfirmasi -->
            <div x-data="{show:false}">
              <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock-keyhole text-sm"></i>
                </span>
                <input :type="show ? 'text' : 'password'" name="password_confirm" required minlength="8" x-ref="confirm"
                       placeholder="Ulangi password"
                       class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="new-password">
                <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Field khusus vendor -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-circle-check text-sm"></i></span>
                <select name="vendor_status"
                        class="appearance-none w-full pl-10 pr-9 py-2.5 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                  <option value="active" <?= old('vendor_status') === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="suspended" <?= old('vendor_status') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                  <option value="pending" <?= old('vendor_status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                  <i class="fa-solid fa-chevron-down text-xs"></i>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Verified</label>
              <label class="inline-flex items-center gap-2 h-[42px] px-3 rounded-lg border border-gray-300 cursor-pointer">
                <input type="checkbox" name="is_verified" value="1" <?= old('is_verified') ? 'checked' : '' ?> class="h-4 w-4 text-emerald-600 rounded border-gray-300">
                <span class="text-sm text-gray-700">Terverifikasi</span>
              </label>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Komisi (%)</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-percent text-sm"></i></span>
                <input type="number" step="0.01" min="0" max="100" name="commission_rate"
                       value="<?= old('commission_rate') ?>" placeholder="cth: 10"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
              </div>
            </div>
          </div>

          <!-- Alias hidden (aman bila backend butuh nama lain) -->
          <input type="hidden" name="no_telp"        x-ref="aliasNoTelp">
          <input type="hidden" name="no_hp"          x-ref="aliasNoHp">
          <input type="hidden" name="email_address"  x-ref="aliasEmailAddr">

          <div class="my-5 border-t border-dashed border-gray-200"></div>

          <div class="flex items-center justify-between">
            <div class="text-[11px] text-gray-500">Pastikan data sudah benar sebelum menyimpan.</div>
            <div class="flex items-center gap-2">
              <a href="<?= site_url('admin/users?tab=vendor'); ?>" class="px-4 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold shadow-sm">Batal</a>
              <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-md">Simpan User</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </main>
</div>

<script>
function newUserForm() {
  return {
    onInit(){},
    beforeSubmit(event){
      // validasi password
      const p = this.$refs?.pass?.value || '';
      const c = this.$refs?.confirm?.value || '';
      if (p !== c) {
        event.preventDefault();
        alert('Konfirmasi password tidak sama.');
        this.$refs?.confirm?.focus();
        return false;
      }

      // sinkron alias
      const phoneVal = (document.querySelector('input[name="phone"]')?.value || '').trim();
      const emailVal = (document.querySelector('input[name="email"]')?.value || '').trim();
      if (this.$refs.aliasNoTelp)     this.$refs.aliasNoTelp.value    = phoneVal;
      if (this.$refs.aliasNoHp)       this.$refs.aliasNoHp.value      = phoneVal;
      if (this.$refs.aliasEmailAddr)  this.$refs.aliasEmailAddr.value = emailVal;

      // cache ringan untuk fallback tampilan tabel
      try {
        const username = (document.querySelector('input[name="username"]')?.value || '').trim();
        if (username) {
          const k = 'userInfoCache_v1';
          const data = JSON.parse(localStorage.getItem(k) || '{}');
          data[username] = { phone: phoneVal, email: emailVal, ts: Date.now() };
          localStorage.setItem(k, JSON.stringify(data));
        }
      } catch(e){}
<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap"
     class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out"
     :class="(sidebarOpen && (typeof isDesktop==='undefined' || isDesktop)) ? 'md:ml-64' : 'ml-0'"
     x-data="newUserForm()"
     x-init="onInit()">

  <!-- HEADER -->
  <div class="px-4 md:px-6 pt-4 md:pt-6 max-w-screen-lg mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-100 text-emerald-600">
            <i class="fa-solid fa-user-plus"></i>
          </span>
          <h1 class="text-xl md:text-2xl font-bold text-gray-900">Add Vendor</h1>
        </div>
        <p class="text-xs md:text-sm text-gray-500 mt-1">Buat akun baru untuk Vendor</p>
      </div>

      <a href="<?= site_url('admin/users?tab=vendor'); ?>"
         class="inline-flex items-center gap-2 bg-white/80 border border-gray-200 hover:bg-white text-gray-700 font-medium text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
        <i class="fa-solid fa-arrow-left text-[11px]"></i> Kembali
      </a>
    </div>

    <!-- Flash -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mt-3 p-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
      <script>try{ localStorage.removeItem('userMgmtHidden_v5'); }catch(e){}</script>
    <?php elseif (session()->getFlashdata('error')): ?>
      <div class="mt-3 p-3 rounded-lg bg-rose-50 text-rose-800 text-sm border border-rose-200">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($validation) && $validation->getErrors()): ?>
      <div class="mt-3 p-3 rounded-lg bg-amber-50 text-amber-800 text-sm border border-amber-200">
        <ul class="list-disc list-inside">
          <?php foreach ($validation->getErrors() as $err): ?>
            <li><?= esc($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

  <!-- MAIN -->
  <main class="flex-1 px-4 md:px-6 pb-10 mt-3 max-w-screen-lg mx-auto w-full">
    <section class="relative overflow-hidden rounded-2xl border border-gray-100 shadow-sm bg-white">
      <div class="h-3 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700"></div>

      <div class="px-4 md:px-6 py-3 bg-white">
        <div class="flex items-center gap-2">
          <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-50 text-emerald-600">
            <i class="fa-solid fa-users"></i>
          </span>
          <h2 class="text-sm md:text-base font-semibold text-gray-800">Form Vendor Baru</h2>
        </div>
      </div>

      <div class="px-4 md:px-6 pb-5 pt-2">
        <div class="mb-4 text-[12px] text-gray-500">
          Lengkapi data berikut. Bidang bertanda <span class="text-red-500 font-semibold">*</span> wajib diisi.
        </div>

        <form action="<?= site_url('admin/users/store'); ?>" method="post" data-turbo="false" @submit="beforeSubmit">
          <?= csrf_field() ?>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Fullname -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-id-badge text-sm"></i>
                </span>
                <input name="fullname" required placeholder="Masukkan nama lengkap" value="<?= old('fullname') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Username -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-user text-sm"></i>
                </span>
                <input name="username" required placeholder="Masukkan username" value="<?= old('username') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">No. Telepon</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-phone text-sm"></i>
                </span>
                <input name="phone" placeholder="08xx xxxx xxxx" value="<?= old('phone') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-envelope text-sm"></i>
                </span>
                <input type="email" name="email" required placeholder="email@contoh.com" value="<?= old('email') ?>"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="off">
              </div>
            </div>

            <!-- Role tetap vendor -->
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-layer-group text-sm"></i>
                </span>
                <select disabled class="appearance-none w-full bg-gray-50 text-gray-700 pl-10 pr-9 py-2.5 text-sm rounded-xl border border-gray-200 cursor-not-allowed">
                  <option value="vendor" selected>vendor</option>
                </select>
                <input type="hidden" name="role" value="vendor">
                <input type="hidden" name="groups[]" value="vendor">
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                  <i class="fa-solid fa-lock text-xs"></i>
                </span>
              </div>
            </div>

            <!-- Password -->
            <div x-data="{show:false}">
              <label class="block text-xs font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock text-sm"></i>
                </span>
                <input :type="show ? 'text' : 'password'" name="password" required minlength="8" x-ref="pass"
                       placeholder="Minimal 8 karakter"
                       class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="new-password">
                <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
              <p class="text-[11px] text-gray-500 mt-1">Gunakan kombinasi huruf, angka, dan simbol.</p>
            </div>

            <!-- Konfirmasi -->
            <div x-data="{show:false}">
              <label class="block text-xs font-semibold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock-keyhole text-sm"></i>
                </span>
                <input :type="show ? 'text' : 'password'" name="password_confirm" required minlength="8" x-ref="confirm"
                       placeholder="Ulangi password"
                       class="w-full pl-10 pr-10 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-emerald-500/70 focus:border-emerald-500 transition-shadow" autocomplete="new-password">
                <button type="button" @click="show=!show" class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="show ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Field khusus vendor -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mt-4">
            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-regular fa-circle-check text-sm"></i></span>
                <select name="vendor_status"
                        class="appearance-none w-full pl-10 pr-9 py-2.5 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
                  <option value="active" <?= old('vendor_status') === 'active' ? 'selected' : '' ?>>Active</option>
                  <option value="suspended" <?= old('vendor_status') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                  <option value="pending" <?= old('vendor_status') === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                  <i class="fa-solid fa-chevron-down text-xs"></i>
                </span>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Verified</label>
              <label class="inline-flex items-center gap-2 h-[42px] px-3 rounded-lg border border-gray-300 cursor-pointer">
                <input type="checkbox" name="is_verified" value="1" <?= old('is_verified') ? 'checked' : '' ?> class="h-4 w-4 text-emerald-600 rounded border-gray-300">
                <span class="text-sm text-gray-700">Terverifikasi</span>
              </label>
            </div>

            <div>
              <label class="block text-xs font-semibold text-gray-700 mb-1">Komisi (%)</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fa-solid fa-percent text-sm"></i></span>
                <input type="number" step="0.01" min="0" max="100" name="commission_rate"
                       value="<?= old('commission_rate') ?>" placeholder="cth: 10"
                       class="w-full pl-10 pr-3 py-2.5 text-sm rounded-lg border border-gray-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
              </div>
            </div>
          </div>

          <!-- Alias hidden (aman bila backend butuh nama lain) -->
          <input type="hidden" name="no_telp"        x-ref="aliasNoTelp">
          <input type="hidden" name="no_hp"          x-ref="aliasNoHp">
          <input type="hidden" name="email_address"  x-ref="aliasEmailAddr">

          <div class="my-5 border-t border-dashed border-gray-200"></div>

          <div class="flex items-center justify-between">
            <div class="text-[11px] text-gray-500">Pastikan data sudah benar sebelum menyimpan.</div>
            <div class="flex items-center gap-2">
              <a href="<?= site_url('admin/users?tab=vendor'); ?>" class="px-4 py-2.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 font-semibold shadow-sm">Batal</a>
              <button type="submit" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold shadow-md">Simpan User</button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </main>
</div>

<script>
function newUserForm() {
  return {
    onInit(){},
    beforeSubmit(event){
      // validasi password
      const p = this.$refs?.pass?.value || '';
      const c = this.$refs?.confirm?.value || '';
      if (p !== c) {
        event.preventDefault();
        alert('Konfirmasi password tidak sama.');
        this.$refs?.confirm?.focus();
        return false;
      }

      // sinkron alias
      const phoneVal = (document.querySelector('input[name="phone"]')?.value || '').trim();
      const emailVal = (document.querySelector('input[name="email"]')?.value || '').trim();
      if (this.$refs.aliasNoTelp)     this.$refs.aliasNoTelp.value    = phoneVal;
      if (this.$refs.aliasNoHp)       this.$refs.aliasNoHp.value      = phoneVal;
      if (this.$refs.aliasEmailAddr)  this.$refs.aliasEmailAddr.value = emailVal;

      // cache ringan untuk fallback tampilan tabel
      try {
        const username = (document.querySelector('input[name="username"]')?.value || '').trim();
        if (username) {
          const k = 'userInfoCache_v1';
          const data = JSON.parse(localStorage.getItem(k) || '{}');
          data[username] = { phone: phoneVal, email: emailVal, ts: Date.now() };
          localStorage.setItem(k, JSON.stringify(data));
        }
      } catch(e){}

      // bersihkan cache "hapus tampilan"
      try { localStorage.removeItem('userMgmtHidden_v5'); } catch(e) {}

      return true;
    },
  }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>
      // bersihkan cache "hapus tampilan"
      try { localStorage.removeItem('userMgmtHidden_v5'); } catch(e) {}

      return true;
    },
  }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>