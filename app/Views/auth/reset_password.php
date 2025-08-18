<form action="<?= site_url('reset-password/' . esc($token)) ?>" method="post" class="px-8 py-8 space-y-6 bg-white rounded-2xl shadow-lg max-w-md mx-auto mt-16">
    <?= csrf_field() ?>

    <h2 class="text-xl font-bold mb-4 text-gray-800 text-center">Reset Password</h2>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="text-red-600 mb-2"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Password Baru</label>
        <input type="password" name="password" placeholder="Password baru" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none" required>
    </div>

    <div>
        <label class="block text-sm font-semibold text-gray-700 mb-2">Konfirmasi Password</label>
        <input type="password" name="password_confirm" placeholder="Konfirmasi password" class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:border-blue-600 focus:ring-2 focus:ring-blue-200 outline-none" required>
    </div>

    <button type="submit" class="w-full py-3 rounded-lg bg-blue-600 hover:bg-blue-700 transition font-semibold text-white shadow">Reset Password</button>

    <div class="text-center mt-2 text-sm text-gray-600">
        <a href="<?= site_url('login') ?>" class="text-blue-600 font-semibold hover:underline">Kembali ke Login</a>
    </div>
</form>
