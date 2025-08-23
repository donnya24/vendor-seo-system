<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }" x-data="{ showPass:false }">
  <header class="bg-white shadow z-20 sticky top-0">
    <div class="flex items-center justify-between p-4">
      <h2 class="font-semibold text-gray-700">Edit User #<?= esc($user['id']) ?></h2>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <form action="<?= site_url('admin/users/'.$user['id'].'/update'); ?>" method="post" class="bg-white rounded-lg shadow p-4 space-y-4">
      <?= csrf_field() ?>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
        <input name="username" required class="w-full border rounded px-3 py-2"
               value="<?= esc($user['username'] ?? '') ?>">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
        <select name="role" class="w-full border rounded px-3 py-2">
          <?php $current = $groups[0] ?? 'vendor'; ?>
          <option value="admin"   <?= $current==='admin'?'selected':''; ?>>admin</option>
          <option value="seoteam" <?= $current==='seoteam'?'selected':''; ?>>seoteam</option>
          <option value="vendor"  <?= $current==='vendor'?'selected':''; ?>>vendor</option>
        </select>
      </div>

      <div class="relative">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Reset Password (opsional)</label>
        <input :type="showPass ? 'text' : 'password'" name="password" class="w-full border rounded px-3 py-2" placeholder="biarkan kosong kalau tidak ganti">
        <button type="button" @click="showPass=!showPass"
                class="absolute right-2 top-8 text-sm text-gray-500 hover:text-gray-700">
          <span x-show="!showPass">Tampilkan</span>
          <span x-show="showPass">Sembunyikan</span>
        </button>
      </div>

      <div class="pt-2">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan Perubahan</button>
        <a href="<?= site_url('admin/users'); ?>" class="ml-2 px-4 py-2 rounded border">Batal</a>
      </div>
    </form>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
