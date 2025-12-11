<!DOCTYPE html>
<html lang="id" x-data>
<head>
  <meta charset="UTF-8" />
  <title>Lupa Password | Vendor Partnership SEO Performance</title>
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet" />
  <style>
    html,body{height:100%} body{font-family:'Montserrat',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial}
  </style>
</head>
<body class="bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image:url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card ringkas & mobile-first -->
    <div class="relative z-10 w-[92%] max-w-sm bg-white rounded-2xl shadow-2xl overflow-hidden"
         x-data="{
           email: '<?= esc(old('email') ?? '') ?>',
           touched: false,
           get valid(){
             return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email);
           }
         }">

      <!-- Header -->
      <div class="px-6 pt-8 pb-5 text-center border-b">
        <div class="mx-auto w-12 h-12 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-7 h-7">
        </div>
        <h1 class="mt-3 text-xl sm:text-2xl font-bold text-gray-800">Lupa Password</h1>
        <p class="text-gray-500 text-xs sm:text-sm mt-1">Vendor Partnership & SEO Performance</p>
      </div>

      <!-- Form -->
      <form action="<?= site_url('forgot-password') ?>" method="post" class="px-6 py-6 sm:py-7 space-y-5" novalidate>
        <?= csrf_field() ?>

        <!-- Flash Message -->
        <?php if(session()->getFlashdata('error')): ?>
          <div class="p-3 rounded-lg bg-red-100 text-red-700 text-sm">
            <?= esc(session()->getFlashdata('error')) ?>
          </div>
        <?php elseif(session()->getFlashdata('success')): ?>
          <div class="p-3 rounded-lg bg-green-100 text-green-700 text-sm">
            <?= esc(session()->getFlashdata('success')) ?>
          </div>
        <?php endif; ?>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
          <input id="email" name="email" type="email" x-model="email" @blur="touched=true"
                 :class="valid || !touched ? 'border-gray-300' : 'border-red-400 focus:border-red-500 focus:ring-red-200'"
                 class="w-full px-4 py-3 rounded-lg border focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none text-[15px]"
                 placeholder="you@example.com" required autocomplete="username" inputmode="email" autocapitalize="none" />
          <p class="mt-1 text-xs text-red-600" x-show="touched && !valid">Format email tidak valid.</p>
        </div>

        <!-- Submit -->
        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow disabled:opacity-60 disabled:cursor-not-allowed"
                :disabled="!valid">
          Kirim Link Reset
        </button>

        <!-- Back -->
        <div class="text-center mt-1 text-sm text-gray-600">
          <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">
            Kembali ke Login
          </a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
