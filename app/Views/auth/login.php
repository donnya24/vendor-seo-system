<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login | Vendor SEO</title>
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
      <h1 class="mt-4 text-2xl font-bold text-gray-800">Masuk ke Sistem</h1>
      <p class="text-gray-500 text-sm mt-1">Vendor Partnership & SEO Performance</p>
    </div>

    <form action="<?= site_url('auth/attemptLogin') ?>" method="post" class="px-8 py-8 space-y-6">
      <?= csrf_field() ?>

      <?php if(session()->getFlashdata('error')): ?>
          <div class="text-red-600 text-sm mb-4"><?= session()->getFlashdata('error') ?></div>
      <?php elseif(session()->getFlashdata('success')): ?>
          <div class="text-green-600 text-sm mb-4"><?= session()->getFlashdata('success') ?></div>
      <?php endif; ?>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
        <input type="email" name="email" value="<?= old('email') ?>"
          class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
          placeholder="you@example.com" required>
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Kata Sandi</label>
        <div class="relative">
          <input type="password" id="password" name="password"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="••••••••" required>
          <button type="button" id="togglePassword"
                  class="absolute inset-y-0 right-3 flex items-center text-sm text-gray-500 hover:text-gray-700">
            Tampilkan
          </button>
        </div>
      </div>

      <div class="flex items-center justify-between">
        <label class="flex items-center space-x-2 text-sm text-gray-600">
          <input type="checkbox" name="remember" class="h-4 w-4 text-blue-600 rounded border-gray-300">
          <span>Ingat saya</span>
        </label>
        <a href="<?= site_url('forgot-password') ?>" class="text-blue-600 font-semibold hover:underline text-sm">Lupa password?</a>
      </div>

      <button type="submit" class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow">Masuk</button>

      <p class="text-center text-sm text-gray-600 mt-2">
        Belum punya akun?
        <a href="<?= site_url('register') ?>" class="text-blue-600 font-semibold hover:underline">Daftar</a>
      </p>
    </form>
  </div>
</div>

<script>
const togglePassword = document.getElementById('togglePassword');
const password = document.getElementById('password');
togglePassword.addEventListener('click', () => {
  const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
  password.setAttribute('type', type);
  togglePassword.textContent = type === 'password' ? 'Tampilkan' : 'Sembunyikan';
});
</script>
</body>
</html>
