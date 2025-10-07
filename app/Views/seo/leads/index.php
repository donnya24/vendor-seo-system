<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-8">
  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fas fa-database text-blue-600"></i> Pantau Leads
      </h1>
      <p class="mt-1 text-sm text-gray-600">Monitor performa leads dari semua vendor</p>
    </div>
  </div>

  <!-- Filter Section -->
  <form method="get" class="bg-white rounded-xl shadow border border-gray-200 p-6">
    <div class="flex flex-col lg:flex-row lg:items-end gap-4">
      <!-- Vendor Filter -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          <i class="fas fa-filter text-blue-600 mr-1"></i> Filter Vendor
        </label>
        <select name="vendor_id" 
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
          <option value="">📋 Semua Vendor</option>
          <?php foreach($vendors as $vendor): ?>
            <option value="<?= $vendor['id'] ?>" 
                    <?= ($vendorId == $vendor['id']) ? 'selected' : '' ?>>
              🏢 <?= esc($vendor['business_name']) ?> (ID: <?= $vendor['id'] ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Date Filter -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          <i class="fas fa-calendar text-blue-600 mr-1"></i> Dari Tanggal
        </label>
        <input type="date" name="start" value="<?= esc($start) ?>" 
               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          <i class="fas fa-calendar-check text-blue-600 mr-1"></i> Sampai Tanggal
        </label>
        <input type="date" name="end" value="<?= esc($end) ?>" 
               class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2">
        <button type="submit" 
                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center shadow-sm">
          <i class="fas fa-search mr-2"></i> Terapkan Filter
        </button>
        <a href="<?= site_url('seo/leads') ?>" 
           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium flex items-center shadow-sm">
          <i class="fas fa-refresh mr-2"></i> Reset
        </a>
      </div>
    </div>

    <!-- Active Filter Info -->
    <?php if(!empty($vendorId) || !empty($start) || !empty($end)): ?>
    <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <i class="fas fa-info-circle text-blue-600 mr-2"></i>
          <span class="text-sm text-blue-700">
            Filter aktif: 
            <?php if(!empty($vendorId)): ?>
              <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                Vendor: <?= $vendorId ?>
              </span>
            <?php endif; ?>
            <?php if(!empty($start) && !empty($end)): ?>
              <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                Periode: <?= date('d M Y', strtotime($start)) ?> - <?= date('d M Y', strtotime($end)) ?>
              </span>
            <?php elseif(!empty($start)): ?>
              <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                Mulai: <?= date('d M Y', strtotime($start)) ?>
              </span>
            <?php elseif(!empty($end)): ?>
              <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                Sampai: <?= date('d M Y', strtotime($end)) ?>
              </span>
            <?php endif; ?>
          </span>
        </div>
        <span class="text-xs text-blue-600 bg-white px-2 py-1 rounded">
          <?= count($leads) ?> data ditemukan
        </span>
      </div>
    </div>
    <?php endif; ?>
  </form>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
          <i class="fas fa-sign-in-alt text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Total Leads Masuk</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?= number_format($summary['total_masuk'] ?? 0, 0, ',', '.') ?>
          </p>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-green-100 text-green-600">
          <i class="fas fa-check-circle text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Total Leads Closing</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?= number_format($summary['total_closing'] ?? 0, 0, ',', '.') ?>
          </p>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
          <i class="fas fa-chart-line text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Conversion Rate</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?= number_format($summary['conversion_rate'] ?? 0, 1) ?>%
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabel -->
  <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <h3 class="text-lg font-semibold text-gray-800">Daftar Leads Vendor</h3>
      <div class="text-sm text-gray-500">
        <i class="fas fa-database mr-1"></i>
        <span><?= !empty($leads) ? count($leads) : 0 ?> record ditemukan</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-blue-600 text-white text-xs uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-center">No</th>
            <th class="px-4 py-3 text-left">Vendor</th>
            <th class="px-4 py-3 text-center">Periode Laporan</th>
            <th class="px-4 py-3 text-center">Leads Masuk</th>
            <th class="px-4 py-3 text-center">Leads Closing</th>
            <th class="px-4 py-3 text-center">Conversion Rate</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 bg-white">
          <?php if (!empty($leads)): ?>
            <?php $i = 1; foreach ($leads as $l): ?>
              <?php
                $leadsMasuk = $l['jumlah_leads_masuk'] ?? 0;
                $leadsClosing = $l['jumlah_leads_closing'] ?? 0;
                $rate = $leadsMasuk > 0 ? ($leadsClosing / $leadsMasuk) * 100 : 0;
              ?>
            <tr class="hover:bg-blue-50 transition-colors">
              <td class="px-4 py-3 text-center text-gray-600"><?= $i++ ?></td>
              <td class="px-4 py-3">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-sm"></i>
                  </div>
                  <div class="ml-3">
                    <div class="font-medium text-gray-900"><?= esc($l['vendor_name'] ?? '-') ?></div>
                    <div class="text-xs text-gray-500">ID: <?= $l['vendor_id'] ?></div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-center text-gray-700">
                <?= date('d M Y', strtotime($l['tanggal_mulai'])) ?> 
                <span class="text-gray-400">s/d</span><br>
                <?= date('d M Y', strtotime($l['tanggal_selesai'])) ?>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="font-semibold text-blue-600 text-lg">
                  <?= number_format($leadsMasuk, 0, ',', '.') ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="font-semibold text-green-600 text-lg">
                  <?= number_format($leadsClosing, 0, ',', '.') ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="font-semibold <?= $rate >= 10 ? 'text-green-600' : ($rate >= 5 ? 'text-yellow-600' : 'text-red-600') ?>">
                  <?= number_format($rate, 1) ?>%
                </span>
              </td>
            </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="px-4 py-10 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                  <p class="text-lg font-medium text-gray-900">Tidak ada data leads</p>
                  <p class="mt-1 text-sm text-gray-500">
                    <?php if(!empty($vendorId) || !empty($start) || !empty($end)): ?>
                      Tidak ada data leads dengan filter yang dipilih
                    <?php else: ?>
                      Belum ada data leads yang tersedia
                    <?php endif; ?>
                  </p>
                </div>
              </td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
    <div class="px-5 py-4 border-t border-gray-100 bg-gray-50">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
          Menampilkan halaman <?= $pager->getCurrentPage() ?> dari <?= $pager->getPageCount() ?>
        </div>
        <div class="flex space-x-1">
          <?= $pager->links() ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>