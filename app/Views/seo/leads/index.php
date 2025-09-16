<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Pantau Leads</h2>
  </div>

  <!-- Filter periode -->
  <form method="get" class="flex flex-wrap gap-3 mb-4 bg-white p-4 rounded-xl shadow border border-blue-50">
    <input type="hidden" name="vendor_id" value="<?= esc($vendorId) ?>">
    <div class="flex items-center gap-2">
      <label class="text-gray-600 text-sm">Periode</label>
      <input type="date" name="start" value="<?= esc($start) ?>" 
             class="border rounded-lg px-3 py-2">
      <span class="text-gray-500">—</span>
      <input type="date" name="end" value="<?= esc($end) ?>" 
             class="border rounded-lg px-3 py-2">
    </div>
    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">
      <i class="fas fa-filter mr-2"></i> Filter
    </button>

    <a href="<?= site_url('seo/leads?vendor_id='.$vendorId) ?>" 
      class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
      <i class="fas fa-undo mr-2"></i> Reset
    </a>
  </form>

  <!-- Tabel -->
  <div class="bg-white p-4 rounded-xl shadow border border-blue-50 overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-blue-600 text-white">
        <tr>
          <th class="p-3 text-center">No</th>
          <th class="p-3 text-center">Tanggal</th>
          <th class="p-3 text-center">Leads Masuk</th>
          <th class="p-3 text-center">Leads Closing</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (!empty($leads)): ?>
          <?php $i = 1; foreach ($leads as $l): ?>
          <tr class="hover:bg-blue-50">
            <td class="p-3 text-center"><?= $i++ ?></td>
            <td class="p-3 text-center"><?= esc($l['tanggal']) ?></td>
            <td class="p-3 text-center"><?= esc($l['jumlah_leads_masuk'] ?? 0) ?></td>
            <td class="p-3 text-center"><?= esc($l['jumlah_leads_closing'] ?? 0) ?></td>
          </tr>
          <?php endforeach ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="p-3 text-center text-gray-500">Tidak ada data</td>
          </tr>
        <?php endif ?>
      </tbody>
    </table>

    <div class="mt-4">
      <?= $pager->links() ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
