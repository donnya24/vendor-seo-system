<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0"><div class="p-4 font-semibold text-gray-700">New Announcement</div></header>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <form action="<?= site_url('admin/announcements/store'); ?>" method="post" class="bg-white rounded-lg shadow p-4 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
        <input name="title" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Konten</label>
        <textarea name="content" class="w-full border rounded px-3 py-2" rows="5" required></textarea>
      </div>
      <div>
        <label class="block text-sm font-semibold text-gray-700 mb-1">Audience</label>
        <select name="audience" class="w-full border rounded px-3 py-2">
          <option value="all">Semua</option>
          <option value="admin">Admin</option>
          <option value="seoteam">Tim SEO</option>
          <option value="vendor">Vendor</option>
        </select>
      </div>
      <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="border rounded">
        <span class="text-sm text-gray-700">Aktif</span>
      </label>
      <div class="pt-2">
        <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan</button>
        <a href="<?= site_url('admin/announcements'); ?>" class="ml-2 px-4 py-2 rounded border">Batal</a>
      </div>
    </form>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
