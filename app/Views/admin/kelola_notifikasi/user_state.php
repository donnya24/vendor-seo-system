<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap" class="flex-1 flex flex-col overflow-hidden relative z-10">
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
      <div>
        <h1 class="text-lg font-bold text-gray-800">Notifikasi Terhapus</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola notifikasi yang telah dihapus oleh user</p>
      </div>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto bg-gray-50 pt-4">
    <div class="px-4 sm:px-6 py-6">
      
      <!-- HEADER DENGAN FILTER DAN DELETE ALL -->
      <div class="mb-5 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <!-- FILTER VENDOR -->
        <form method="get" class="flex items-center gap-3">
          <div class="flex items-center gap-2">
            <label for="filter" class="font-medium text-gray-700">Filter:</label>
            <select id="filter" name="filter" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
              <option value="">Semua Notifikasi</option>
              <option value="read" <?= ($filter ?? '') == 'read' ? 'selected' : '' ?>>Notifikasi Terbaca</option>
              <option value="unread" <?= ($filter ?? '') == 'unread' ? 'selected' : '' ?>>Notifikasi Belum Dibaca</option>
              <option value="hidden" <?= ($filter ?? '') == 'hidden' ? 'selected' : '' ?>>Notifikasi Terhapus</option>
            </select>
          </div>
          <div class="flex items-center gap-2">
            <label for="search" class="font-medium text-gray-700">Pencarian:</label>
            <input type="text" id="search" name="search" value="<?= esc($search ?? '') ?>" 
                   class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500" 
                   placeholder="Cari nama user, judul, pesan, atau tipe...">
          </div>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow">
            Filter
          </button>
        </form>
        <!-- TOMBOL KEMBALI -->
        <a href="<?= base_url('admin/notification-management') ?>" 
          class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
          <i class="fas fa-arrow-left"></i> Kembali ke Notifikasi
        </a>
        <!-- DELETE ALL BUTTON -->
        <?php if (!empty($notifications)): ?>
          <div class="flex space-x-2">
            <button id="deleteSelectedBtn" 
                    class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                    disabled>
              <i class="fas fa-trash"></i> Hapus yang Dipilih
            </button>
          </div>
        <?php endif; ?>
      </div>

      <!-- INFO STATS -->
      <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex flex-wrap gap-4 text-sm text-blue-800">
          <div class="flex items-center gap-1">
            <i class="fas fa-bell"></i>
            <span>Total Notifikasi: <?= number_format($stats['total_notifications'] ?? 0) ?></span>
          </div>
          <div class="flex items-center gap-1">
            <i class="fas fa-eye-slash"></i>
            <span>Belum Dibaca: <?= number_format($stats['unread_count'] ?? 0) ?></span>
          </div>
          <div class="flex items-center gap-1">
            <i class="fas fa-trash"></i>
            <span>Notifikasi Terhapus: <?= number_format($stats['user_state_count'] ?? 0) ?></span>
          </div>
        </div>
      </div>
      
      <!-- Flash Messages -->
      <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-3 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-2">
              <p class="text-xs text-green-700">
                <?= session()->getFlashdata('success') ?>
              </p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-3 rounded">
          <div class="flex">
            <div class="flex-shrink-0">
              <i class="fas fa-exclamation-circle text-red-400"></i>
            </div>
            <div class="ml-2">
              <p class="text-xs text-red-700">
                <?= session()->getFlashdata('error') ?>
              </p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- TABLE -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-purple-600 border-b border-purple-700">
          <h3 class="text-lg font-medium text-white">
            Daftar Notifikasi Terhapus (<?= $totalNotifications ?? 0 ?>)
          </h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-purple-600 to-indigo-700 text-white">
              <tr>
                <th class="px-4 py-3 text-center text-xs font-semibold w-12">
                  <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </th>
                <th class="px-4 py-3 text-center text-xs font-semibold">User</th>
                <th class="px-4 py-3 text-center text-xs font-semibold">Type</th>
                <th class="px-4 py-3 text-center text-xs font-semibold">Title</th>
                <th class="px-4 py-3 text-center text-xs font-semibold w-48">Message</th>
                <th class="px-4 py-3 text-center text-xs font-semibold">Status</th>
                <th class="px-4 py-3 text-center text-xs font-semibold">Tanggal Dihapus</th>
                <th class="px-4 py-3 text-center text-xs font-semibold">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <?php if (empty($notifications)): ?>
                <tr>
                  <td colspan="8" class="p-4 text-center text-gray-500">
                    <div class="py-8">
                      <i class="fas fa-trash-alt text-4xl text-gray-300 mb-2"></i>
                      <p class="text-lg">Tidak ada notifikasi terhapus</p>
                    </div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                  <tr>
                    <td class="px-4 py-2 text-center">
                      <input type="checkbox" class="notification-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                             value="<?= $notif['notification_id'] ?>">
                    </td>
                    <td class="px-4 py-2 text-center">
                      <div class="text-sm font-medium text-gray-900">
                        <?php
                        if (!empty($notif['admin_name'])) {
                          echo esc($notif['admin_name']) . ' <span class="text-purple-600 text-xs">(Admin)</span>';
                        } elseif (!empty($notif['seo_name'])) {
                          echo esc($notif['seo_name']) . ' <span class="text-blue-600 text-xs">(SEO)</span>';
                        } elseif (!empty($notif['vendor_name'])) {
                          echo esc($notif['vendor_name']) . ' <span class="text-green-600 text-xs">(Vendor)</span>';
                        } else {
                          echo esc($notif['username'] ?? 'Unknown User');
                        }
                        ?>
                      </div>
                    </td>
                    <td class="px-4 py-2 text-center">
                      <?php 
                      $type = $notif['type'] ?? 'general';
                      $colorClass = '';
                      
                      if ($type === 'system') {
                        $colorClass = 'bg-blue-100 text-blue-800';
                      } elseif ($type === 'announcement') {
                        $colorClass = 'bg-purple-100 text-purple-800';
                      } else {
                        $colorClass = 'bg-gray-100 text-gray-800';
                      }
                      ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $colorClass ?>">
                        <?= ucfirst(esc($type)) ?>
                      </span>
                    </td>
                    <td class="px-4 py-2 text-center">
                      <div class="text-sm font-medium text-gray-900 max-w-xs">
                        <?= esc($notif['title']) ?>
                      </div>
                    </td>
                    <td class="px-4 py-2 text-center">
                      <div class="text-sm text-gray-500 truncate" style="max-width: 150px;" title="<?= esc($notif['message']) ?>">
                        <?= (strlen(esc($notif['message'])) > 30) ? substr(esc($notif['message']), 0, 30) . '...' : esc($notif['message']) ?>
                      </div>
                      <button onclick="viewMessage('<?= esc($notif['notification_id']) ?>', '<?= esc(str_replace("'", "\'", $notif['title'])) ?>', '<?= esc(str_replace("'", "\'", $notif['message'])) ?>')" 
                              class="text-blue-600 hover:text-blue-800 text-xs mt-1">
                        <i class="fas fa-eye"></i> Lihat
                      </button>
                    </td>
                    <td class="px-4 py-2 text-center">
                      <?php if (!empty($notif['is_read'])): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <i class="fas fa-check mr-1"></i>
                          Dibaca
                        </span>
                      <?php else: ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                          <i class="fas fa-clock mr-1"></i>
                          Belum Dibaca
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-500 text-center">
                      <?= !empty($notif['hidden_at']) ? date('d M Y H:i', strtotime($notif['hidden_at'])) : '-' ?>
                    </td>
                    <td class="px-4 py-2 text-center">
                      <div class="flex space-x-2 justify-center">
                        <button onclick="deletePermanent(<?= $notif['notification_id'] ?>)" 
                                class="inline-flex items-center px-2.5 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
                                title="Hapus Permanen">
                          <i class="fas fa-trash-alt mr-1 text-xs"></i>
                          <span class="text-xs">Hapus</span>
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<!-- Modal untuk Detail Message -->
<div id="messageModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-indigo-600 px-4 py-3 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="messageModalTitle">Detail Pesan</h3>
                <button type="button" onclick="closeMessageModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mb-4">
                    <h4 class="text-md font-medium text-gray-900 mb-2" id="messageTitle"></h4>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700 whitespace-pre-wrap" id="messageContent"></p>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" onclick="closeMessageModal()" 
                            class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-times mr-2"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Global variables for CSRF
