<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6" x-data="vendorManager()">
  <!-- Header -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-gray-200">
    <div>
      <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
        <i class="fas fa-building text-blue-600"></i> Daftar Vendor
      </h1>
      <p class="mt-1 text-sm text-gray-600">Kelola vendor dan verifikasi pendaftaran</p>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-start">
      <i class="fas fa-check-circle mt-0.5 mr-2"></i>
      <div><?= session()->getFlashdata('success') ?></div>
    </div>
  <?php elseif (session()->getFlashdata('error')): ?>
    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm flex items-start">
      <i class="fas fa-exclamation-circle mt-0.5 mr-2"></i>
      <div><?= session()->getFlashdata('error') ?></div>
    </div>
  <?php endif; ?>

  <!-- Table -->
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <h3 class="text-lg font-semibold text-gray-800">Daftar Vendor</h3>
      <div class="text-sm text-gray-500">
        <i class="fas fa-database mr-1"></i>
        <span><?= !empty($vendors) ? count($vendors) : 0 ?> vendor ditemukan</span>
      </div>
    </div>
    
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-blue-600">
        <tr>
          <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">No</th>
          <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Nama Usaha</th>
          <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Pemilik</th>
          <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Kontak</th>
          <th scope="col" class="px-5 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Komisi Diminta</th>
          <th scope="col" class="px-5 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
          <th scope="col" class="px-5 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
        </tr>
      </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (!empty($vendors)): ?>
            <?php $no = 1; foreach ($vendors as $vendor): ?>
              <tr class="hover:bg-gray-50 transition">
                <td class="px-5 py-4 text-center text-gray-600"><?= $no++ ?></td>
                <td class="px-5 py-4 font-medium text-gray-900">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                      <i class="fas fa-building text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900"><?= esc($vendor['business_name']) ?></div>
                    </div>
                  </div>
                </td>
                <td class="px-5 py-4 text-gray-700"><?= esc($vendor['owner_name']) ?></td>
                <td class="px-5 py-4 text-gray-700">
                  <div class="flex items-center text-sm">
                    <i class="fas fa-phone-alt text-gray-400 mr-2"></i>
                    <span><?= esc($vendor['phone']) ?></span>
                  </div>
                  <?php if (!empty($vendor['whatsapp_number'])): ?>
                    <div class="flex items-center text-sm text-green-600 mt-1">
                      <i class="fab fa-whatsapp mr-2"></i>
                      <span><?= esc($vendor['whatsapp_number']) ?></span>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 text-center text-gray-900 font-medium">
                  <?php if ($vendor['commission_type'] === 'percent' && !empty($vendor['requested_commission'])): ?>
                    <?= esc($vendor['requested_commission']) ?>%
                  <?php elseif ($vendor['commission_type'] === 'nominal' && !empty($vendor['requested_commission_nominal'])): ?>
                    Rp <?= number_format($vendor['requested_commission_nominal'], 0, ',', '.') ?>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 text-center">
                  <?php if ($vendor['status'] === 'verified'): ?>
                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-green-100 text-green-800">
                      <i class="fas fa-check-circle mr-1"></i> Verified
                    </span>
                  <?php elseif ($vendor['status'] === 'rejected'): ?>
                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-red-100 text-red-800">
                      <i class="fas fa-times-circle mr-1"></i> Rejected
                    </span>
                  <?php elseif ($vendor['status'] === 'inactive'): ?>
                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-gray-100 text-gray-800">
                      <i class="fas fa-ban mr-1"></i> Inactive
                    </span>
                  <?php else: ?>
                    <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-medium rounded-full bg-yellow-100 text-yellow-800">
                      <i class="fas fa-clock mr-1"></i> Pending
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 text-center">
                  <?php if ($vendor['status'] === 'pending'): ?>
                    <button @click="confirmApprove(<?= $vendor['id'] ?>, '<?= esc($vendor['business_name']) ?>')"
                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-green-600 text-white text-xs font-medium shadow-sm hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                      <i class="fas fa-check"></i> Approve
                    </button>
                  <?php else: ?>
                    <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-600 text-xs">Tidak Ada Aksi</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-5 py-10 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <i class="fas fa-building text-gray-300 text-4xl mb-3"></i>
                  <p class="text-lg font-medium text-gray-900">Belum ada data vendor</p>
                  <p class="mt-1 text-sm text-gray-500">Vendor yang terdaftar akan muncul di sini</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
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
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-green-100">
          <i class="fas fa-check text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2">Konfirmasi Approve</h3>
        <p class="text-gray-600 text-center mb-6">
          Apakah Anda yakin ingin menyetujui vendor <span class="font-semibold" x-text="vendorName"></span>?
        </p>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="showConfirmModal = false"
                  class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition">
            Batal
          </button>
          <button @click="executeApprove()"
                  class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
            Approve Vendor
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
function vendorManager() {
    return {
        showConfirmModal: false,
        vendorId: null,
        vendorName: '',
        loading: false,
        notification: {
            show: false,
            type: 'success',
            title: '',
            message: '',
            timeout: null
        },
        
        confirmApprove(id, name) {
            this.vendorId = id;
            this.vendorName = name;
            this.showConfirmModal = true;
        },
        
        async executeApprove() {
            this.showConfirmModal = false;
            this.loading = true;
            
            const url = `<?= site_url('seo/vendor_verify/approve') ?>/${this.vendorId}`;
                
            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.showNotification(
                        'success', 
                        'Berhasil', 
                        `Vendor ${this.vendorName} berhasil disetujui`
                    );
                    
                    // Reload setelah delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
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