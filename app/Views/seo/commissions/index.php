<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-2" x-data="commissionManager()">
  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-200">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fas fa-wallet text-blue-600"></i> Komisi Vendor
      </h1>
      <p class="mt-1 text-sm text-gray-600">Kelola pembayaran komisi vendor Anda</p>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex flex-col lg:flex-row lg:items-end gap-4">
      <!-- Vendor Filter -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          <i class="fas fa-filter text-blue-600 mr-1"></i> Filter Vendor
        </label>
        <select x-model="selectedVendor" 
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
      
      <!-- Status Filter -->
      <div class="flex-1">
        <label class="block text-sm font-medium text-gray-700 mb-2">
          <i class="fas fa-tag text-blue-600 mr-1"></i> Filter Status
        </label>
        <select x-model="selectedStatus"
                class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white shadow-sm">
          <option value="">Semua Status</option>
          <option value="unpaid" <?= ($status == 'unpaid') ? 'selected' : '' ?>>🟡 Unpaid</option>
          <option value="paid" <?= ($status == 'paid') ? 'selected' : '' ?>>🟢 Paid</option>
        </select>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-2">
        <button @click="applyFilter()" 
                class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium flex items-center shadow-sm">
          <i class="fas fa-search mr-2"></i> Terapkan Filter
        </button>
        <button @click="resetFilter()" 
                class="bg-gray-500 text-white px-6 py-3 rounded-lg hover:bg-gray-600 transition-colors font-medium flex items-center shadow-sm">
          <i class="fas fa-refresh mr-2"></i> Reset
        </button>
      </div>
    </div>

    <!-- Active Filter Info -->
    <div x-show="hasActiveFilter()" class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <i class="fas fa-info-circle text-blue-600 mr-2"></i>
          <span class="text-sm text-blue-700">
            Filter aktif: 
            <span x-show="selectedVendor" class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
              Vendor: <span x-text="getVendorName(selectedVendor)"></span>
            </span>
            <span x-show="selectedStatus" class="font-medium bg-white px-2 py-1 rounded border border-blue-200 ml-1">
              Status: <span x-text="getStatusLabel(selectedStatus)"></span>
            </span>
          </span>
        </div>
        <span class="text-xs text-blue-600 bg-white px-2 py-1 rounded">
          <?= count($commissions) ?> data ditemukan
        </span>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-blue-100 text-blue-600">
          <i class="fas fa-file-invoice-dollar text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Total Komisi</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?php 
              $totalCommissions = array_sum(array_column($commissions, 'amount'));
              echo "Rp " . number_format($totalCommissions, 0, ',', '.');
            ?>
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
          <h3 class="text-sm font-medium text-gray-500">Sudah Dibayar</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?php 
              $paidCount = count(array_filter($commissions, function($c) {
                return strtolower($c['status'] ?? '') === 'paid';
              }));
              echo $paidCount;
            ?>
          </p>
        </div>
      </div>
    </div>
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
      <div class="flex items-center">
        <div class="p-3 rounded-lg bg-yellow-100 text-yellow-600">
          <i class="fas fa-clock text-xl"></i>
        </div>
        <div class="ml-4">
          <h3 class="text-sm font-medium text-gray-500">Belum Dibayar</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?php 
              $unpaidCount = count(array_filter($commissions, function($c) {
                return strtolower($c['status'] ?? '') === 'unpaid';
              }));
              echo $unpaidCount;
            ?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <h3 class="text-lg font-semibold text-gray-800">Daftar Komisi Vendor</h3>
      <div class="text-sm text-gray-500">
        <i class="fas fa-database mr-1"></i>
        <span><?= !empty($commissions) && is_array($commissions) ? count($commissions) : 0 ?> record ditemukan</span>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
            <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider">Periode</th>
            <th scope="col" class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama Vendor</th>
            <th scope="col" class="px-5 py-3 text-right text-xs font-medium uppercase tracking-wider">Jumlah Komisi</th>
            <th scope="col" class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider">Bukti Transfer</th>
            <th scope="col" class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
            <th scope="col" class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (!empty($commissions) && is_array($commissions)): ?>
            <?php $no = 1; foreach ($commissions as $c): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4 text-center text-gray-600"><?= $no++ ?></td>

                <!-- PERBAIKAN: Tampilan periode menjadi sejajar horizontal -->
                <td class="px-5 py-4">
                  <div class="text-sm font-medium text-gray-900 whitespace-nowrap">
                    <?= esc($c['period_start']) ?> s/d <?= esc($c['period_end']) ?>
                  </div>
                </td>

                <td class="px-5 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900"><?= esc($c['vendor_name'] ?? '—') ?></div>
                      <div class="text-xs text-gray-500">ID: <?= $c['vendor_id'] ?></div>
                    </div>
                  </div>
                </td>

                <td class="px-5 py-4 text-right font-semibold text-gray-900">
                  Rp <?= number_format($c['amount'] ?? 0, 0, ',', '.') ?>
                </td>

                <td class="px-5 py-4 text-center">
                  <?php if (!empty($c['proof'])): ?>
                    <?php 
                      $ext = strtolower(pathinfo($c['proof'], PATHINFO_EXTENSION));
                      $imgExt = ['jpg','jpeg','png','gif','webp'];
                    ?>
                    <?php if (in_array($ext, $imgExt)): ?>
                      <div class="flex justify-center">
                        <img src="<?= base_url('uploads/commissions/'.$c['proof']) ?>" 
                             alt="Bukti Transfer" class="h-12 w-12 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:shadow-md transition"
                             onclick="window.open('<?= base_url('uploads/commissions/'.$c['proof']) ?>', '_blank')">
                      </div>
                      <a href="<?= base_url('uploads/commissions/'.$c['proof']) ?>" 
                         target="_blank" 
                         class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                        <i class="fas fa-file-download mr-1"></i> Lihat File
                      </a>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>

                <td class="px-5 py-4 text-center">
                  <?php 
                    $status = strtolower($c['status'] ?? '-');
                    $badgeClass = 'bg-gray-100 text-gray-700';
                    $label = ucfirst($status);
                    $icon = '';
                    if ($status === 'paid') {
                      $badgeClass = 'bg-green-100 text-green-800';
                      $label = 'Paid';
                      $icon = '<i class="fas fa-check-circle mr-1"></i>';
                    } elseif ($status === 'unpaid') {
                      $badgeClass = 'bg-yellow-100 text-yellow-800';
                      $label = 'Unpaid';
                      $icon = '<i class="fas fa-clock mr-1"></i>';
                    }
                  ?>
                  <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full <?= $badgeClass ?>">
                    <?= $icon ?><?= $label ?>
                  </span>
                </td>

                <td class="px-5 py-4 text-center">
                  <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    <?php if ($status === 'unpaid'): ?>
                      <button @click="confirmAction(<?= $c['id'] ?>)"
                              class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md text-xs font-medium shadow-sm transition flex items-center justify-center">
                        <i class="fas fa-check mr-1"></i> Verify
                      </button>
                    <?php elseif ($status === 'paid'): ?>
                      <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-double mr-1"></i> Sudah Dibayar
                      </span>
                    <?php else: ?>
                      <span class="text-gray-400 text-sm">-</span>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                  <p class="text-lg font-medium text-gray-900">Tidak ada data komisi</p>
                  <p class="mt-1 text-sm text-gray-500">
                    <?php if(!empty($vendorId) || !empty($status)): ?>
                      Tidak ada komisi dengan filter yang dipilih
                    <?php else: ?>
                      Belum ada komisi vendor yang tersedia
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
    <?php if($pager->getPageCount() > 1): ?>
    <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
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

  <!-- Modal Konfirmasi -->
  <div x-show="showConfirmModal" x-cloak
       class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
       style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0">
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md mx-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4">
      <div class="p-6">
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100">
          <i class="fas fa-check text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2">
          Konfirmasi Verifikasi
        </h3>
        <p class="text-gray-600 text-center mb-6">
          Apakah Anda yakin ingin menyetujui komisi ini?
        </p>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="showConfirmModal = false"
                  class="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
            Batal
          </button>
          <button @click="executeAction()"
                  class="px-4 py-2.5 rounded-lg text-white font-medium bg-green-600 hover:bg-green-700 transition-colors">
            Verify
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Notification Toast -->
  <div x-show="notification.show" x-cloak
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 transform translate-y-2"
       x-transition:enter-end="opacity-100 transform translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 transform translate-y-0"
       x-transition:leave-end="opacity-0 transform translate-y-2"
       class="fixed bottom-4 right-4 z-[100] bg-white rounded-lg shadow-lg border-l-4 p-4 max-w-md"
       :class="{'border-green-500': notification.type === 'success', 'border-red-500': notification.type === 'error'}">
    <div class="flex items-start">
      <div class="flex-shrink-0">
        <i class="fas text-xl"
           :class="{'fa-check-circle text-green-500': notification.type === 'success', 'fa-exclamation-circle text-red-500': notification.type === 'error'}"></i>
      </div>
      <div class="ml-3">
        <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
        <p class="mt-1 text-sm text-gray-500" x-text="notification.message"></p>
      </div>
      <div class="ml-auto pl-3">
        <button @click="notification.show = false" class="text-gray-400 hover:text-gray-500 focus:outline-none transition-colors">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div x-show="loading" x-cloak
       class="fixed inset-0 z-[100] flex items-center justify-center bg-black bg-opacity-50 backdrop-blur-sm"
       style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;">
    
    <!-- Loading Content -->
    <div class="relative bg-white rounded-lg p-6 flex flex-col items-center shadow-xl">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
      <p class="text-gray-700 font-medium">Memproses...</p>
    </div>
  </div>