let currentCsrfToken = '<?= csrf_hash() ?>';
let currentCsrfName = '<?= csrf_token() ?>';

// Update CSRF token function
function updateCsrfToken(token, name) {
    currentCsrfToken = token;
    currentCsrfName = name;
    // Update meta tags
    document.querySelector('meta[name="csrf-token"]').setAttribute('content', token);
    document.querySelector('meta[name="csrf-name"]').setAttribute('content', name);
}

// Get CSRF token for form
function getCsrfFormData() {
    const formData = new FormData();
    formData.append(currentCsrfName, currentCsrfToken);
    return formData;
}

// Get default headers with CSRF
function getDefaultHeaders() {
    return {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-Token': currentCsrfToken
    };
}

// Buat notifikasi "toast" yang lebih kecil dan di pojok kanan atas
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    width: '300px',
    padding: '0.75rem',
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Fungsi untuk menampilkan modal detail pesan
function viewMessage(id, title, message) {
    document.getElementById('messageTitle').textContent = title;
    document.getElementById('messageContent').textContent = message;
    document.getElementById('messageModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Fungsi untuk menghapus permanen notifikasi dengan SweetAlert
function deletePermanent(id) {
    Swal.fire({
        title: 'Hapus Permanen?',
        html: `Apakah Anda yakin ingin menghapus <strong>PERMANEN</strong> notifikasi ini?<br><br>Tindakan ini tidak dapat dibatalkan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus Permanen!',
        cancelButtonText: 'Batal',
        width: '340px',
        padding: '0.8rem',
        customClass: {
            popup: 'small-swal-popup',
            title: 'small-swal-title',
            htmlContainer: 'small-swal-content',
            confirmButton: 'small-swal-confirm',
            cancelButton: 'small-swal-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Kirim request delete permanent via AJAX
            const formData = getCsrfFormData();
            
            fetch(`<?= base_url('admin/notification-management/delete-permanent/') ?>${id}`, {
                method: 'POST',
                headers: getDefaultHeaders(),
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Update CSRF token
                if (data.csrf_token && data.csrf_name) {
                    updateCsrfToken(data.csrf_token, data.csrf_name);
                }
                
                if (data.status === 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Notifikasi berhasil dihapus permanen'
                    }).then(() => {
                        // Reload halaman setelah notifikasi muncul
                        window.location.reload();
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Gagal menghapus permanen notifikasi'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.fire({
                    icon: 'error',
                    title: 'Terjadi kesalahan. Silakan coba lagi.'
                });
            });
        }
    });
}

// Fungsi untuk menghapus permanen notifikasi yang dipilih
function deleteSelectedNotifications() {
    const checkboxes = document.querySelectorAll('.notification-checkbox:checked');
    if (checkboxes.length === 0) {
        Toast.fire({
            icon: 'warning',
            title: 'Pilih minimal satu notifikasi'
        });
        return;
    }
    
    const ids = Array.from(checkboxes).map(cb => cb.value);
    
    Swal.fire({
        title: 'Hapus Permanen Terpilih?',
        html: `Apakah Anda yakin ingin menghapus <strong>PERMANEN ${checkboxes.length} notifikasi</strong> yang dipilih?<br><br>Tindakan ini tidak dapat dibatalkan!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus Permanen!',
        cancelButtonText: 'Batal',
        width: '340px',
        padding: '0.8rem',
        customClass: {
            popup: 'small-swal-popup',
            title: 'small-swal-title',
            htmlContainer: 'small-swal-content',
            confirmButton: 'small-swal-confirm',
            cancelButton: 'small-swal-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // PERBAIKAN: Gunakan application/x-www-form-urlencoded bukan FormData
            const formData = new URLSearchParams();
            formData.append(currentCsrfName, currentCsrfToken);
            formData.append('ids', JSON.stringify(ids));
            
            fetch('<?= base_url('admin/notification-management/delete-selected-permanent') ?>', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Notifikasi berhasil dihapus permanen'
                    }).then(() => {
                        // Reload halaman setelah notifikasi muncul
                        window.location.reload();
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Gagal menghapus permanen notifikasi'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Toast.fire({
                    icon: 'error',
                    title: 'Terjadi kesalahan. Silakan coba lagi.'
                });
            });
        }
    });
}

