<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0">
    <div class="p-4 flex items-center justify-between">
      <div class="font-semibold text-gray-700">Announcements</div>
      <a href="<?= site_url('admin/announcements/create'); ?>" class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm">New</a>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100 text-xs uppercase text-gray-600">
          <tr><th class="px-4 py-2 text-left">Judul</th><th class="px-4 py-2 text-left">Audience</th><th class="px-4 py-2 text-left">Active</th><th class="px-4 py-2 text-right">Aksi</th></tr>
        </thead>
        <tbody class="text-sm">
          <?php foreach(($items ?? []) as $a): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= esc($a['title'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= esc($a['audience'] ?? 'all') ?></td>
              <td class="px-4 py-2"><?= !empty($a['is_active']) ? 'Yes' : 'No' ?></td>
              <td class="px-4 py-2 text-right">
                <a href="<?= site_url('admin/announcements/'.$a['id'].'/edit'); ?>" class="px-2 py-1 rounded bg-yellow-500 text-white text-xs">Edit</a>
                <form action="<?= site_url('admin/announcements/'.$a['id'].'/delete'); ?>" method="post" class="inline" onsubmit="return confirm('Hapus pengumuman?')">
                  <?= csrf_field() ?>
                  <button class="px-2 py-1 rounded bg-red-600 text-white text-xs">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
