<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-8">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
      <i class="fas fa-database text-blue-600"></i> Pantau Leads
    </h1>
  </div>

  <!-- Filter Periode -->
  <form method="get" 
        class="flex flex-col sm:flex-row sm:items-end flex-wrap gap-4 bg-white p-5 rounded-xl shadow border border-gray-200">
    <input type="hidden" name="vendor_id" value="<?= esc($vendorId) ?>">

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
      <input type="date" name="start" value="<?= esc($start) ?>" 
             class="w-full sm:w-48 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    </div>

    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
      <input type="date" name="end" value="<?= esc($end) ?>" 
             class="w-full sm:w-48 border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
    </div>

    <div class="flex gap-3">
      <button type="submit" 
              class="inline-flex items-center bg-blue-600 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
        <i class="fas fa-filter mr-2"></i> Filter
      </button>
      <a href="<?= site_url('seo/leads?vendor_id='.$vendorId) ?>" 
         class="inline-flex items-center bg-gray-100 text-gray-700 px-5 py-2.5 rounded-lg font-medium hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition">
        <i class="fas fa-undo mr-2"></i> Reset
      </a>
    </div>
  </form>

  <!-- Tabel -->
  <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-blue-600 text-white text-xs uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-center">No</th>
            <th class="px-4 py-3 text-left">Vendor</th>
            <th class="px-4 py-3 text-center">Periode Laporan</th>
            <th class="px-4 py-3 text-center">Leads Masuk</th>
            <th class="px-4 py-3 text-center">Leads Closing</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <?php if (!empty($leads)): ?>
            <?php $i = 1; foreach ($leads as $l): ?>
            <tr class="hover:bg-blue-50 transition-colors">
              <td class="px-4 py-3 text-center text-gray-600"><?= $i++ ?></td>
              <td class="px-4 py-3 font-medium text-gray-900"><?= esc($l['vendor_name'] ?? '-') ?></td>
              <td class="px-4 py-3 text-center text-gray-700">
                  <?= esc(date('d M Y', strtotime($l['tanggal_mulai']))) ?> s/d <?= esc(date('d M Y', strtotime($l['tanggal_selesai']))) ?>
              </td>
              <td class="px-4 py-3 text-center font-semibold text-blue-600"><?= number_format($l['jumlah_leads_masuk'] ?? 0, 0, ',', '.') ?></td>
              <td class="px-4 py-3 text-center font-semibold text-green-600"><?= number_format($l['jumlah_leads_closing'] ?? 0, 0, ',', '.') ?></td>
            </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                <p>Tidak ada data leads pada periode yang dipilih.</p>
                <p class="text-xs mt-2">Coba hapus filter untuk melihat semua data.</p>
              </td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
    <div class="px-5 py-4 border-t border-gray-100">
      <div class="flex justify-center">
        <?= $pager->links() ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>