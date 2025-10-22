<?= $this->include('admin/layouts/header') ?>
<?= $this->include('admin/layouts/sidebar') ?>

<!-- CSRF Meta Tags -->
<meta name="csrf-token" content="<?= csrf_hash() ?>">
<meta name="csrf-name" content="<?= csrf_token() ?>">

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
                <a href="<?= base_url('admin/notification-management/user-state') ?>" 
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
                        <option value="read" <?= ($filter ?? '') == 'read' ? 'selected' : '' ?>>Notifikasi Terbaca</option>
                        <option value="unread" <?= ($filter ?? '') == 'unread' ? 'selected' : '' ?>>Notifikasi Belum Dibaca</option>
                        <option value="system" <?= ($filter ?? '') == 'system' ? 'selected' : '' ?>>Tipe System</option>
                        <option value="announcement" <?= ($filter ?? '') == 'announcement' ? 'selected' : '' ?>>Tipe Announcement</option>
                        <option value="vendor" <?= ($filter ?? '') == 'vendor' ? 'selected' : '' ?>>User Vendor</option>
                        <option value="seo" <?= ($filter ?? '') == 'seo' ? 'selected' : '' ?>>User Tim SEO</option>
                    </select>
                </div>
                <div class="md:col-span-5">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                    <input type="text" name="search" value="<?= esc($search ?? '') ?>" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Cari nama user, judul, pesan, atau tipe...">
                </div>
                <div class="md:col-span-3 flex items-end space-x-2">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                    <a href="<?= base_url('admin/notification-management') ?>" class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
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
                    Daftar Notifikasi (<?= $totalNotifications ?? 0 ?>)
                </h3>
                <div class="flex space-x-2">
                    <?php if (!empty($notifications)): ?>
                        <button id="deleteSelectedBtn" 
                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                disabled>
                            <i class="fas fa-trash mr-2"></i>
                            Hapus yang Dipilih
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-blue-500">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider w-12">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Title</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider w-32">Message</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($notifications)): ?>
                            <tr>
                                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-bell-slash text-3xl mb-2 text-gray-300"></i>
                                    <p class="text-lg">Tidak ada notifikasi</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <input type="checkbox" class="notification-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500" 
                                            value="<?= $notif['id'] ?>">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php
                                            // Prioritas tampilkan berdasarkan ID profil yang ada
                                            if (!empty($notif['admin_name'])) {
                                                echo esc($notif['admin_name']) . ' <span class="text-purple-600 text-xs">(Admin)</span>';
                                            } elseif (!empty($notif['seo_name'])) {
                                                echo esc($notif['seo_name']) . ' <span class="text-blue-600 text-xs">(SEO)</span>';
                                            } elseif (!empty($notif['vendor_name'])) {
                                                echo esc($notif['vendor_name']) . ' <span class="text-green-600 text-xs">(Vendor)</span>';
                                            } 
                                            // Jika tidak ada nama profil, cek berdasarkan grup user
                                            elseif (!empty($notif['user_group'])) {
                                                if ($notif['user_group'] === 'admin') {
                                                    // Coba ambil dari admin_profiles
                                                    $adminProfile = $adminProfileModel->getByUserId($notif['user_id']);
                                                    if ($adminProfile) {
                                                        echo esc($adminProfile['name']) . ' <span class="text-purple-600 text-xs">(Admin)</span>';
                                                    } else {
                                                        echo esc($notif['username'] ?? 'Unknown User') . ' <span class="text-purple-600 text-xs">(Admin)</span>';
                                                    }
                                                } elseif ($notif['user_group'] === 'seoteam') {
                                                    // Coba ambil dari seo_profiles
                                                    $seoProfile = $seoProfilesModel->getByUserId($notif['user_id']);
                                                    if ($seoProfile) {
                                                        echo esc($seoProfile['name']) . ' <span class="text-blue-600 text-xs">(SEO)</span>';
                                                    } else {
                                                        echo esc($notif['username'] ?? 'Unknown User') . ' <span class="text-blue-600 text-xs">(SEO)</span>';
                                                    }
                                                } elseif ($notif['user_group'] === 'vendor') {
                                                    // Coba ambil dari vendor_profiles
                                                    $vendorProfile = $vendorProfilesModel->getByUserId($notif['user_id']);
                                                    if ($vendorProfile) {
                                                        echo esc($vendorProfile['business_name']) . ' <span class="text-green-600 text-xs">(Vendor)</span>';
                                                    } else {
                                                        echo esc($notif['username'] ?? 'Unknown User') . ' <span class="text-green-600 text-xs">(Vendor)</span>';
                                                    }
                                                } else {
                                                    echo esc($notif['username'] ?? 'Unknown User');
                                                }
                                            }
                                            // Jika tidak ada informasi grup, tampilkan username saja
                                            else {
                                                echo esc($notif['username'] ?? 'Unknown User');
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
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
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm font-medium text-gray-900 max-w-xs">
                                            <?= esc($notif['title']) ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="text-sm text-gray-500 truncate" style="max-width: 150px;" title="<?= esc($notif['message']) ?>">
                                            <?= (strlen(esc($notif['message'])) > 30) ? substr(esc($notif['message']), 0, 30) . '...' : esc($notif['message']) ?>
                                        </div>
                                        <button onclick="viewMessage('<?= esc($notif['id']) ?>', '<?= rawurlencode($notif['title']) ?>', '<?= rawurlencode($notif['message']) ?>')" 
                                                class="text-blue-600 hover:text-blue-800 text-xs mt-1">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                        <?= !empty($notif['created_at']) ? date('d M Y H:i', strtotime($notif['created_at'])) : '-' ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                        <div class="flex space-x-2 justify-center">
                                            <button onclick="openEditModal(<?= $notif['id'] ?>)" 
                                            class="inline-flex items-center px-2.5 py-1.5 bg-blue-100 text-blue-700 rounded-md hover:bg-blue-200 transition-colors"
                                            title="Edit Notifikasi">
                                                <i class="fas fa-edit mr-1 text-xs"></i>
                                                <span class="text-xs">Edit</span>
                                            </button>
                                            <button onclick="deleteNotification(<?= $notif['id'] ?>)" 
                                                    class="inline-flex items-center px-2.5 py-1.5 bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors"
                                                    title="Hapus Notifikasi">
                                                <i class="fas fa-trash mr-1 text-xs"></i>
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
    
    // Load content dari endpoint create
    fetch('<?= base_url('admin/notification-management/create') ?>', {
        headers: getDefaultHeaders()
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            document.getElementById('modalContent').innerHTML = data.html;
            // Update CSRF token
            if (data.csrf_token && data.csrf_name) {
                updateCsrfToken(data.csrf_token, data.csrf_name);
            }
        } else {
            throw new Error(data.message);
        }
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
    
    const editUrl = '<?= base_url("admin/notification-management/edit") ?>/' + id;
    
    console.log('Using URL:', editUrl);
    
    fetch(editUrl, {
        method: 'GET',
        headers: getDefaultHeaders(),
        credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Successfully loaded content');
        if (data.status === 'success') {
            document.getElementById('modalContent').innerHTML = data.html;
            // Update CSRF token
            if (data.csrf_token && data.csrf_name) {
                updateCsrfToken(data.csrf_token, data.csrf_name);
            }
        } else {
            throw new Error(data.message);
        }
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

// Fungsi untuk menampilkan modal detail pesan
// Fungsi untuk menampilkan modal detail pesan
function viewMessage(id, title, message) {
    // Menggunakan decodeURIComponent untuk memastikan karakter khusus ditangani dengan benar
    const decodedTitle = decodeURIComponent(title);
    const decodedMessage = decodeURIComponent(message);
    
    document.getElementById('messageTitle').textContent = decodedTitle;
    document.getElementById('messageContent').textContent = decodedMessage;
    document.getElementById('messageModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

// Fungsi untuk menghapus notifikasi dengan SweetAlert
function deleteNotification(id) {
    Swal.fire({
        title: 'Hapus Notifikasi?',
        html: `Apakah Anda yakin ingin menghapus notifikasi ini?<br><br>Notifikasi akan dihapus permanen dari sistem.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
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
            // Kirim request delete via AJAX
            const formData = getCsrfFormData();
            
            fetch(`<?= base_url('admin/notification-management/delete/') ?>${id}`, {
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
                        title: data.message || 'Notifikasi berhasil dihapus'
                    }).then(() => {
                        // Reload halaman setelah notifikasi muncul
                        window.location.reload();
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Gagal menghapus notifikasi'
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

// Fungsi untuk menghapus notifikasi yang dipilih
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
        title: 'Hapus Notifikasi Terpilih?',
        html: `Apakah Anda yakin ingin menghapus <strong>${checkboxes.length} notifikasi</strong> yang dipilih?<br><br>Notifikasi akan dihapus permanen dari sistem.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
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
            
            fetch('<?= base_url('admin/notification-management/delete-selected') ?>', {
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
                // Update CSRF token
                if (data.csrf_token && data.csrf_name) {
                    updateCsrfToken(data.csrf_token, data.csrf_name);
                }
                
                if (data.status === 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Notifikasi berhasil dihapus'
                    }).then(() => {
                        // Reload halaman setelah notifikasi muncul
                        window.location.reload();
                    });
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: data.message || 'Gagal menghapus notifikasi'
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
document.getElementById('formModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('messageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMessageModal();
    }
});

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeMessageModal();
    }
});

// Handle form submission via AJAX
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Only handle forms inside modal
    if (!form.closest('#modalContent')) return;
    
    e.preventDefault();
    
    const formData = new FormData(form);
    // Add CSRF token
    formData.append(currentCsrfName, currentCsrfToken);
    
    const submitButton = form.querySelector('button[type="submit"]');
    
    // Show loading state
    if (submitButton) {
        const originalText = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
        submitButton.disabled = true;
    }
    
    fetch(form.action, {
        method: 'POST',
        headers: getDefaultHeaders(),
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        // Update CSRF token
        if (data.csrf_token && data.csrf_name) {
            updateCsrfToken(data.csrf_token, data.csrf_name);
        }
        
        if (data.status === 'success') {
            Toast.fire({
                icon: 'success',
                title: data.message
            }).then(() => {
                closeModal();
                // Redirect ke index setelah notifikasi muncul
                window.location.href = '<?= base_url('admin/notification-management') ?>';
            });
        } else {
            // Show validation errors if any
            if (data.errors) {
                let errorMessages = '';
                for (const field in data.errors) {
                    errorMessages += `${data.errors[field]}\n`;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    text: errorMessages,
                    width: '300px',
                    padding: '0.75rem',
                });
            } else {
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

<?= $this->include('admin/layouts/footer') ?>