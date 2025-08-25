<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0"><div class="p-4 font-semibold text-gray-700">Services (Read-Only)</div></header>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100 text-xs uppercase text-gray-600">
          <tr><th class="px-4 py-2 text-left">ID</th><th class="px-4 py-2 text-left">Name</th><th class="px-4 py-2 text-left">Description</th></tr>
        </thead>
        <tbody class="text-sm">
          <?php foreach(($items ?? []) as $s): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= esc($s['id']) ?></td>
              <td class="px-4 py-2"><?= esc($s['name']) ?></td>
              <td class="px-4 py-2"><?= esc($s['description'] ?? '-') ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <tr><td colspan="3" class="px-4 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
