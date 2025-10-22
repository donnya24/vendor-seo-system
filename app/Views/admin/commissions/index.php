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
    
    <!-- Export Button Only -->
    <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
        <!-- Export Button -->
        <a href="<?= site_url('admin/commissions/export-csv') . 
            ($vendor_id && $vendor_id !== 'all' ? '?vendor_id=' . $vendor_id : '') . 
            ($status && $status !== 'all' ? ($vendor_id && $vendor_id !== 'all' ? '&' : '?') . 'status=' . $status : '') ?>" 
           class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition-colors flex items-center gap-2 w-full sm:w-auto justify-center">
            <i class="fas fa-file-export"></i> Export CSV
        </a>
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
            <option value="<?= $vendor['id'] ?>" <?= ($vendor_id == $vendor['id']) ? 'selected' : '' ?>>
              <?= esc($vendor['business_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
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
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Periode</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Vendor</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-right text-xs font-medium uppercase tracking-wider">Jumlah</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Bukti</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Diproses Oleh</th>
            <th scope="col" class="px-4 sm:px-6 py-3 text-center text-xs font-medium uppercase tracking-wider">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (!empty($commissions) && is_array($commissions)): ?>
            <?php $no = 1; foreach ($commissions as $c): ?>
              <tr class="hover:bg-gray-50 transition-colors">
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

                <!-- Diproses Oleh -->
                <td class="px-4 sm:px-6 py-4">
                    <?php if (!empty($c['action_by_name'])): ?>
                        <?php 
                        // Tentukan role user - langsung di view tanpa memanggil method controller
                        $role = 'admin'; // Default
                        
                        // Cek apakah user_id ada di tabel admin_profiles
                        $db = \Config\Database::connect();
                        $adminProfile = $db->table('admin_profiles')
                            ->where('user_id', $c['action_by_user_id'])
                            ->get()
                            ->getRowArray();
                            
                        // Cek apakah user_id ada di tabel seo_profiles
                        $seoProfile = $db->table('seo_profiles')
                            ->where('user_id', $c['action_by_user_id'])
                            ->get()
                            ->getRowArray();
                        
                        // Tentukan role berdasarkan hasil query
                        if ($seoProfile) {
                            $role = 'seo';
                        } elseif ($adminProfile) {
                            $role = 'admin';
                        }
                        
                        $iconClass = $role === 'seo' ? 'fa-user' : 'fa-user-tie';
                        $bgColor = $role === 'seo' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600';
                        $roleLabel = $role === 'seo' ? 'SEO' : 'Admin';
                        ?>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 <?= $bgColor ?> rounded-full flex items-center justify-center">
                                <i class="fas <?= $iconClass ?> text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900"><?= esc($c['action_by_name']) ?></div>
                                <div class="text-xs text-gray-500">
                                    <?= $roleLabel ?>
                                    <?php if (strtolower($c['status'] ?? '') === 'paid' && !empty($c['paid_at'])): ?>
                                        • <?= date('d/m/Y H:i', strtotime($c['paid_at'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif (!empty($c['action_by_user_id']) && $c['action_by_user_id'] != 1): ?>
                        <!-- Fallback jika action_by_name tidak ada tapi action_by_user_id ada -->
                        <?php 
                        // Cek role user
                        $role = 'admin'; // Default
                        
                        $db = \Config\Database::connect();
                        $adminProfile = $db->table('admin_profiles')
                            ->where('user_id', $c['action_by_user_id'])
                            ->get()
                            ->getRowArray();
                            
                        $seoProfile = $db->table('seo_profiles')
                            ->where('user_id', $c['action_by_user_id'])
                            ->get()
                            ->getRowArray();
                        
                        if ($seoProfile) {
                            $role = 'seo';
                        } elseif ($adminProfile) {
                            $role = 'admin';
                        }
                        
                        $iconClass = $role === 'seo' ? 'fa-user' : 'fa-user-tie';
                        $bgColor = $role === 'seo' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600';
                        $roleLabel = $role === 'seo' ? 'SEO' : 'Admin';
                        ?>
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 <?= $bgColor ?> rounded-full flex items-center justify-center">
                                <i class="fas <?= $iconClass ?> text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900"><?= $roleLabel ?> User #<?= $c['action_by_user_id'] ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php if (strtolower($c['status'] ?? '') === 'paid' && !empty($c['paid_at'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($c['paid_at'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php elseif (!empty($c['action_by']) && $c['action_by'] != 1): ?>
                        <!-- Fallback jika hanya action_by yang ada -->
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user-tie text-purple-600 text-xs"></i>
                            </div>
                            <div class="ml-2">
                                <div class="text-sm font-medium text-gray-900">ID: <?= $c['action_by'] ?></div>
                                <div class="text-xs text-gray-500">
                                    <?php if (strtolower($c['status'] ?? '') === 'paid' && !empty($c['paid_at'])): ?>
                                        <?= date('d/m/Y H:i', strtotime($c['paid_at'])) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <span class="text-gray-400 text-sm">-</span>
                    <?php endif; ?>
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
                        <i class="fas fa-check mr-1"></i> Verifikasi Pembayaran
                      </button>
                    <?php endif; ?>

                    <?php if ($currentStatus === 'paid'): ?>
                      <button @click="confirmAction('unpaid', <?= $c['id'] ?>)"
                              class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg text-xs font-medium transition-colors flex items-center justify-center gap-1">
                        <i class="fas fa-clock mr-1"></i> Tandai Belum Dibayar
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
        <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 rounded-full"
             :class="{
               'bg-red-100': actionType === 'delete',
               'bg-blue-100': actionType === 'verify',
               'bg-yellow-100': actionType === 'unpaid'
             }">
          <i class="text-2xl"
             :class="{
               'fas fa-trash text-red-600': actionType === 'delete',
               'fas fa-check-circle text-blue-600': actionType === 'verify',
               'fas fa-clock text-yellow-600': actionType === 'unpaid'
             }"></i>
        </div>
        <h3 class="text-lg font-semibold text-center text-gray-900 mb-2" x-text="modalTitle"></h3>
        <p class="text-gray-600 text-center mb-6" x-text="modalMessage"></p>
        
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
          <button @click="closeModal()"
                  class="px-4 py-2.5 border border-gray-300 rounded-lg text-gray-700 bg-white hover:bg-gray-50 font-medium transition-colors">
            Batal
          </button>
          <button @click="executeAction()"
                  class="px-4 py-2.5 rounded-lg text-white font-medium transition-colors"
                  :class="{
                    'bg-red-600 hover:bg-red-700': actionType === 'delete',
                    'bg-blue-600 hover:bg-blue-700': actionType === 'verify',
                    'bg-yellow-600 hover:bg-yellow-700': actionType === 'unpaid'
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
    z-index: 9999 !important;
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

/* PERBAIKAN: Style untuk body saat modal terbuka - gunakan pendekatan yang lebih baik */
body.modal-open {
    overflow: hidden !important;
    /* Menggunakan position: sticky dan top: 0 untuk menjaga posisi scroll */
    position: sticky;
    top: 0;
    width: 100%;
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
</style>

<script>
function commissionManager() {
    return {
        showConfirmModal: false,
        actionType: '',
        commissionId: null,
        loading: false,
        scrollPosition: 0, // Tambahkan variabel untuk menyimpan posisi scroll
        actionLabels: {
            'verify': 'Verifikasi Pembayaran',
            'unpaid': 'Tandai Belum Dibayar',
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
                'verify': 'Konfirmasi Verifikasi Pembayaran',
                'unpaid': 'Konfirmasi Tandai Belum Dibayar',
                'delete': 'Konfirmasi Penghapusan'
            };
            return titles[this.actionType] || 'Konfirmasi';
        },
        
        get modalMessage() {
            const messages = {
                'verify': 'Apakah Anda yakin ingin memverifikasi dan menandai komisi ini sebagai sudah dibayar?',
                'unpaid': 'Apakah Anda yakin ingin menandai komisi ini sebagai belum dibayar?',
                'delete': 'Apakah Anda yakin ingin menghapus komisi ini? Tindakan ini tidak dapat dibatalkan.'
            };
            return messages[this.actionType] || 'Apakah Anda yakin?';
        },
        
        confirmAction(action, id) {
            this.actionType = action;
            this.commissionId = id;
            
            // PERBAIKAN: Simpan posisi scroll sebelum membuka modal
            this.scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
            
            this.showConfirmModal = true;
            
            // PERBAIKAN: Gunakan pendekatan yang lebih baik untuk mencegah scroll
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.top = `-${this.scrollPosition}px`;
            document.body.style.width = '100%';
        },
        
        // PERBAIKAN: Tambahkan fungsi untuk menutup modal dengan benar
        closeModal() {
            this.showConfirmModal = false;
            
            // PERBAIKAN: Kembalikan posisi scroll ke semula
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.width = '';
            
            // Kembalikan posisi scroll ke posisi sebelumnya
            window.scrollTo(0, this.scrollPosition);
        },
                
        async executeAction() {
            this.closeModal(); // Gunakan fungsi closeModal yang baru
            this.loading = true;

            let url;
            
            switch(this.actionType) {
                case 'verify':
                    url = `<?= site_url('admin/commissions/verify') ?>/${this.commissionId}`;
                    break;
                case 'unpaid':
                    url = `<?= site_url('admin/commissions/unpaid') ?>/${this.commissionId}`;
                    break;
                case 'delete':
                    url = `<?= site_url('admin/commissions/delete') ?>/${this.commissionId}`;
                    break;
                default:
                    this.loading = false;
                    this.showNotification('error', 'Gagal', 'Aksi tidak valid.');
                    return;
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: { 
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

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
                console.error('Error executing action:', error);
                this.showNotification(
                    'error',
                    'Gagal',
                    error.message || 'Terjadi kesalahan saat memproses permintaan'
                );
            } finally {
                this.loading = false;
            }
        },

        async exportCsv() {
            this.loading = true;
            
            try {
                // Build URL dengan filter yang aktif
                const url = new URL('<?= site_url('admin/commissions/export-csv') ?>');
                
                // Tambahkan parameter filter jika ada
                const urlParams = new URLSearchParams(window.location.search);
                const vendorId = urlParams.get('vendor_id');
                const status = urlParams.get('status');
                
                if (vendorId && vendorId !== 'all') {
                    url.searchParams.set('vendor_id', vendorId);
                }
                
                if (status && status !== 'all') {
                    url.searchParams.set('status', status);
                }
                
                // Trigger download
                window.location.href = url.toString();
                
                this.showNotification(
                    'success', 
                    'Export Berhasil', 
                    'Data komisi berhasil diunduh. File akan segera tersedia.'
                );
                
            } catch (error) {
                console.error('Error exporting CSV:', error);
                this.showNotification(
                    'error',
                    'Export Gagal',
                    'Terjadi kesalahan saat mengekspor data. Silakan coba lagi.'
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
            
            const timeoutDuration = type === 'error' && message.length > 100 ? 8000 : 5000;
            this.notification.timeout = setTimeout(() => {
                this.notification.show = false;
            }, timeoutDuration);
        },

        hideNotification() {
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }
            this.notification.show = false;
        },

        resetState() {
            this.closeModal(); // Gunakan fungsi closeModal yang baru
            this.actionType = '';
            this.commissionId = null;
            this.loading = false;
            this.hideNotification();
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('commissionManager', commissionManager);
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const manager = document.querySelector('[x-data]').__x.$data;
        if (manager.showConfirmModal) {
            manager.closeModal(); // Gunakan fungsi closeModal yang baru
        }
    }
});
</script>

<?= $this->include('admin/layouts/footer'); ?>