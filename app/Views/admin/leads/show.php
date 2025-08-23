<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0"><div class="p-4 font-semibold text-gray-700">Lead Detail</div></header>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <div class="bg-white rounded-lg shadow p-4 space-y-2">
      <p><b>Waktu:</b> <?= esc($lead['created_at'] ?? '-') ?></p>
      <p><b>Pelanggan:</b> <?= esc($lead['customer_name'] ?? '-') ?></p>
      <p><b>Telepon:</b> <?= esc($lead['phone'] ?? '-') ?></p>
      <p><b>Vendor:</b> <?= esc($lead['vendor_name'] ?? ($lead['vendor_id'] ?? '-')) ?></p>
      <p><b>Service:</b> <?= esc($lead['service_name'] ?? ($lead['service_id'] ?? '-')) ?></p>
      <p><b>Status:</b> <?= esc($lead['status'] ?? '-') ?></p>
      <p><b>Source:</b> <?= esc($lead['source'] ?? '-') ?></p>
      <p><b>Ringkasan:</b><br><?= esc($lead['summary'] ?? '-') ?></p>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
