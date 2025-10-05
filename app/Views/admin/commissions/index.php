<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="space-y-6" x-data="commissionManager()">
  <!-- Header -->
  <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 pb-4 border-b border-gray-200 px-4 sm:px-6">
    <div class="w-full lg:w-auto">
      <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fas fa-wallet text-blue-600"></i> Manajemen Komisi Vendor
      </h1>
      <p class="mt-1 text-sm text-gray-600">Kelola dan verifikasi komisi semua vendor</p>
    </div>
    
    <!-- Bulk Actions -->
    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
      <div class="flex gap-2 w-full sm:w-auto">
        <!-- PERBAIKAN: Hanya ada verify dan delete karena status hanya unpaid/paid -->
        <select x-model="bulkAction" class="rounded-lg border-gray-300 text-sm py-2 px-3 w-full sm:w-40">
          <option value="">Aksi Massal</option>
          <option value="verify">Verifikasi & Bayar</option>
          <option value="delete">Hapus</option>
        </select>
        <button @click="executeBulkAction()" 
                :disabled="!bulkAction || selectedCommissions.length === 0"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors whitespace-nowrap w-full sm:w-auto">
          Terapkan
        </button>
      </div>
    </div>
  </div>

  <!-- Filter Section -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mx-4 sm:mx-6">
    <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Vendor</label>
        <select name="vendor_id" class="w-full rounded-lg border-gray-300 text-sm py-2 px-3 focus:border-blue-500 focus:ring-blue-500">
          <option value="all">Semua Vendor</option>
          <?php foreach ($vendors as $vendor): ?>
            <option value="<?= $vendor['id'] ?>" <?= ($vendorId == $vendor['id']) ? 'selected' : '' ?>>
              <?= esc($vendor['business_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
        <!-- PERBAIKAN: Sesuaikan opsi status dengan enum database (unpaid/paid) -->
        <select name="status" class="w-full rounded-lg border-gray-300 text-sm py-2 px-3 focus:border-blue-500 focus:ring-blue-500">
          <option value="all">Semua Status</option>
          <option value="unpaid" <?= ($status == 'unpaid') ? 'selected' : '' ?>>Unpaid</option>
          <option value="paid" <?= ($status == 'paid') ? 'selected' : '' ?>>Paid</option>
        </select>
      </div>
      
      <div class="flex items-end gap-2">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors flex items-center gap-2 w-full sm:w-auto justify-center">
          <i class="fas fa-filter"></i> Filter
        </button>
        <a href="<?= site_url('admin/commissions') ?>" 
           class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors flex items-center gap-2 w-full sm:w-auto justify-center">
          <i class="fas fa-refresh"></i> Reset
        </a>
      </div>
    </form>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-4 sm:px-6">
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
  </div>

  <!-- Table -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mx-4 sm:mx-6">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <h3 class="text-lg font-semibold text-gray-800">Daftar Komisi</h3>
      <div class="text-sm text-gray-500 flex items-center gap-4">
        <span x-show="selectedCommissions.length > 0" class="text-blue-600 font-medium bg-blue-50 px-3 py-1 rounded-full">
          <span x-text="selectedCommissions.length"></span> terpilih
        </span>
        <span class="flex items-center gap-1">
          <i class="fas fa-database"></i>
          <span><?= !empty($commissions) && is_array($commissions) ? count($commissions) : 0 ?> record</span>
        </span>
      </div>
    </div>
    
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center w-12">
              <input type="checkbox" @change="toggleAllCommissions($event)" class="rounded border-gray-300">
            </th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Periode</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendor</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Jumlah</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Bukti</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (!empty($commissions) && is_array($commissions)): ?>
            <?php $no = 1; foreach ($commissions as $c): ?>
              <tr class="hover:bg-gray-50 transition-colors">
                <!-- Checkbox -->
                <td class="px-4 sm:px-6 py-4 text-center">
                  <input type="checkbox" 
                         value="<?= $c['id'] ?>" 
                         @change="toggleCommission($event)"
                         class="commission-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </td>

                <!-- No -->
                <td class="px-4 sm:px-6 py-4 text-center text-gray-600 font-medium"><?= $no++ ?></td>

                <!-- Periode -->
                <td class="px-4 sm:px-6 py-4">
                  <div class="text-sm font-medium text-gray-900"><?= esc($c['period_start']) ?></div>
                  <div class="text-xs text-gray-500">sampai <?= esc($c['period_end']) ?></div>
                </td>

                <!-- Vendor -->
                <td class="px-4 sm:px-6 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                      <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900"><?= esc($c['vendor_name'] ?? '—') ?></div>
                      <div class="text-xs text-gray-500"><?= esc($c['owner_name'] ?? '') ?></div>
                    </div>
                  </div>
                </td>

                <!-- Jumlah -->
                <td class="px-4 sm:px-6 py-4 text-right">
                  <div class="font-semibold text-gray-900">
                    Rp <?= number_format($c['amount'] ?? 0, 0, ',', '.') ?>
                  </div>
                </td>

                <!-- Bukti -->
                <td class="px-4 sm:px-6 py-4 text-center">
                  <?php if (!empty($c['proof'])): ?>
                    <?php 
                      $ext = strtolower(pathinfo($c['proof'], PATHINFO_EXTENSION));
                      $imgExt = ['jpg','jpeg','png','gif','webp'];
                    ?>
                    <?php if (in_array($ext, $imgExt)): ?>
                      <div class="flex justify-center mb-2">
                        <img src="<?= base_url('uploads/commissions/'.$c['proof']) ?>" 
                             alt="Bukti Transfer" 
                             class="h-12 w-12 object-cover rounded-lg border border-gray-200 shadow-sm cursor-pointer hover:shadow-md transition-all"
                             onclick="window.open('<?= base_url('uploads/commissions/'.$c['proof']) ?>', '_blank')">
                      </div>
                    <?php endif; ?>
                    <a href="<?= base_url('uploads/commissions/'.$c['proof']) ?>" 
                       target="_blank" 
                       class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                      <i class="fas fa-external-link-alt mr-1 text-xs"></i> Lihat
                    </a>
                  <?php else: ?>
                    <span class="text-gray-400 text-sm">-</span>
                  <?php endif; ?>
                </td>

                <!-- Status -->
                <td class="px-4 sm:px-6 py-4 text-center">
                  <?php 
                    $status = strtolower($c['status'] ?? '-');
                    $badgeClass = 'bg-gray-100 text-gray-700';
                    $label = 'Unknown';
                    $icon = '<i class="fas fa-question-circle mr-1"></i>';
                    
                    if ($status === 'paid') {
                      $badgeClass = 'bg-green-100 text-green-800';
                      $label = 'Paid';
                      $icon = '<i class="fas fa-check-double mr-1"></i>';
                    } elseif ($status === 'unpaid') {
                      $badgeClass = 'bg-yellow-100 text-yellow-800';
                      $label = 'Unpaid';
                      $icon = '<i class="fas fa-clock mr-1"></i>';
                    }
                  ?>
                  <span class="px-3 py-1.5 inline-flex items-center text-xs leading-5 font-medium rounded-full <?= $badgeClass ?>">
                    <?= $icon ?><?= $label ?>
                  </span>
                </td>

                <!-- Aksi -->
                <td class="px-4 sm:px-6 py-4 text-center">
                  <div class="flex flex-col gap-2 justify-center">
                    <?php 
                    $currentStatus = strtolower($c['status'] ?? '');
                    ?>
                    
                    <?php if ($currentStatus === 'unpaid'): ?>
                      <button @click="confirmAction('verify', <?= $c['id'] ?>)"
                              class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-check mr-1"></i> Verifikasi & Bayar
                      </button>
                    <?php endif; ?>
                    
                    <!-- Delete button for all status -->
                    <button @click="confirmAction('delete', <?= $c['id'] ?>)"
                            class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1">
                      <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="px-4 sm:px-6 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                  <p class="text-lg font-medium text-gray-900 mb-2">Tidak ada data komisi</p>
                  <p class="text-sm text-gray-500">Belum ada komisi vendor yang tersedia</p>
                </div>
              </td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php if ($pager->getPageCount() > 1): ?>
      <div class="px-4 sm:px-6 py-4 border-t border-gray-200 bg-gray-50">
        <?= $pager->links() ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- Modal Konfirmasi -->
  <div x-show="showConfirmModal" x-cloak
       class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0"
       x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100"
       x-transition:leave-end="opacity-0">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md"
         @click.stop
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-1">
      <div class="p-6">
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full"
             :class="{
               'bg-red-100': actionType === 'delete',
               'bg-blue-100': actionType === 'verify'
             }">
          <i class="text-2xl"
             :class="{
               'fas fa-trash text-red-600': actionType === 'delete',
               'fas fa-check-circle text-blue-600': actionType === 'verify'
             }"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2" x-text="modalTitle"></h3>
        <p class="text-gray-600 text-center mb-6" x-text="modalMessage"></p>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="showConfirmModal = false"
                  class="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
            Batal
          </button>
          <button @click="executeAction()"
                  class="px-4 py-2.5 rounded-lg text-white font-medium transition-colors"
                  :class="{
                    'bg-red-600 hover:bg-red-700': actionType === 'delete',
                    'bg-blue-600 hover:bg-blue-700': actionType === 'verify'
                  }">
            <span x-text="actionLabels[actionType]"></span>
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
       class="fixed bottom-4 right-4 z-50 bg-white rounded-lg shadow-lg border-l-4 p-4 max-w-md"
       :class="{
         'border-green-500': notification.type === 'success',
         'border-red-500': notification.type === 'error'
       }">
    <div class="flex items-start">
      <div class="flex-shrink-0">
        <i class="fas text-xl"
           :class="{
             'fa-check-circle text-green-500': notification.type === 'success',
             'fa-exclamation-circle text-red-500': notification.type === 'error'
           }"></i>
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
       class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex flex-col items-center shadow-xl">
      <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500 mb-4"></div>
      <p class="text-gray-700 font-medium">Memproses...</p>
    </div>
  </div>

