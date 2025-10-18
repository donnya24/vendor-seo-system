<?= $this->include('admin/layouts/header') ?>
<?= $this->include('admin/layouts/sidebar') ?>

<div class="content-area">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Kelola Notifikasi</h1>
                <p class="text-gray-600 mt-1">Kelola semua notifikasi sistem</p>
            </div>
            <div class="flex space-x-3 mt-4 sm:mt-0">
                <button onclick="openCreateModal()" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Buat Notifikasi
                </button>
                <a href="<?= base_url('admin/kelola-notifikasi/user-state') ?>" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-user-check mr-2"></i>
                    Notifikasi terhapus
                </a>
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

        <!-- Filter & Search -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <form method="get" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                <div class="md:col-span-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter</label>
                    <select name="filter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Semua Notifikasi</option>
                        <option value="read" <?= $filter == 'read' ? 'selected' : '' ?>>Notifikasi Terbaca</option>
                        <option value="unread" <?= $filter == 'unread' ? 'selected' : '' ?>>Notifikasi Belum Dibaca</option>
                        <option value="system" <?= $filter == 'system' ? 'selected' : '' ?>>Tipe System</option>
                        <option value="announcement" <?= $filter == 'announcement' ? 'selected' : '' ?>>Tipe Announcement</option>
                        <option value="vendor" <?= $filter == 'vendor' ? 'selected' : '' ?>>User Vendor</option>
                        <option value="seo" <?= $filter == 'seo' ? 'selected' : '' ?>>User Tim SEO</option>
                    </select>
                </div>
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?= esc($search) ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Cari nama user, judul, pesan, atau tipe...">
                </div>
                <div class="md:col-span-3 flex items-end space-x-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="<?= base_url('admin/kelola-notifikasi') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                        <i class="fas fa-refresh mr-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg mr-4">
                        <i class="fas fa-bell text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Notifikasi</p>
                        <p class="text-2xl font-bold text-blue-900"><?= number_format($stats['total_notifications'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 rounded-lg mr-4">
                        <i class="fas fa-eye-slash text-orange-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-orange-600 font-medium">Belum Dibaca</p>
                        <p class="text-2xl font-bold text-orange-900"><?= number_format($stats['unread_count'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg mr-4">
                        <i class="fas fa-users text-purple-600 text-xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-purple-600 font-medium">Notifikasi terhapus</p>
                        <p class="text-2xl font-bold text-purple-900"><?= number_format($stats['user_state_count'] ?? 0) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-blue-600 border-b border-blue-700 flex justify-between items-center">
                <h3 class="text-lg font-medium text-white">
                    Daftar Notifikasi (<?= $totalNotifications ?>)
                </h3>
                <?php if (!empty($notifications)): ?>
                    <form action="<?= base_url('admin/kelola-notifikasi/delete-all') ?>" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA notifikasi? Tindakan ini tidak dapat dibatalkan!')">
                        <?= csrf_field() ?>
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Semua
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-500">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider w-48">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-bell-slash text-3xl mb-2 text-gray-300"></i>
                                    <p class="text-lg">Tidak ada notifikasi</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php
                                            // Menampilkan nama user
                                            $userName = 'Unknown User';
                                            
                                            if (!empty($notification['user_display'])) {
                                                $userName = $notification['user_display'];
                                            } elseif (!empty($notification['user_name'])) {
                                                $userName = $notification['user_name'];
                                            }
                                            
                                            echo esc($userName);
                                            
                                            // Menambahkan label berdasarkan user_type
                                            if (!empty($notification['user_type'])) {
                                                if ($notification['user_type'] === 'admin') {
                                                    echo ' <span class="text-purple-600 text-xs">(Admin)</span>';
                                                } elseif ($notification['user_type'] === 'seo') {
                                                    echo ' <span class="text-blue-600 text-xs">(SEO)</span>';
                                                } elseif ($notification['user_type'] === 'vendor') {
                                                    echo ' <span class="text-green-600 text-xs">(Vendor)</span>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php 
                                        $type = $notification['type'] ?? 'general';
                                        $colorClass = '';
                                        
                                        // Menentukan warna berdasarkan tipe
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
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900 max-w-xs">
                                            <?= esc($notification['title']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-500 truncate" title="<?= esc($notification['message']) ?>">
                                            <?= (strlen(esc($notification['message'])) > 50) ? substr(esc($notification['message']), 0, 50) . '...' : esc($notification['message']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($notification['is_read']): ?>
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?= date('d M Y H:i', strtotime($notification['created_at'])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex space-x-2 justify-end">
                                            <button onclick="openEditModal(<?= $notification['id'] ?>)" 
                                               class="inline-flex items-center px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors"
                                               title="Edit Notifikasi">
                                                <i class="fas fa-edit mr-1 text-xs"></i>
                                                <span class="text-xs">Edit</span>
                                            </button>
                                            <form action="<?= base_url('admin/kelola-notifikasi/delete/' . $notification['id']) ?>" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus notifikasi ini?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" 
                                                        class="inline-flex items-center px-2.5 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
                                                        title="Hapus Notifikasi">
                                                    <i class="fas fa-trash mr-1 text-xs"></i>
                                                    <span class="text-xs">Hapus</span>
                                                </button>
                                            </form>
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
</div>

<!-- Modal Container -->
<div id="formModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
            <div class="bg-blue-600 px-4 py-3 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="modal-title">Buat Notifikasi Baru</h3>
                <button type="button" onclick="closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// --- PERBAIKAN NOTIFIKASI ---
// Buat notifikasi "toast" yang lebih kecil dan di pojok kanan atas
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end', // Pojok kanan atas
    showConfirmButton: false,
    timer: 3000, // Hilang otomatis setelah 3 detik
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer)
        toast.addEventListener('mouseleave', Swal.resumeTimer)
    }
});

// Fungsi untuk membuka modal create
function openCreateModal() {
    // Show loading spinner
    document.getElementById('modalContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
    `;
    
    // Update modal title
    document.getElementById('modal-title').textContent = 'Buat Notifikasi Baru';
    
    // Show modal
    document.getElementById('formModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // --- PERBAIKAN RUTE ---
    // Load content dari endpoint create dengan URL yang benar
    fetch('<?= base_url('admin/kelola-notifikasi/create') ?>', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(html => {
        document.getElementById('modalContent').innerHTML = html;
    })
    .catch(error => {
        console.error('Error loading modal content:', error);
        document.getElementById('modalContent').innerHTML = `
            <div class="p-4 text-red-700 bg-red-100 border border-red-400 rounded">
                <strong>Error:</strong> Gagal memuat form. Silakan coba lagi.<br>
                <small>${error.message}</small>
            </div>
        `;
    });
}

// Fungsi untuk membuka modal edit
function openEditModal(id) {
    console.log('Opening edit modal for ID:', id);
    
    // Show loading spinner
    document.getElementById('modalContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
    `;
    
    // Update modal title
    document.getElementById('modal-title').textContent = 'Edit Notifikasi';
    
    // Show modal
    document.getElementById('formModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Gunakan URL yang sesuai dengan rute yang didefinisikan
    const editUrl = '<?= base_url("admin/kelola-notifikasi/edit") ?>/' + id;
    
    console.log('Using URL:', editUrl);
    
    fetch(editUrl, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.text();
    })
    .then(html => {
        console.log('Successfully loaded content');
        document.getElementById('modalContent').innerHTML = html;
    })
    .catch(error => {
        console.error('Error loading modal content:', error);
        document.getElementById('modalContent').innerHTML = `
            <div class="p-4 text-red-700 bg-red-100 border border-red-400 rounded">
                <strong>Error:</strong> Gagal memuat form. Silakan coba lagi.<br>
                <small>${error.message}</small>
            </div>
        `;
    });
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Close modal when clicking outside
document.getElementById('formModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Handle form submission via AJAX
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Only handle forms inside modal
    if (!form.closest('#modalContent')) return;
    
    e.preventDefault();
    
    const formData = new FormData(form);
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Show loading state
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        submitButton.disabled = true;
    }
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            // --- PERBAIKAN NOTIFIKASI ---
            // Gunakan Toast yang sudah didefinisikan
            Toast.fire({
                icon: 'success',
                title: data.message
            }).then(() => {
                closeModal();
                // Redirect ke index setelah notifikasi muncul
                window.location.href = '<?= base_url('admin/kelola-notifikasi') ?>';
            });
        } else {
            // Show validation errors if any
            if (data.errors) {
                let errorMessages = '';
                for (const field in data.errors) {
                    errorMessages += `${data.errors[field]}\n`;
                }
                // --- PERBAIKAN NOTIFIKASI ---
                // Gunakan Swal untuk error yang butuh konfirmasi (misalnya list error)
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: errorMessages
                });
            } else {
                // --- PERBAIKAN NOTIFIKASI ---
                // Gunakan Toast untuk error pesan tunggal
                Toast.fire({
                    icon: 'error',
                    title: data.message
                });
            }
            
            // Reset button
            if (submitButton) {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        // --- PERBAIKAN NOTIFIKASI ---
        Toast.fire({
            icon: 'error',
            title: 'Terjadi kesalahan jaringan. Silakan coba lagi.'
        });
        
        // Reset button
        if (submitButton) {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
    });
});

// Initialize tooltips and other interactive elements
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips if needed
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    if (typeof bootstrap !== 'undefined') {
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
});
</script>
<?= $this->include('admin/layouts/footer') ?>