// Close modal when clicking outside
document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeMessageModal();
    }
});

// Handle checkbox selection
document.addEventListener('DOMContentLoaded', function() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const notificationCheckboxes = document.querySelectorAll('.notification-checkbox');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    
    // Select all functionality
    selectAllCheckbox.addEventListener('change', function() {
        notificationCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateDeleteSelectedButton();
    });
    
    // Individual checkbox change
    notificationCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateDeleteSelectedButton);
    });
    
    // Update delete selected button state
    function updateDeleteSelectedButton() {
        const checkedBoxes = document.querySelectorAll('.notification-checkbox:checked');
        deleteSelectedBtn.disabled = checkedBoxes.length === 0;
    }
    
    // Delete selected button click
    deleteSelectedBtn.addEventListener('click', deleteSelectedNotifications);
});
</script>

<style>
/* Style untuk SweetAlert yang lebih kecil */
.swal2-popup {
    font-size: 0.8rem !important;
    border-radius: 12px !important;
}

.swal2-title {
    font-size: 1rem !important;
    margin-bottom: 0.4rem !important;
    padding: 0 !important;
}

.swal2-html-container {
    font-size: 0.75rem !important;
    line-height: 1.3 !important;
    padding: 0 !important;
}

.swal2-confirm,
.swal2-cancel {
    font-size: 0.75rem !important;
    padding: 0.4rem 1rem !important;
    margin: 0 0.2rem !important;
}

/* Style untuk notifikasi toast yang lebih kecil */
.swal2-toast {
    font-size: 0.8rem !important;
}

.swal2-toast .swal2-title {
    font-size: 1rem !important;
    margin-bottom: 0.2rem !important;
}

.swal2-toast .swal2-html-container {
    font-size: 0.8rem !important;
    margin-top: 0.2rem !important;
}

/* Style untuk dialog konfirmasi hapus */
.small-swal-popup {
    font-size: 0.8rem !important;
    border-radius: 12px !important;
}

.small-swal-title {
    font-size: 1rem !important;
    margin-bottom: 0.4rem !important;
    padding: 0 !important;
}

.small-swal-content {
    font-size: 0.75rem !important;
    line-height: 1.3 !important;
    padding: 0 !important;
}

.small-swal-confirm,
.small-swal-cancel {
    font-size: 0.75rem !important;
    padding: 0.4rem 1rem !important;
    margin: 0 0.2rem !important;
}
</style>

<?= $this->include('admin/layouts/footer'); ?>