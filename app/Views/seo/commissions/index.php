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
          <h3 class="text-sm font-medium text-gray-500">Sudah Disetujui</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?php 
              $approvedCount = count(array_filter($commissions, function($c) {
                return in_array(strtolower($c['status'] ?? ''), ['approved', 'paid']);
              }));
              echo $approvedCount;
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
          <h3 class="text-sm font-medium text-gray-500">Menunggu Verifikasi</h3>
          <p class="text-2xl font-bold text-gray-800 mt-1">
            <?php 
              $pendingCount = count(array_filter($commissions, function($c) {
                return strtolower($c['status'] ?? '') === 'unpaid';
              }));
              echo $pendingCount;
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
                <!-- No -->
                <td class="px-5 py-4 text-center text-gray-600"><?= $no++ ?></td>

                <!-- Periode -->
                <td class="px-5 py-4">
                  <div class="text-sm font-medium text-gray-900"><?= esc($c['period_start']) ?></div>
                  <div class="text-xs text-gray-500">— <?= esc($c['period_end']) ?></div>
                </td>

                <!-- Vendor -->
                <td class="px-5 py-4">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                      <i class="fas fa-building text-blue-600 text-sm"></i>
                    </div>
                    <div class="ml-3">
                      <div class="text-sm font-medium text-gray-900"><?= esc($c['vendor_name'] ?? '—') ?></div>
                    </div>
                  </div>
                </td>

                <!-- Jumlah -->
                <td class="px-5 py-4 text-right font-semibold text-gray-900">
                  Rp <?= number_format($c['amount'] ?? 0, 0, ',', '.') ?>
                </td>

                <!-- Bukti -->
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

                <!-- Status -->
                <td class="px-5 py-4 text-center">
                  <?php 
                    $status = strtolower($c['status'] ?? '-');
                    $badgeClass = 'bg-gray-100 text-gray-700';
                    $label = ucfirst($status);
                    $icon = '';
                    if ($status === 'approved' || $status === 'paid') {
                      $badgeClass = 'bg-green-100 text-green-800';
                      $label = 'Approved';
                      $icon = '<i class="fas fa-check-circle mr-1"></i>';
                    } elseif ($status === 'rejected') {
                      $badgeClass = 'bg-red-100 text-red-800';
                      $label = 'Rejected';
                      $icon = '<i class="fas fa-times-circle mr-1"></i>';
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

                <!-- Aksi -->
                <td class="px-5 py-4 text-center">
                  <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    <?php if ($status === 'unpaid'): ?>
                      <button @click="confirmAction('verify', <?= $c['id'] ?>)"
                              class="px-3 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md text-xs font-medium shadow-sm transition flex items-center justify-center">
                        <i class="fas fa-check mr-1"></i> Verify
                      </button>
                      <button @click="confirmAction('reject', <?= $c['id'] ?>)"
                              class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-medium shadow-sm transition flex items-center justify-center">
                        <i class="fas fa-times mr-1"></i> Reject
                      </button>
                    <?php elseif ($status === 'paid'): ?>
                      <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-double mr-1"></i> Sudah Dibayar
                      </span>
                    <?php elseif ($status === 'rejected'): ?>
                      <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-ban mr-1"></i> Ditolak
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
                  <p class="mt-1 text-sm text-gray-500">Belum ada komisi vendor yang tersedia</p>
                </div>
              </td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
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
             :class="actionType === 'verify' ? 'bg-green-100' : 'bg-red-100'">
          <i class="text-2xl"
             :class="actionType === 'verify' ? 'fas fa-check text-green-600' : 'fas fa-times text-red-600'"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2">
          Konfirmasi <span x-text="actionType === 'verify' ? 'Verifikasi' : 'Penolakan'"></span>
        </h3>
        <p class="text-gray-600 text-center mb-6">
          Apakah Anda yakin ingin <span x-text="actionType === 'verify' ? 'menyetujui' : 'menolak'"></span> komisi ini?
        </p>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="showConfirmModal = false"
                  class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition">
            Batal
          </button>
          <button @click="executeAction()"
                  class="px-4 py-2 rounded-lg text-white font-medium transition"
                  :class="actionType === 'verify' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'">
            <span x-text="actionType === 'verify' ? 'Verify' : 'Reject'"></span>
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
        <button @click="notification.show = false" class="text-gray-400 hover:text-gray-500 focus:outline-none">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div x-show="loading" x-cloak
       class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 flex flex-col items-center">
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
        loading: false,
        notification: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            timeout: null
        },
        
        confirmAction(action, id) {
            this.actionType = action;
            this.commissionId = id;
            this.showConfirmModal = true;
        },
        
        async executeAction() {
            this.showConfirmModal = false;
            this.loading = true;

            const url = this.actionType === 'verify' 
                ? `<?= site_url('seo/commissions/approve') ?>/${this.commissionId}?vendor_id=<?= esc($vendorId) ?>`
                : `<?= site_url('seo/commissions/reject') ?>/${this.commissionId}?vendor_id=<?= esc($vendorId) ?>`;

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
                    this.showNotification(
                        'success',
                        'Berhasil',
                        `Komisi berhasil ${this.actionType === 'verify' ? 'diverifikasi' : 'ditolak'}`
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

        showNotification(type, title, message) {
            // Clear any existing timeout
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
            
            // Set notification properties
            this.notification.type = type;
            this.notification.title = title;
            this.notification.message = message;
            this.notification.show = true;
            
            // Auto hide after 5 seconds
            this.notification.timeout = setTimeout(() => {
                this.notification.show = false;
            }, 5000);
        }
    }
}
</script>

<?= $this->endSection() ?>