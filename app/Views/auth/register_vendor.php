<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register | Vendor Partnership & SEO Performance</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-['Montserrat'] bg-gray-50">

  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image: url('/assets/img/logo/background.png');">
    <div class="absolute inset-0 bg-black/60"></div>

    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
      <div class="px-8 pt-10 pb-6 text-center border-b">
        <div class="mx-auto w-14 h-14 rounded-full bg-white flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-8 h-8">
        </div>
        <h1 class="mt-4 text-2xl font-bold text-gray-800">Buat Akun Vendor</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola profil, leads & laporan dalam satu sistem</p>
      </div>

      <form action="<?= site_url('register') ?>" method="post" class="px-8 py-8 space-y-6">
        <?= csrf_field() ?>

        <?php if (session()->getFlashdata('error')): ?>
          <div class="text-red-600 text-sm mb-2"><?= session()->getFlashdata('error') ?></div>
        <?php elseif (session()->getFlashdata('message')): ?>
          <div class="text-green-600 text-sm mb-2"><?= session()->getFlashdata('message') ?></div>
        <?php endif; ?>

        <!-- Nama Vendor -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Vendor</label>
          <input type="text" name="vendor_name" value="<?= old('vendor_name') ?>"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="Nama perusahaan / brand" required>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
          <input type="email" name="email" value="<?= old('email') ?>"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="you@example.com" required>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
          <div class="relative">
            <input id="password" type="password" name="password"
                   class="w-full pr-12 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                   placeholder="Minimal 8 karakter" required>
            <button type="button" id="btnTogglePassword"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                    aria-label="Tampilkan/Sembunyikan password">
              <!-- Eye (show) -->
              <svg class="icon-show h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <!-- Eye-off (hide) -->
              <svg class="icon-hide h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.263-3.739M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.056 10.056 0 01-4.138 5.091M3 3l18 18" />
              </svg>
            </button>
          </div>
        </div>

        <!-- Konfirmasi Password -->
        <div>
          <label for="pass_confirm" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
          <div class="relative">
            <input id="pass_confirm" type="password" name="pass_confirm"
                   class="w-full pr-12 pl-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                   placeholder="Ulangi password" required>
            <button type="button" id="btnTogglePassConfirm"
                    class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700"
                    aria-label="Tampilkan/Sembunyikan konfirmasi password">
              <!-- Eye (show) -->
              <svg class="icon-show h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <!-- Eye-off (hide) -->
              <svg class="icon-hide h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.263-3.739M6.223 6.223A9.956 9.956 0 0112 5c4.477 0 8.268 2.943 9.542 7a10.056 10.056 0 01-4.138 5.091M3 3l18 18" />
              </svg>
            </button>
          </div>
        </div>

        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow">
          Daftar
        </button>

        <p class="text-center text-sm text-gray-600">
          Sudah punya akun?
          <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">Masuk</a>
        </p>
      </form>
    </div>
  </div>

<script defer src="<?= base_url('assets/js/Auth/register.js') ?>"></script>
</body>
</html>
