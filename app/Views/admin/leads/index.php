<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }" x-data="{ openFilter: false }">
  <!-- Header Section -->
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="px-6 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-800">Leads Management</h1>
        <p class="text-sm text-gray-500 mt-1">Read-only access to all lead information</p>
      </div>
      <div class="flex gap-3">
        <a href="<?= site_url('admin/leads/export/csv'); ?>" class="px-4 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium flex items-center transition-colors duration-200">
          <i class="fa fa-file-csv mr-2"></i> Export CSV
        </a>
        <button class="px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-sm font-medium flex items-center transition-colors duration-200" 
                @click="openFilter = !openFilter">
          <i class="fa fa-filter mr-2"></i> 
          <span x-text="openFilter ? 'Tutup Filter' : 'Filter'"></span>
        </button>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
    <!-- Filter Form -->
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 transition-all duration-300 ease-in-out" 
         x-show="openFilter" x-transition x-cloak>
      <h2 class="text-lg font-semibold text-gray-800 mb-4">Filter Leads</h2>
      <form method="get" class="space-y-4 md:space-y-0">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian</label>
            <input name="q" value="<?= esc(service('request')->getGet('q')) ?>" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors duration-200" 
                   placeholder="Cari nama/telepon">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors duration-200">
              <option value="">Semua Status</option>
              <?php foreach (['new' => 'Baru', 'in_progress' => 'Dalam Proses', 'closed' => 'Tertutup', 'rejected' => 'Ditolak'] as $value => $label): ?>
                <option value="<?= $value ?>" <?= service('request')->getGet('status') === $value ? 'selected' : ''; ?>>
                  <?= $label ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date" name="from" value="<?= esc(service('request')->getGet('from')) ?>" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors duration-200">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date" name="to" value="<?= esc(service('request')->getGet('to')) ?>" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-colors duration-200">
          </div>
        </div>
        <div class="flex gap-3 pt-2">
          <button class="px-5 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium flex items-center transition-colors duration-200">
            <i class="fa fa-check-circle mr-2"></i> Terapkan Filter
          </button>
          <a href="<?= site_url('admin/leads'); ?>" class="px-5 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium flex items-center transition-colors duration-200">
            <i class="fa fa-refresh mr-2"></i> Reset
          </a>
        </div>
      </form>
    </div>

    <!-- Leads Table with Animation -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-slide-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Pelanggan</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Vendor</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Service</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Source</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach (($leads ?? []) as $l): ?>
              <tr class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900"><?= esc(date('d M Y', strtotime($l['created_at'] ?? 'now'))); ?></div>
                  <div class="text-xs text-gray-500"><?= esc(date('H:i', strtotime($l['created_at'] ?? 'now'))); ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900"><?= esc($l['customer_name'] ?? '-') ?></div>
                  <?php if (!empty($l['customer_phone'])): ?>
                    <div class="text-xs text-gray-500"><?= esc($l['customer_phone']) ?></div>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900"><?= esc($l['vendor_name'] ?? ($l['vendor_id'] ?? '-')) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900"><?= esc($l['service_name'] ?? ($l['service_id'] ?? '-')) ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <?php
                  $statusColors = [
                    'new' => 'bg-blue-100 text-blue-800',
                    'in_progress' => 'bg-yellow-100 text-yellow-800',
                    'closed' => 'bg-green-100 text-green-800',
                    'rejected' => 'bg-red-100 text-red-800'
                  ];
                  $statusTexts = [
                    'new' => 'Baru',
                    'in_progress' => 'Proses',
                    'closed' => 'Tertutup',
                    'rejected' => 'Ditolak'
                  ];
                  $status = $l['status'] ?? 'new';
                  $colorClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                  $statusText = $statusTexts[$status] ?? ucfirst($status);
                  ?>
                  <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $colorClass ?>">
                    <?= $statusText ?>
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900"><?= esc($l['source'] ?? '-') ?></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <a href="<?= site_url('admin/leads/'.$l['id']); ?>" 
                     class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <i class="fa fa-eye mr-1"></i> Lihat
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($leads)): ?>
              <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-400">
                    <i class="fa fa-inbox text-4xl mb-3"></i>
                    <p class="text-lg font-medium">Tidak ada data leads</p>
                    <p class="text-sm mt-1">Coba ubah filter atau tambah data baru</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Pagination (jika ada) -->
      <?php if (isset($pager) && $pager->getPageCount() > 1): ?>
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Menampilkan <span class="font-medium"><?= $pager->getCurrentPageFirstItem() ?></span> 
            sampai <span class="font-medium"><?= $pager->getCurrentPageLastItem() ?></span> 
            dari <span class="font-medium"><?= $pager->getTotalItems() ?></span> hasil
          </div>
          <div class="flex space-x-2">
            <?php if ($pager->hasPreviousPage()): ?>
              <a href="<?= $pager->getPreviousPageURI() ?>" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Sebelumnya
              </a>
            <?php endif; ?>
            
            <?php if ($pager->hasNextPage()): ?>
              <a href="<?= $pager->getNextPageURI() ?>" class="px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Selanjutnya
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<style>
  /* Animasi untuk slide up dari bawah */
  .animate-slide-up {
    animation: slideUp 0.5s ease-out forwards;
    opacity: 0;
    transform: translateY(20px);
  }
  
  @keyframes slideUp {
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>

<?= $this->include('admin/layouts/footer'); ?>