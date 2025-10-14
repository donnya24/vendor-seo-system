<?= $this->include('admin/layouts/header') ?>
<?= $this->include('admin/layouts/sidebar') ?>

<div class="p-6 space-y-6" x-data="seoReports()" x-init="init()">

  <head>
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
  </head>

  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
          <h2 class="text-2xl font-bold text-gray-900">SEO Reports - Admin</h2>
          <p class="mt-1 text-sm text-gray-600">Laporan performa keyword yang sudah completed (Akses Admin)</p>
      </div>
      <div>
          <a href="<?= site_url('admin/reports/export-csv') ?>?<?= $_SERVER['QUERY_STRING'] ?? '' ?>" 
            class="inline-flex items-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
              <i class="fas fa-file-csv mr-2"></i> Export CSV
          </a>
      </div>
  </div>

  <!-- Filter Section -->
  <form method="get" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
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

      <!-- Position Filter -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          <i class="fas fa-chart-line text-blue-600 mr-1"></i> Filter Posisi
        </label>
        <select name="position"
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
          <option value="">Semua Posisi</option>
          <option value="top3" <?= ($position == 'top3') ? 'selected' : '' ?>>🥇 Top 3</option>
          <option value="top10" <?= ($position == 'top10') ? 'selected' : '' ?>>🏆 Top 10</option>
          <option value="top20" <?= ($position == 'top20') ? 'selected' : '' ?>>📈 Top 20</option>
          <option value="below20" <?= ($position == 'below20') ? 'selected' : '' ?>>📊 Dibawah 20</option>
        </select>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2">
        <button type="submit" 
                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center shadow-sm">
          <i class="fas fa-search mr-2"></i> Terapkan Filter
        </button>
        <a href="<?= site_url('admin/reports') ?>" 
           class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium flex items-center shadow-sm">
          <i class="fas fa-refresh mr-2"></i> Reset
        </a>
      </div>
    </div>

    <!-- Active Filter Info -->
    <?php if(!empty($vendorId) || !empty($position)): ?>
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
            <?php if(!empty($position)): ?>
              <span class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
                Posisi: 
                <?= $position == 'top3' ? 'Top 3' : '' ?>
                <?= $position == 'top10' ? 'Top 10' : '' ?>
                <?= $position == 'top20' ? 'Top 20' : '' ?>
                <?= $position == 'below20' ? 'Dibawah 20' : '' ?>
              </span>
            <?php endif; ?>
          </span>
        </div>
        <span class="text-xs text-blue-600 bg-white px-2 py-1 rounded">
          <?= count($reports) ?> laporan ditemukan
        </span>
      </div>
    </div>
    <?php endif; ?>
  </form>

  <!-- Stats Cards -->
  <?php
    $totalReports = count($reports);
    $top3Count = count(array_filter($reports, function($r) {
        return ($r['current_position'] ?? 0) <= 3;
    }));
    $top10Count = count(array_filter($reports, function($r) {
        $pos = $r['current_position'] ?? 0;
        return $pos > 3 && $pos <= 10;
    }));
    $improvedCount = count(array_filter($reports, function($r) {
        return ($r['change'] ?? 0) > 0;
    }));
  ?>
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
          <i class="fas fa-file-alt text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Total Laporan</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1"><?= $totalReports ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-green-100 text-green-600">
          <i class="fas fa-trophy text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Top 3</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1"><?= $top3Count ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-yellow-100 text-yellow-600">
          <i class="fas fa-medal text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Top 10</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1"><?= $top10Count ?></p>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-purple-100 text-purple-600">
          <i class="fas fa-chart-line text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Meningkat</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1"><?= $improvedCount ?></p>
        </div>
      </div>
    </div>
  </div>

  <!-- Table Container -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <!-- Table Header -->
    <div class="px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <h3 class="text-lg font-medium text-gray-900">Daftar Laporan Completed</h3>
      <div class="text-sm text-gray-500">
        <i class="fas fa-database mr-1"></i>
        <span><?= count($reports) ?> laporan ditemukan</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 text-sm">
        <!-- Table Head -->
        <thead class="bg-blue-600 text-white text-xs uppercase sticky top-0 z-10">
          <tr>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">No</th>
            <th class="px-4 py-3 text-left font-semibold tracking-wider">Vendor</th>
            <th class="px-4 py-3 text-left font-semibold tracking-wider">Project</th>
            <th class="px-4 py-3 text-left font-semibold tracking-wider">Keyword</th>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">Target</th>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">Posisi</th>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">Perubahan</th>
            <th class="px-4 py-3 text-center font-semibold tracking-wider">Selesai</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($reports)): $no=1; foreach($reports as $r): ?>
            <tr class="hover:bg-gray-50 transition-colors duration-150">
              <td class="px-4 py-3 text-center text-gray-600"><?= $no++ ?></td>
              <td class="px-4 py-3">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-sm"></i>
                  </div>
                  <div class="ml-3">
                    <div class="font-medium text-gray-900"><?= esc($r['vendor_name']) ?></div>
                    <div class="text-xs text-gray-500">ID: <?= $r['vendor_id'] ?></div>
                  </div>
                </div>
              </td>
              <td class="px-4 py-3 text-gray-700"><?= esc($r['project_name']) ?></td>
              <td class="px-4 py-3 text-gray-700 truncate max-w-[200px]" title="<?= esc($r['keyword']) ?>">
                <?= esc($r['keyword']) ?>
              </td>
              <td class="px-4 py-3 text-center">
                <span class="inline-flex items-center justify-center px-2 py-1 rounded-full bg-purple-100 text-purple-700 text-xs font-medium">
                  <?= $r['target_position'] ?: '-' ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <?php 
                  $currentPos = $r['current_position'] ?? 0;
                  $positionClass = match(true) {
                    $currentPos <= 3 => 'bg-green-100 text-green-700',
                    $currentPos <= 10 => 'bg-blue-100 text-blue-700',
                    $currentPos <= 20 => 'bg-yellow-100 text-yellow-700',
                    default => 'bg-gray-100 text-gray-700'
                  };
                ?>
                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full font-medium <?= $positionClass ?>">
                  <?= $currentPos ?: '-' ?>
                </span>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if ($r['change'] !== null): ?>
                  <?php if ($r['change'] > 0): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                      +<?= $r['change'] ?> <i class="fas fa-arrow-up ml-1"></i>
                    </span>
                  <?php elseif ($r['change'] < 0): ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                      <?= $r['change'] ?> <i class="fas fa-arrow-down ml-1"></i>
                    </span>
                  <?php else: ?>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                      0 <i class="fas fa-minus ml-1"></i>
                    </span>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-gray-400">—</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-center text-gray-600 text-sm">
                <?= date('d M Y', strtotime($r['updated_at'] ?? $r['created_at'])) ?>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr>
              <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                  <p class="text-lg font-medium text-gray-900">Tidak ada laporan</p>
                  <p class="mt-1 text-sm text-gray-500">
                    <?php if(!empty($vendorId) || !empty($position)): ?>
                      Tidak ada laporan dengan filter yang dipilih
                    <?php else: ?>
                      Belum ada laporan SEO yang completed
                    <?php endif; ?>
                  </p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function seoReports() {
    return {
        init() {
            // Initialization code if needed
        }
    }
}
</script>

<?= $this->include('admin/layouts/footer') ?>