</div>

<style>
.fixed.inset-0 {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    margin: 0 !important;
    padding: 0 !important;
    z-index: 9999 !important;
}

.modal-backdrop-full {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.5) !important;
    backdrop-filter: blur(4px) !important;
    z-index: 9998 !important;
}

.loading-overlay-full {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    background: rgba(0, 0, 0, 0.7) !important;
    backdrop-filter: blur(8px) !important;
    z-index: 10000 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

body.modal-open {
    overflow: hidden !important;
    height: 100vh !important;
    position: fixed !important;
    width: 100% !important;
}

.commission-row-error {
    animation: pulseError 2s ease-in-out;
    border-left: 4px solid #ef4444 !important;
    background-color: #fef2f2 !important;
}

@keyframes pulseError {
    0%, 100% { 
        background-color: rgb(254 242 242);
    }
    50% { 
        background-color: rgb(254 202 202);
    }
}

[x-cloak] { 
    display: none !important; 
}

.modal-content-center {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 10000 !important;
}
</style>

<script>
function commissionManager() {
  return {
    selectedVendor: '<?= $vendorId ?? "" ?>',
    selectedStatus: '<?= $status ?? "" ?>',
    showConfirmModal: false,
    commissionId: null,
    loading: false,
    notification: {
      show: false,
      type: 'success',
      title: '',
      message: '',
      timeout: null
    },

    vendors: {
      <?php foreach($vendors as $vendor): ?>
        '<?= $vendor['id'] ?>': '<?= esc($vendor['business_name']) ?>',
      <?php endforeach; ?>
    },

    hasActiveFilter() {
      return this.selectedVendor || this.selectedStatus;
    },

    getVendorName(vendorId) {
      return this.vendors[vendorId] || 'Unknown Vendor';
    },

    getStatusLabel(status) {
      const statusLabels = {
        'paid': 'Paid',
        'unpaid': 'Unpaid'
      };
      return statusLabels[status] || status;
    },

    applyFilter() {
      const params = new URLSearchParams();
      if (this.selectedVendor) params.append('vendor_id', this.selectedVendor);
      if (this.selectedStatus) params.append('status', this.selectedStatus);
      
      const queryString = params.toString();
      const url = queryString ? `?${queryString}` : '';
      
      window.location.href = `<?= site_url('seo/commissions') ?>${url}`;
    },

    resetFilter() {
      window.location.href = '<?= site_url('seo/commissions') ?>';
    },

    confirmAction(id) {
      this.commissionId = id;
      this.showConfirmModal = true;
      document.body.classList.add('modal-open');
      document.body.style.overflow = 'hidden';
    },

    async executeAction() {
      this.showConfirmModal = false;
      this.loading = true;
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';

      const url = `<?= site_url('seo/commissions/approve') ?>/${this.commissionId}`;

      try {
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        const response = await fetch(url, {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          this.showNotification('success', 'Berhasil', 'Komisi berhasil diverifikasi');
          setTimeout(() => window.location.reload(), 1500);
        } else {
          throw new Error(data.message || 'Terjadi kesalahan');
        }
      } catch (error) {
        this.showNotification('error', 'Gagal', error.message || 'Terjadi kesalahan saat memproses permintaan');
      } finally {
        this.loading = false;
      }
    },

    showNotification(type, title, message) {
      if (this.notification.timeout) clearTimeout(this.notification.timeout);
      this.notification = { show: true, type, title, message, timeout: null };
      this.notification.timeout = setTimeout(() => this.notification.show = false, 5000);
    }
  };
}

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const manager = document.querySelector('[x-data]').__x.$data;
    if (manager.showConfirmModal) {
      manager.showConfirmModal = false;
      document.body.style.overflow = '';
    }
  }
});
</script>

<?= $this->endSection() ?>