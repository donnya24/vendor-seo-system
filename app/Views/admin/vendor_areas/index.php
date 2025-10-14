<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="p-4 md:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Area Vendor</h1>
        <button onclick="openModal('<?= site_url('admin/areas/create') ?>')" class="mt-4 sm:mt-0 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Area Vendor
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-blue-600 text-white">
            <h2 class="text-lg font-semibold">Daftar Area Vendor</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="vendorAreasTable">
                        <?php $i = 1; ?>
                        <?php foreach ($vendorAreas as $item): ?>
                            <tr id="vendor-<?= $item['vendor']['id'] ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $i++ ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($item['vendor']['business_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $item['vendor']['status'] == 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                        <?= ucfirst($item['vendor']['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?php if (!empty($item['areas'])): ?>
                                        <div class="flex flex-wrap gap-1" id="areas-<?= $item['vendor']['id'] ?>">
                                            <?php foreach ($item['areas'] as $area): ?>
                                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 area-item" 
                                                      data-vendor-id="<?= $item['vendor']['id'] ?>" 
                                                      data-area-id="<?= $area['id'] ?>"
                                                      title="<?= esc($area['path'] ?? $area['name']) ?>">
                                                    <?= esc($area['name']) ?>
                                                    <button type="button" 
                                                            onclick="deleteArea(<?= $item['vendor']['id'] ?>, <?= $area['id'] ?>, '<?= esc($area['name']) ?>')"
                                                            class="ml-1 text-red-600 hover:text-red-800 text-xs">
                                                        ×
                                                    </button>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-500">Belum ada area</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openEditModal(<?= $item['vendor']['id'] ?>)" class="text-blue-600 hover:text-blue-900 mr-3">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="clearAllAreas(<?= $item['vendor']['id'] ?>, '<?= esc($item['vendor']['business_name']) ?>')" 
                                            class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i> Hapus Semua
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-blue-600 px-4 py-3 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="modal-title">Form Area Vendor</h3>
                <button type="button" onclick="closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 max-h-[80vh] overflow-y-auto" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables for areas data
window.__AREAS_PRE = [];
window.__AREAS_SEARCH_URL = '<?= site_url('admin/areas/search') ?>';

// PERBAIKAN: Fungsi global untuk SweetAlert dengan ukuran kecil
function showMini(status, message, redirect = null) {
    const config = {
        toast: true, // Menggunakan toast instead of modal
        position: 'top-end',
        icon: status,
        title: status === 'success' ? 'Sukses' : (status === 'error' ? 'Error' : 'Info'),
        text: message,
        showConfirmButton: status !== 'success',
        timer: status === 'success' ? 2000 : 3000,
        timerProgressBar: true,
        width: '300px',
        padding: '0.5rem',
        customClass: {
            popup: 'small-toast',
            title: 'small-toast-title',
            htmlContainer: 'small-toast-text',
            confirmButton: 'small-toast-button'
        }
    };

    Swal.fire(config).then((result) => {
        if (redirect && (status === 'success' || result.isConfirmed)) {
            window.location.href = redirect;
        }
    });
}

// PERBAIKAN: Fungsi untuk menghapus area individual dengan notifikasi kecil
function deleteArea(vendorId, areaId, areaName) {
    // PERBAIKAN: Gunakan konfirmasi kecil
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'question',
        title: 'Hapus Area?',
        text: `Hapus "${areaName}" dari vendor?`,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        width: '300px',
        padding: '0.5rem'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            const areaElement = document.querySelector(`.area-item[data-vendor-id="${vendorId}"][data-area-id="${areaId}"]`);
            if (areaElement) {
                areaElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            }

            // AJAX request to delete area
            fetch('<?= site_url('admin/areas/delete') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `vendor_id=${vendorId}&area_id=${areaId}&<?= csrf_token() ?>=<?= csrf_hash() ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove area from UI
                    if (areaElement) {
                        areaElement.remove();
                    }
                    
                    // Check if no areas left
                    const areasContainer = document.getElementById(`areas-${vendorId}`);
                    if (areasContainer) {
                        const remainingAreas = areasContainer.querySelectorAll('.area-item');
                        
                        if (remainingAreas.length === 0) {
                            // Jika tidak ada area lagi, update seluruh cell
                            const areaCell = areasContainer.closest('td');
                            if (areaCell) {
                                areaCell.innerHTML = '<span class="text-gray-500">Belum ada area</span>';
                            }
                        }
                    }
                    
                    // PERBAIKAN: Tampilkan notifikasi kecil
                    showMini('success', data.message);
                } else {
                    showMini('error', data.message);
                    // Reset area element if error
                    if (areaElement) {
                        areaElement.innerHTML = `${areaName} <button type="button" onclick="deleteArea(${vendorId}, ${areaId}, '${areaName}')" class="ml-1 text-red-600 hover:text-red-800 text-xs">×</button>`;
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMini('error', 'Terjadi kesalahan jaringan');
                // Reset area element if error
                if (areaElement) {
                    areaElement.innerHTML = `${areaName} <button type="button" onclick="deleteArea(${vendorId}, ${areaId}, '${areaName}')" class="ml-1 text-red-600 hover:text-red-800 text-xs">×</button>`;
                }
            });
        }
    });
}

