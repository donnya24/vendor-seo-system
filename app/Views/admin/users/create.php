<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }" x-data="{ showPass:false }">
  <header class="bg-white shadow z-20 sticky top-0">
    <div class="flex items-center justify-between p-4">
      <h2 class="font-semibold text-gray-700">Create User</h2>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <form action="<?= site_url('admin/users/store'); ?>" method="post" class="bg-white rounded-lg shadow p-4 space-y-4">
      <?= csrf_field() ?>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
        <input name="username" required class="w-full border rounded px-3 py-2" placeholder="username">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
        <input type="email" name="email" required class="w-full border rounded px-3 py-2" placeholder="you@example.com">
      </div>

      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
        <select name="role" class="w-full border rounded px-3 py-2">
          <option value="admin">admin</option>
          <option value="seoteam">seoteam</option>
          <option value="vendor">vendor</option>
        </select>
      </div>

      <div class="relative">
        <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
        <input :type="showPass ? 'text' : 'password'" name="password" required class="w-full border rounded px-3 py-2" placeholder="min 8 karakter">
        <button type="button" @click="showPass=!showPass"
                class="absolute right-2 top-8 text-sm text-gray-500 hover:text-gray-700">
          <span x-show="!showPass">Tampilkan</span>
          <span x-show="showPass">Sembunyikan</span>
        </button>
      </div>

      <div class="pt-2">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
        <a href="<?= site_url('admin/users'); ?>" class="ml-2 px-4 py-2 rounded border">Batal</a>
      </div>
    </form>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