<script>
function commissionManager() {
    return {
        showConfirmModal: false,
        actionType: '',
        commissionId: null,
        bulkAction: '',
        selectedCommissions: [],
        loading: false,
        actionLabels: {
            'verify': 'Verifikasi & Bayar',
            'delete': 'Hapus'
        },
        notification: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            timeout: null
        },
        
        get modalTitle() {
            const titles = {
                'verify': 'Konfirmasi Verifikasi & Pembayaran',
                'delete': 'Konfirmasi Penghapusan'
            };
            return titles[this.actionType] || 'Konfirmasi';
        },
        
        get modalMessage() {
            const messages = {
                'verify': 'Apakah Anda yakin ingin memverifikasi dan menandai komisi ini sebagai sudah dibayar?',
                'delete': 'Apakah Anda yakin ingin menghapus komisi ini? Tindakan ini tidak dapat dibatalkan.'
            };
            return messages[this.actionType] || 'Apakah Anda yakin?';
        },
        
        confirmAction(action, id) {
            this.actionType = action;
            this.commissionId = id;
            this.showConfirmModal = true;
        },
        
        async executeAction() {
            this.showConfirmModal = false;
            this.loading = true;

            let url, method;
            
            switch(this.actionType) {
                case 'verify':
                    url = `<?= site_url('admin/commissions/verify') ?>/${this.commissionId}`;
                    method = 'POST';
                    break;
                case 'delete':
                    url = `<?= site_url('admin/commissions/delete') ?>/${this.commissionId}`;
                    method = 'POST';
                    break;
                default:
                    this.loading = false;
                    this.showNotification('error', 'Gagal', 'Aksi tidak valid.');
                    return;
            }

            try {
                const response = await fetch(url, {
                    method: method,
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification(
                        'success',
                        'Berhasil',
                        data.message || `Komisi berhasil ${this.actionLabels[this.actionType].toLowerCase()}`
                    );

                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                this.showNotification(
                    'error',
                    'Gagal',
                    error.message || 'Terjadi kesalahan saat memproses permintaan'
                );
            } finally {
                this.loading = false;
            }
        },

        toggleCommission(event) {
            const commissionId = event.target.value;
            if (event.target.checked) {
                if (!this.selectedCommissions.includes(commissionId)) {
                    this.selectedCommissions.push(commissionId);
                }
            } else {
                this.selectedCommissions = this.selectedCommissions.filter(id => id !== commissionId);
            }
        },

        toggleAllCommissions(event) {
            const checkboxes = document.querySelectorAll('.commission-checkbox');
            if (event.target.checked) {
                this.selectedCommissions = Array.from(checkboxes).map(cb => cb.value);
                checkboxes.forEach(cb => cb.checked = true);
            } else {
                this.selectedCommissions = [];
                checkboxes.forEach(cb => cb.checked = false);
            }
        },

        async executeBulkAction() {
            if (!this.bulkAction || this.selectedCommissions.length === 0) {
                this.showNotification('error', 'Gagal', 'Pilih aksi dan komisi yang akan diproses.');
                return;
            }

            this.loading = true;

            try {
                // PERBAIKAN: Kirim data sebagai JSON
                const response = await fetch(`<?= site_url('admin/commissions/bulk-action') ?>`, {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: this.bulkAction,
                        commission_ids: this.selectedCommissions
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.showNotification(
                        'success',
                        'Berhasil',
                        data.message || `Aksi massal berhasil diterapkan pada ${this.selectedCommissions.length} komisi`
                    );

                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat memproses aksi massal');
                }
            } catch (error) {
                this.showNotification(
                    'error',
                    'Gagal',
                    error.message || 'Terjadi kesalahan saat memproses aksi massal'
                );
            } finally {
                this.loading = false;
            }
        },

        showNotification(type, title, message) {
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
            
            this.notification.type = type;
            this.notification.title = title;
            this.notification.message = message;
            this.notification.show = true;
            
            this.notification.timeout = setTimeout(() => {
                this.notification.show = false;
            }, 5000);
        }
    }
}
</script>

<?= $this->include('admin/layouts/footer'); ?>