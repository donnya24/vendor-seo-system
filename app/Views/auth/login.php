<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk | Vendor SEO System</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-['Montserrat'] bg-gray-50">

  <!-- Wrapper -->
  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image: url('/assets/img/1.jpg');">
    
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card -->
    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
      
      <!-- Header -->
      <div class="px-8 pt-10 pb-6 text-center border-b">
        <div class="mx-auto w-14 h-14 rounded-full bg-blue-100 flex items-center justify-center shadow">
          <img src="/assets/img/logo.png" alt="Logo" class="w-8 h-8">
        </div>
        <h1 class="mt-4 text-2xl font-bold text-gray-800">Masuk ke Sistem</h1>
        <p class="text-gray-500 text-sm mt-1">Vendor SEO System</p>
      </div>

      <!-- Form (Shield default) -->
      <form action="<?= site_url('Auth/Login') ?>" method="post" class="px-8 py-8 space-y-6">
        <?= csrf_field() ?>

        <!-- Email/Username -->
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
          <input type="text" name="email" value="<?= old('email') ?>"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="you@example.com" required>
        </div>

        <!-- Password -->
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

        <!-- Tombol Login -->
        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow">
          Masuk
        </button>

        <!-- Link ke Register -->
        <p class="text-center text-sm text-gray-600">
          Belum punya akun?
          <a href="<?= site_url('Auth/Register') ?>" class="text-blue-600 font-semibold hover:underline">Daftar</a>
        </p>
      </form>
    </div>
  </div>

  <!-- Toggle Password JS -->
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
