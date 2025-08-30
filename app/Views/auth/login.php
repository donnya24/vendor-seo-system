<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Login | Vendor Partnership SEO Performance</title>
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

    <!-- Card ringkas -->
    <div class="relative z-10 w-[92%] max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
         x-data="{
           email: '<?= esc(old('email') ?? '') ?>',
           password: '',
           show: false,
           get validPw(){ return this.password.length >= 8 }
         }">

      <div class="px-6 pt-8 pb-5 text-center border-b">
        <div class="mx-auto w-12 h-12 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-7 h-7" />
        </div>
        <h1 class="mt-3 text-xl sm:text-2xl font-bold text-gray-800">Masuk ke Sistem</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Vendor Partnership & SEO Performance</p>
      </div>

      <form action="<?= site_url('login') ?>" method="post" class="px-6 py-6 sm:py-7 space-y-5" novalidate>
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="text-red-600 text-sm"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php elseif (session()->getFlashdata('message')): ?>
          <div class="text-green-600 text-sm"><?= esc(session()->getFlashdata('message')) ?></div>
        <?php endif; ?>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
          <input id="email" name="email" type="email" x-model="email"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                 placeholder="you@example.com" required autocomplete="username" inputmode="email" autocapitalize="none" enterkeyhint="next" />
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Kata Sandi</label>
          <div class="relative">
            <input :type="show ? 'text' : 'password'" id="password" name="password" x-model="password"
                   class="w-full pr-20 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                   placeholder="Minimal 8 karakter" required minlength="6" autocomplete="current-password" enterkeyhint="done" />
            <button type="button"
                    class="absolute inset-y-0 right-0 px-4 flex items-center text-sm text-gray-500 hover:text-gray-700"
                    @click="show = !show" x-text="show ? 'Sembunyikan' : 'Tampilkan'"></button>
          </div>
          <p class="mt-1 text-xs" :class="validPw ? 'text-green-600' : 'text-gray-500'"
             x-text="validPw ? 'Siap.' : 'Minimal 8 karakter.'"></p>
        </div>

        <!-- Ingat saya & Lupa password -->
        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2 text-sm text-gray-700 select-none">
            <input type="checkbox" name="remember" value="1" class="h-5 w-5 rounded border-gray-300 text-blue-600" />
            <span>Ingat saya</span>
          </label>
          <a href="<?= site_url('forgot-password') ?>" class="text-blue-600 font-semibold hover:underline text-sm">Lupa password?</a>
        </div>

        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="!validPw">
          Masuk
        </button>

        <p class="text-center text-sm text-gray-600">
          Belum punya akun?
          <a href="<?= site_url('register') ?>" class="text-blue-600 font-semibold hover:underline">Daftar</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>
