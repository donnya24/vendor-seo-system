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
            <th scope="col" class="px-5 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Diproses Oleh</th>
            <th scope="col" class="px-5 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (!empty($vendors)): ?>
            <?php 
            $no = 1; 
            foreach ($vendors as $vendor): 
              // Ambil data user yang melakukan aksi
              $actionByUser = null;
              if (!empty($vendor['action_by'])) {
                $db = \Config\Database::connect();
                $actionByUser = $db->table('users')
                                 ->select('users.username, seo_profiles.name as seo_name')
                                 ->join('seo_profiles', 'seo_profiles.user_id = users.id', 'left')
                                 ->where('users.id', $vendor['action_by'])
                                 ->get()
                                 ->getRowArray();
              }
            ?>
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
                <td class="px-5 py-4 text-center text-gray-600 text-xs">
                  <?php if (!empty($actionByUser)): ?>
                    <div class="flex flex-col items-center">
                      <div class="font-medium text-gray-900">
                        <?= esc($actionByUser['seo_name'] ?? $actionByUser['username'] ?? 'Unknown') ?>
                      </div>
                      <div class="text-gray-500 text-xs mt-1">
                        <?php if (!empty($vendor['approved_at'])): ?>
                          <?= date('d M Y', strtotime($vendor['approved_at'])) ?>
                        <?php elseif (!empty($vendor['rejected_at'])): ?>
                          <?= date('d M Y', strtotime($vendor['rejected_at'])) ?>
                        <?php elseif (!empty($vendor['updated_at'])): ?>
                          <?= date('d M Y', strtotime($vendor['updated_at'])) ?>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="px-5 py-4 text-center">
                  <?php if ($vendor['status'] === 'pending'): ?>
                    <div class="flex flex-col gap-2 items-center">
                      <div class="flex gap-2">
                        <button @click="confirmApprove(<?= $vendor['id'] ?>, '<?= esc($vendor['business_name']) ?>')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-green-600 text-white text-xs font-medium shadow-sm hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                          <i class="fas fa-check"></i> Approve
                        </button>

                        <!-- Button Reject -->
                        <button @click="confirmReject(<?= $vendor['id'] ?>, '<?= esc($vendor['business_name']) ?>')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-red-600 text-white text-xs font-medium shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                          <i class="fas fa-times"></i> Reject
                        </button>
                      </div>
                    </div>
                  <?php else: ?>
                    <div class="flex flex-col items-center">
                      <span class="px-3 py-1.5 rounded-md bg-gray-100 text-gray-600 text-xs mb-1">Tidak Ada Aksi</span>
                      <?php if (!empty($vendor['rejection_reason'])): ?>
                        <button @click="$dispatch('show-rejection-reason', {reason: '<?= esc($vendor['rejection_reason']) ?>', vendor: '<?= esc($vendor['business_name']) ?>'})"
                                class="text-xs text-blue-600 hover:text-blue-800 underline">
                          Lihat Alasan
                        </button>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="px-5 py-10 text-center text-gray-500">
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

  <!-- Modal Konfirmasi - PERBAIKAN: Backdrop overlay yang memenuhi seluruh halaman -->
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
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
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

  <!-- Modal Konfirmasi Reject - PERBAIKAN: Backdrop overlay yang memenuhi seluruh halaman -->
  <div x-show="showRejectModal" x-cloak
      class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
      style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; width: 100vw; height: 100vh;"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0"
      x-transition:enter-end="opacity-100"
      x-transition:leave="transition ease-in duration-200"
      x-transition:leave-start="opacity-100"
      x-transition:leave-end="opacity-0">
    
    <!-- Modal Content -->
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
        @click.stop
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-95 translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-1">
      
      <div class="p-6">
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-red-100">
          <i class="fas fa-times text-red-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2">Konfirmasi Reject</h3>
        <p class="text-gray-600 text-center mb-4">
          Apakah Anda yakin ingin menolak vendor <span class="font-semibold" x-text="vendorName"></span>?
        </p>
        
        <!-- Input Alasan Reject -->
        <div class="mb-4">
          <label for="rejectReason" class="block text-sm font-medium text-gray-700 mb-2">
            Alasan Penolakan <span class="text-red-500">*</span>
          </label>
          <textarea 
            id="rejectReason"
            x-model="rejectReason"
            placeholder="Masukkan alasan penolakan vendor..."
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition"
            required></textarea>
          <p class="text-xs text-gray-500 mt-1">Alasan penolakan akan dikirim ke vendor</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="showRejectModal = false"
                  class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition">
            Batal
          </button>
          <button @click="executeReject()"
                  :disabled="!rejectReason.trim()"
                  :class="!rejectReason.trim() ? 'bg-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-700'"
                  class="px-4 py-2 text-white rounded-lg font-medium transition">
            Reject Vendor
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Lihat Alasan Reject -->
  <div x-data="{ showRejectionReason: false, rejectionReason: '', vendorName: '' }"
       @show-rejection-reason.window="showRejectionReason = true; rejectionReason = $event.detail.reason; vendorName = $event.detail.vendor"
       x-show="showRejectionReason" x-cloak
       class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm">
    
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto"
         @click.stop>
      
      <div class="p-6">
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100">
          <i class="fas fa-info-circle text-blue-600 text-2xl"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2">Alasan Penolakan</h3>
        <p class="text-gray-600 text-center mb-4">
          Vendor: <span class="font-semibold" x-text="vendorName"></span>
        </p>
        
        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
          <p class="text-gray-700 whitespace-pre-line" x-text="rejectionReason"></p>
        </div>
        
        <div class="flex justify-center mt-6">
          <button @click="showRejectionReason = false"
                  class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Notification Toast - PERBAIKAN: Z-index yang lebih tinggi -->
  <div x-show="notification.show" x-cloak
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 transform translate-y-2"
       x-transition:enter-end="opacity-100 transform translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 transform translate-y-0"
       x-transition:leave-end="opacity-0 transform translate-y-2"
       class="fixed bottom-4 right-4 z-[100] bg-white rounded-lg shadow-lg border-l-4 p-4 max-w-md"
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

  <!-- Loading Overlay - PERBAIKAN: Backdrop overlay yang memenuhi seluruh halaman -->
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
/* PERBAIKAN: Backdrop overlay yang memenuhi seluruh halaman */
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
    z-index: 100 !important;
}

