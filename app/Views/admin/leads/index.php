<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }" x-data="{ openFilter:false }">
  <header class="bg-white shadow z-20 sticky top-0">
    <div class="p-4 flex items-center justify-between">
      <div class="font-semibold text-gray-700">Leads (Read-Only)</div>
      <div class="flex gap-2">
        <a href="<?= site_url('admin/leads/export/csv'); ?>" class="px-3 py-2 rounded bg-green-600 text-white text-sm"><i class="fa fa-file-csv mr-1"></i> CSV</a>
        <button class="px-3 py-2 rounded border text-sm" @click="openFilter=!openFilter"><i class="fa fa-filter mr-1"></i> Filter</button>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <!-- Filter form (GET) -->
    <form method="get" class="bg-white rounded-lg shadow p-4 mb-4" x-show="openFilter" x-cloak>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <input name="q" value="<?= esc(service('request')->getGet('q')) ?>" class="border rounded px-3 py-2" placeholder="Cari nama/telepon">
        <select name="status" class="border rounded px-3 py-2">
          <option value="">-- Status --</option>
          <?php foreach (['new','in_progress','closed','rejected'] as $st): ?>
            <option value="<?= $st ?>" <?= service('request')->getGet('status')===$st?'selected':''; ?>><?= $st ?></option>
          <?php endforeach; ?>
        </select>
        <input type="date" name="from" value="<?= esc(service('request')->getGet('from')) ?>" class="border rounded px-3 py-2">
        <input type="date" name="to"   value="<?= esc(service('request')->getGet('to'))   ?>" class="border rounded px-3 py-2">
      </div>
      <div class="mt-3">
        <button class="px-4 py-2 rounded bg-blue-600 text-white text-sm">Terapkan</button>
        <a href="<?= site_url('admin/leads'); ?>" class="px-4 py-2 rounded border text-sm ml-2">Reset</a>
      </div>
    </form>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100 text-xs uppercase text-gray-600">
          <tr>
            <th class="px-4 py-2 text-left">Tanggal</th>
            <th class="px-4 py-2 text-left">Pelanggan</th>
            <th class="px-4 py-2 text-left">Vendor</th>
            <th class="px-4 py-2 text-left">Service</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Source</th>
            <th class="px-4 py-2 text-right">Detail</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <?php foreach (($leads ?? []) as $l): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= esc(date('Y-m-d H:i', strtotime($l['created_at'] ?? 'now'))); ?></td>
              <td class="px-4 py-2"><?= esc($l['customer_name'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= esc($l['vendor_name'] ?? ($l['vendor_id'] ?? '-')) ?></td>
              <td class="px-4 py-2"><?= esc($l['service_name'] ?? ($l['service_id'] ?? '-')) ?></td>
              <td class="px-4 py-2"><?= esc($l['status'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= esc($l['source'] ?? '-') ?></td>
              <td class="px-4 py-2 text-right">
                <a href="<?= site_url('admin/leads/'.$l['id']); ?>" class="px-2 py-1 rounded bg-blue-600 text-white text-xs">Lihat</a>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($leads)): ?>
            <tr><td colspan="7" class="px-4 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
