<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Register | Vendor Partnership & SEO Performance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    html,body{height:100%}body{font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
  </style>
</head>
<body class="bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image:url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card diperkecil & mobile-first -->
    <div class="relative z-10 w-[92%] max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
         x-data="{
           vendor_name: '<?= esc(old('vendor_name') ?? '') ?>',
           email: '<?= esc(old('email') ?? '') ?>',
           password: '',
           confirm: '',
           showPw: false,
           showCf: false,
           get okPw(){ return this.password.length >= 8 },
           get match(){ return this.password !== '' && this.password === this.confirm },
           get canSubmit(){ return this.vendor_name.trim() && this.email.trim() && this.okPw && this.match }
         }">

      <div class="px-6 pt-8 pb-5 text-center border-b">
        <div class="mx-auto w-12 h-12 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-7 h-7" />
        </div>
        <h1 class="mt-3 text-xl sm:text-2xl font-bold text-gray-800">Buat Akun Vendor</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Kelola profil, leads & laporan</p>
      </div>

      <form action="<?= site_url('register') ?>" method="post" class="px-6 py-6 sm:py-7 space-y-5" novalidate>
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="text-red-600 text-sm"><?= session()->getFlashdata('error') ?></div>
        <?php elseif (session()->getFlashdata('message')): ?>
          <div class="text-green-600 text-sm"><?= session()->getFlashdata('message') ?></div>
        <?php endif; ?>

        <!-- Nama Vendor -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Vendor</label>
          <input type="text" name="vendor_name" x-model="vendor_name"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                 placeholder="Nama perusahaan / brand" required enterkeyhint="next" />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
          <input type="email" name="email" x-model="email"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                 placeholder="you@example.com" required autocomplete="username" inputmode="email" autocapitalize="none" enterkeyhint="next" />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
          <div class="relative">
            <input :type="showPw ? 'text' : 'password'" name="password" x-model="password"
                   class="w-full pr-20 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                   placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-4 flex items-center text-sm text-gray-500 hover:text-gray-700"
                    @click="showPw = !showPw" x-text="showPw ? 'Sembunyikan' : 'Tampilkan'"></button>
          </div>
          <p class="mt-1 text-xs" :class="okPw ? 'text-green-600' : 'text-gray-500'"
             x-text="okPw ? 'Kuat.' : 'Minimal 8 karakter.'"></p>
        </div>

        <!-- Konfirmasi Password -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1.5">Konfirmasi Password</label>
          <div class="relative">
            <input :type="showCf ? 'text' : 'password'" name="pass_confirm" x-model="confirm"
                   class="w-full pr-20 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                   placeholder="Ulangi password" required autocomplete="new-password" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-4 flex items-center text-sm text-gray-500 hover:text-gray-700"
                    @click="showCf = !showCf" x-text="showCf ? 'Sembunyikan' : 'Tampilkan'"></button>
          </div>
          <p class="mt-1 text-xs" :class="match ? 'text-green-600' : 'text-gray-500'"
             x-text="match ? 'Cocok.' : 'Ketik ulang sama persis.'"></p>
        </div>

        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="!canSubmit">
          Daftar
        </button>

        <p class="text-center text-sm text-gray-600">
          Sudah punya akun?
          <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">Masuk</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>