// PERBAIKAN: Fungsi untuk menghapus semua area dengan notifikasi kecil
function clearAllAreas(vendorId, vendorName) {
    // PERBAIKAN: Gunakan konfirmasi kecil
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'question',
        title: 'Hapus Semua Area?',
        text: `Hapus semua area untuk "${vendorName}"?`,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        width: '300px',
        padding: '0.5rem'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            const row = document.getElementById(`vendor-${vendorId}`);
            if (row) {
                const areasCell = row.querySelector('td:nth-child(4)');
                if (areasCell) {
                    areasCell.innerHTML = '<div class="flex justify-center"><i class="fas fa-spinner fa-spin text-blue-600"></i></div>';
                }
            }

            // AJAX request to clear all areas
            fetch('<?= site_url('admin/areas/clear-all/') ?>' + vendorId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: `<?= csrf_token() ?>=<?= csrf_hash() ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update UI
                    const areasCell = document.querySelector(`#vendor-${vendorId} td:nth-child(4)`);
                    if (areasCell) {
                        areasCell.innerHTML = '<span class="text-gray-500">Belum ada area</span>';
                    }
                    // PERBAIKAN: Tampilkan notifikasi kecil
                    showMini('success', data.message);
                } else {
                    showMini('error', data.message);
                    // Reload on error to sync state
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMini('error', 'Terjadi kesalahan jaringan');
                window.location.reload();
            });
        }
    });
}

function openEditModal(vendorId) {
    console.log('Opening edit modal for vendor:', vendorId);
    
    // Show loading spinner
    document.getElementById('modalContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
    `;
    
    // Show modal
    document.getElementById('formModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // PERBAIKAN: Gunakan parameter modal=1 seperti di Vendoruser
    fetch(`<?= site_url('admin/areas/edit') ?>/${vendorId}?modal=1`, {
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
        
        // Initialize Alpine.js after content is loaded
        setTimeout(() => {
            if (typeof Alpine !== 'undefined') {
                Alpine.initTree(document.getElementById('modalContent'));
            }
        }, 100);
    })
    .catch(error => {
        console.error('Error loading modal content:', error);
        document.getElementById('modalContent').innerHTML = `
            <div class="alert alert-danger p-4 text-red-700 bg-red-100 border border-red-400 rounded">
                <strong>Error:</strong> Gagal memuat form. Silakan coba lagi.<br>
                <small>${error.message}</small>
            </div>
        `;
    });
}

function openModal(url) {
    console.log('Opening modal with URL:', url);
    
    // Show loading spinner
    document.getElementById('modalContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
        </div>
    `;
    
    // Show modal
    document.getElementById('formModal').classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
    
    // Load content
    fetch(url, {
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
        
        // Initialize Alpine.js after content is loaded
        setTimeout(() => {
            if (typeof Alpine !== 'undefined') {
                Alpine.initTree(document.getElementById('modalContent'));
            }
        }, 100);
    })
    .catch(error => {
        console.error('Error loading modal content:', error);
        document.getElementById('modalContent').innerHTML = `
            <div class="alert alert-danger p-4 text-red-700 bg-red-100 border border-red-400 rounded">
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

// Handle form submission via AJAX
document.addEventListener('submit', function(e) {
    const form = e.target;
    
    // Only handle forms inside modal
    if (!form.closest('#modalContent')) return;
    
    e.preventDefault();
    
    // Check if form has @submit.prevent (Alpine.js)
    if (form.hasAttribute('@submit.prevent')) return;
    
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
        if (submitButton) {
            submitButton.innerHTML = '<i class="fas fa-check mr-2"></i> Berhasil';
        }
        
        if (data.status === 'success') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                closeModal();
                // Reload page to reflect changes
                window.location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message
            });
            
            // Reset button
            if (submitButton) {
                submitButton.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan';
                submitButton.disabled = false;
            }
        }
    })
    .catch(error => {
        console.error('Form submission error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Terjadi kesalahan jaringan. Silakan coba lagi.'
        });
        
        // Reset button
        if (submitButton) {
            submitButton.innerHTML = '<i class="fas fa-save mr-2"></i> Simpan';
            submitButton.disabled = false;
        }
    });
});

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});

// Global function for areas popup
function closeAreasPopup() {
    closeModal();
}

// Global variables for areas data
window.__AREAS_PRE = [];
window.__AREAS_SEARCH_URL = '<?= site_url('admin/areas/search') ?>';
window.__VENDORS = []; // Akan diisi saat modal dibuka
</script>

<!-- PERBAIKAN: Tambahkan CSS untuk notifikasi kecil -->
<style>
.small-toast {
    font-size: 0.875rem !important;
    width: 300px !important;
    padding: 0.5rem !important;
}

.small-toast-title {
    font-size: 0.875rem !important;
    font-weight: 600 !important;
}

.small-toast-text {
    font-size: 0.75rem !important;
}

.small-toast-button {
    font-size: 0.75rem !important;
    padding: 0.25rem 0.5rem !important;
}
</style>

<?= $this->include('admin/layouts/footer'); ?>