/* Pastikan modal backdrop menutupi seluruh viewport */
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

/* Loading overlay yang memenuhi halaman */
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

/* Pastikan body tidak scroll saat modal terbuka */
body.modal-open {
    overflow: hidden !important;
    height: 100vh !important;
    position: fixed !important;
    width: 100% !important;
}

/* Style untuk baris error */
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

/* Pastikan modal content di tengah */
.modal-content-center {
    position: fixed !important;
    top: 50% !important;
    left: 50% !important;
    transform: translate(-50%, -50%) !important;
    z-index: 10000 !important;
}

/* Smooth transitions untuk semua modal */
.modal-transition {
    transition: all 0.3s ease-in-out;
}
</style>

<script>
function vendorManager() {
    return {
        showConfirmModal: false,
        showRejectModal: false,
        vendorId: null,
        vendorName: '',
        rejectReason: '',
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
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        },
        
        confirmReject(id, name) {
            this.vendorId = id;
            this.vendorName = name;
            this.rejectReason = '';
            this.showRejectModal = true;
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        },
        
        async executeReject() {
            if (!this.rejectReason.trim()) {
                this.showNotification(
                    'error', 
                    'Gagal', 
                    'Alasan penolakan harus diisi'
                );
                return;
            }
            
            this.showRejectModal = false;
            this.loading = true;
            
            // Restore body overflow when modal is closed
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            
            const url = `<?= site_url('seo/vendor_verify/reject') ?>/${this.vendorId}`;
                
            try {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                formData.append('reject_reason', this.rejectReason);
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                let data;
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // If not JSON, it's probably an HTML error page
                    const text = await response.text();
                    console.error('Server returned HTML instead of JSON:', text.substring(0, 500));
                    throw new Error('Terjadi kesalahan server. Silakan coba lagi.');
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                if (data.success) {
                    this.showNotification(
                        'success', 
                        'Berhasil', 
                        `Vendor ${this.vendorName} berhasil ditolak`
                    );
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error('Error in executeReject:', error);
                this.showNotification(
                    'error', 
                    'Gagal', 
                    error.message || 'Terjadi kesalahan saat memproses permintaan'
                );
            } finally {
                this.loading = false;
                this.rejectReason = '';
            }
        },

        async executeApprove() {
            this.showConfirmModal = false;
            this.loading = true;
            
            // Restore body overflow when modal is closed
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            
            const url = `<?= site_url('seo/vendor_verify/approve') ?>/${this.vendorId}`;
                
            try {
                const formData = new FormData();
                formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });
                
                // Check if response is JSON
                const contentType = response.headers.get('content-type');
                let data;
                
                if (contentType && contentType.includes('application/json')) {
                    data = await response.json();
                } else {
                    // If not JSON, it's probably an HTML error page
                    const text = await response.text();
                    console.error('Server returned HTML instead of JSON:', text.substring(0, 500));
                    throw new Error('Terjadi kesalahan server. Silakan coba lagi.');
                }
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                if (data.success) {
                    this.showNotification(
                        'success', 
                        'Berhasil', 
                        `Vendor ${this.vendorName} berhasil disetujui`
                    );
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error('Error in executeApprove:', error);
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

// Event listener untuk menangani escape key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const manager = document.querySelector('[x-data]').__x.$data;
        if (manager.showConfirmModal) {
            manager.showConfirmModal = false;
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }
        if (manager.showRejectModal) {
            manager.showRejectModal = false;
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
        }
    }
});
</script>

<?= $this->endSection() ?>