<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0">
    <div class="flex items-center justify-between p-4">
      <h2 class="font-semibold text-gray-700">Vendor Detail</h2>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow p-4 space-y-2">
      <p><b>ID:</b> <?= esc($vendor['id']) ?></p>
      <p><b>Status:</b> <?= esc($vendor['status'] ?? '-') ?></p>
      <p><b>Verified:</b> <?= !empty($vendor['is_verified']) ? 'Yes' : 'No' ?></p>
      <p><b>Commission Rate:</b> <?= esc($vendor['commission_rate'] ?? 0) ?>%</p>

      <div class="mt-4 flex gap-2">
        <form action="<?= site_url('admin/vendors/'.$vendor['id'].'/verify'); ?>" method="post">
          <?= csrf_field() ?>
          <button class="px-3 py-2 rounded bg-green-600 text-white text-sm">Verify</button>
        </form>
        <form action="<?= site_url('admin/vendors/'.$vendor['id'].'/unverify'); ?>" method="post">
          <?= csrf_field() ?>
          <button class="px-3 py-2 rounded bg-yellow-600 text-white text-sm">Unverify</button>
        </form>
        <form action="<?= site_url('admin/vendors/'.$vendor['id'].'/commission'); ?>" method="post" class="flex items-center gap-2">
          <?= csrf_field() ?>
          <input name="commission_rate" type="number" step="0.01" min="0" class="border rounded px-2 py-1 w-28" placeholder="Rate %" required>
          <button class="px-3 py-2 rounded bg-blue-600 text-white text-sm">Set Rate</button>
        </form>
      </div>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
