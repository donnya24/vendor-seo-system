<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password | Vendor SEO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="font-['Montserrat'] bg-gray-50">

<div class="min-h-screen flex items-center justify-center bg-cover bg-center relative"
     style="background-image: url('/assets/img/logo/background.png');">
  <div class="absolute inset-0 bg-black/60"></div>

  <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
    <!-- Header -->
    <div class="px-8 pt-10 pb-6 text-center border-b">
      <div class="mx-auto w-14 h-14 rounded-full bg-white flex items-center justify-center shadow">
        <img src="/assets/img/logo/icon.png" alt="Logo" class="w-8 h-8">
      </div>
      <h1 class="mt-4 text-2xl font-bold text-gray-800">Lupa Password</h1>
      <p class="text-gray-500 text-sm mt-1">Vendor Partnership & SEO Performance</p>
    </div>

    <!-- Form -->
    <form action="<?= site_url('forgot-password') ?>" method="post" class="px-8 py-8 space-y-6">
        <?= csrf_field() ?>

        <!-- Flash Message -->
        <?php if(session()->getFlashdata('error')): ?>
            <div class="p-3 rounded-lg bg-red-100 text-red-700 text-sm">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php elseif(session()->getFlashdata('success')): ?>
            <div class="p-3 rounded-lg bg-green-100 text-green-700 text-sm">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <!-- Input -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
            <input type="email" id="email" name="email" value="<?= old('email') ?>"
                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none"
                   placeholder="Masukkan email Anda" required>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow">
            Kirim Link Reset
        </button>

        <!-- Back -->
        <div class="text-center mt-2 text-sm text-gray-600">
            <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">
                Kembali ke Login
            </a>
        </div>
    </form>
  </div>
</div>

</body>
</html>
