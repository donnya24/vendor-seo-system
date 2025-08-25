<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col min-h-screen bg-gray-50"
     :class="sidebarOpen && (typeof isDesktop === 'undefined' || isDesktop) ? 'md:ml-64' : 'md:ml-0'"
     x-data="newUserForm()">

  <!-- PAGE HEADER -->
  <div class="px-4 md:px-6 pt-4 md:pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-[26px] font-bold text-gray-900">Add SEO</h1>
        <p class="text-sm text-gray-500 mt-0.5">Buat akun baru untuk Tim SEO</p>
      </div>

      <div class="flex items-center gap-2 sm:gap-3">
        <a href="<?= site_url('admin/users'); ?>"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
          <i class="fa-solid fa-arrow-left text-[12px]"></i>
          Kembali
        </a>
      </div>
    </div>

    <!-- Flash message -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mt-3 p-3 rounded-lg bg-emerald-50 text-emerald-800 text-sm border border-emerald-200">
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
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

  <!-- FORM CARD -->
  <main class="flex-1 px-4 md:px-6 pb-10 mt-3">
    <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
      <!-- Card header -->
      <div class="px-4 md:px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-700">
        <h2 class="text-white font-semibold text-sm md:text-base flex items-center gap-2">
          <i class="fa-solid fa-user-plus"></i> Form SEO Baru
        </h2>
      </div>

      <!-- Card body -->
      <div class="p-4 md:p-6">
        <form action="<?= site_url('admin/users/store'); ?>" method="post" class="space-y-5" data-turbo="false">
          <?= csrf_field() ?>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Fullname -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-id-badge"></i>
                </span>
                <input name="fullname" required placeholder="Masukkan nama lengkap"
                       value="<?= old('fullname') ?>"
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
                <input name="username" required placeholder="Masukkan username"
                       value="<?= old('username') ?>"
                       class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              </div>
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">No. Telepon</label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-phone"></i>
                </span>
                <input name="phone" placeholder="08xx xxxx xxxx"
                       value="<?= old('phone') ?>"
                       class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              </div>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-regular fa-envelope"></i>
                </span>
                <input type="email" name="email" required placeholder="email@contoh.com"
                       value="<?= old('email') ?>"
                       class="w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
              </div>
            </div>

            <!-- Role: fixed ke seoteam -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Role <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-layer-group"></i>
                </span>
                <!-- Disabled agar tidak bisa diubah -->
                <select disabled
                        class="appearance-none w-full bg-gray-50 text-gray-700 pl-10 pr-9 py-2.5 rounded-lg border border-gray-200 cursor-not-allowed">
                  <option value="seoteam" selected>seoteam</option>
                </select>
                <!-- Hidden input agar nilai tetap terkirim -->
                <input type="hidden" name="role" value="seoteam">
                <span class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 pointer-events-none">
                  <i class="fa-solid fa-lock text-xs"></i>
                </span>
              </div>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Password <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock"></i>
                </span>
                <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8"
                       placeholder="Minimal 8 karakter"
                       class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <button type="button" @click="showPass=!showPass"
                        class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="showPass ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
              <p class="text-xs text-gray-500 mt-1">Gunakan kombinasi huruf, angka, dan simbol.</p>
            </div>

            <!-- Confirm Password -->
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
              <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                  <i class="fa-solid fa-lock-keyhole"></i>
                </span>
                <input :type="showConfirm ? 'text' : 'password'" name="password_confirm" required minlength="8"
                       placeholder="Ulangi password"
                       class="w-full pl-10 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <button type="button" @click="showConfirm=!showConfirm"
                        class="absolute inset-y-0 right-0 pr-3 text-gray-400 hover:text-gray-600">
                  <i :class="showConfirm ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye'"></i>
                </button>
              </div>
            </div>
          </div>

          <!-- Footer actions -->
          <div class="pt-2 flex items-center justify-end gap-2">
            <a href="<?= site_url('admin/users'); ?>"
               class="px-4 py-2.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold">
              Batal
            </a>
            <button type="submit"
                    class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold shadow-sm">
              Simpan User
            </button>
          </div>
        </form>
      </div>
    </section>
  </main>
</div>

<script>
function newUserForm() {
  return {
    // paksa role 'seoteam' supaya vendor status tidak pernah muncul
    role: 'seoteam',
    showPass: false,
    showConfirm: false,
  }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>
