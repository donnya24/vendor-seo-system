<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Register | Vendor Partnership & SEO Performance</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Montserrat -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-['Montserrat'] bg-gray-50">

  <!-- Wrapper -->
  <div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
       style="background-image: url('/assets/img/logo/background.png');">
    
    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/60"></div>

    <!-- Card -->
    <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
      
      <!-- Header -->
      <div class="px-8 pt-10 pb-6 text-center border-b">
        <div class="mx-auto w-14 h-14 rounded-full bg-white-100 flex items-center justify-center shadow">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="w-8 h-8">
        </div>
        <h1 class="mt-4 text-2xl font-bold text-gray-800">Buat Akun Vendor</h1>
        <p class="text-gray-500 text-sm mt-1">Kelola profil, leads & laporan dalam satu sistem</p>
      </div>

      <!-- Form -->
      <form action="<?= site_url('auth/store') ?>" method="post" class="px-8 py-8 space-y-6">
        <?= csrf_field() ?>

        <!-- Nama Vendor -->
        <div>
          <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Vendor</label>
          <input type="text" name="name" id="name" value="<?= old('name') ?>"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="Nama perusahaan / brand" required>
        </div>

        <!-- Email -->
        <div>
          <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
          <input type="email" name="email" id="email" value="<?= old('email') ?>"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="you@example.com" required>
        </div>

        <!-- Password -->
        <div>
          <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
          <input type="password" name="password" id="password"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="••••••••" required>
        </div>

        <!-- Confirm Password -->
        <div>
          <label for="password_confirm" class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
          <input type="password" name="password_confirm" id="password_confirm"
                 class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                 placeholder="••••••••" required>
        </div>

        <!-- Tombol Register -->
        <button type="submit"
                class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow">
          Daftar
        </button>

        <!-- Link ke Login -->
        <p class="text-center text-sm text-gray-600">
          Sudah punya akun?
          <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">Masuk</a>
        </p>
      </form>
    </div>
  </div>

</body>
</html>