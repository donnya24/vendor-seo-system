<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap" class="flex-1 flex flex-col overflow-hidden relative z-10">
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="px-4 sm:px-6 py-3 flex items-center justify-between">
      <div>
        <h1 class="text-lg font-bold text-gray-800">Leads Management</h1>
        <p class="text-xs text-gray-500 mt-1">Kelola semua leads dari vendor</p>
      </div>
      <!-- Tombol Tambah -->
      <button onclick="openCreateModal()"
              class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold flex items-center gap-2">
        <i class="fa fa-plus"></i> Tambah Lead
      </button>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto bg-gray-50 pt-4">
    <div class="px-4 sm:px-6 py-6">
      
      <!-- HEADER DENGAN FILTER DAN DELETE ALL -->
      <div class="mb-5 flex flex-col sm:flex-row gap-4 items-center justify-between">
        <!-- FILTER VENDOR -->
        <form method="get" class="flex items-center gap-3">
          <div class="flex items-center gap-2">
            <label for="vendor_id" class="font-medium text-gray-700">Filter Vendor:</label>
            <select id="vendor_id" name="vendor_id" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
              <option value="">-- Semua Vendor --</option>
              <?php foreach($vendors as $v): ?>
                <option value="<?= esc($v['id']) ?>" <?= ($vendor_id == $v['id']) ? 'selected' : '' ?>>
                  <?= esc($v['business_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow">
            Filter
          </button>
        </form>
        
        <!-- BUTTONS GROUP -->
        <div class="flex items-center gap-2">
          <!-- DELETE ALL BUTTON -->
          <?php if (!empty($leads)): ?>
            <form method="post" action="<?= site_url('admin/leads/delete-all' . (!empty($vendor_id) ? '?vendor_id=' . $vendor_id : '')) ?>" 
                  id="deleteAllForm" class="inline">
              <?= csrf_field() ?>
              <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
                <i class="fas fa-trash-alt"></i>
                Hapus Semua Leads
              </button>
            </form>
          <?php endif; ?>
          
          <!-- EXPORT CSV BUTTON -->
          <a href="<?= site_url('admin/leads/export' . (!empty($vendor_id) ? '?vendor_id=' . $vendor_id : '')) ?>" 
            class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
            <i class="fas fa-file-csv"></i> Export CSV
          </a>
        </div>
      </div>

      <!-- INFO STATS -->
      <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
        <div class="flex flex-wrap gap-4 text-sm text-blue-800">
          <div class="flex items-center gap-1">
            <i class="fas fa-building"></i>
            <span>Total Vendor: <?= count($vendors) ?></span>
          </div>
          <div class="flex items-center gap-1">
            <i class="fas fa-list-alt"></i>
            <span>Total Leads: <?= count($leads) ?></span>
          </div>
          <?php if (!empty($vendor_id)): ?>
            <div class="flex items-center gap-1">
              <i class="fas fa-filter"></i>
              <span>Filter aktif: Vendor tertentu</span>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- TABLE -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
          <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
                  <tr>
                      <th class="px-4 py-3 text-left text-xs font-semibold">NO</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">VENDOR</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">MASUK</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">CLOSING</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">PERIODE TANGGAL</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">UPDATE</th>
                      <th class="px-4 py-3 text-left text-xs font-semibold">AKSI</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                  <?php if (!empty($leads)): ?>
                      <?php $no = 1; foreach($leads as $lead): ?>
                          <tr>
                              <td class="px-4 py-2"><?= $no++ ?></td>
                              <td class="px-4 py-2"><?= esc($lead['vendor_name']) ?></td>
                              <td class="px-4 py-2"><?= esc($lead['jumlah_leads_masuk']) ?></td>
                              <td class="px-4 py-2"><?= esc($lead['jumlah_leads_closing']) ?></td>
                              <td class="px-4 py-2">
                                  <?php 
                                      // Format periode tanggal
                                      if (!empty($lead['tanggal_mulai']) && !empty($lead['tanggal_selesai'])) {
                                          echo date('Y-m-d', strtotime($lead['tanggal_mulai'])) . ' s/d ' . date('Y-m-d', strtotime($lead['tanggal_selesai']));
                                      } elseif (!empty($lead['tanggal_mulai'])) {
                                          echo date('Y-m-d', strtotime($lead['tanggal_mulai'])) . ' s/d sekarang';
                                      } elseif (!empty($lead['tanggal_selesai'])) {
                                          echo 'sampai ' . date('Y-m-d', strtotime($lead['tanggal_selesai']));
                                      } else {
                                          echo '<span class="text-gray-400">-</span>';
                                      }
                                  ?>
                              </td>
                              <td class="px-4 py-2"><?= esc($lead['updated_at']) ?></td>
                              <td class="px-4 py-2">
                                  <div class="action-buttons">
                                      <!-- Tombol Edit -->
                                      <button type="button" 
                                          class="action-btn bg-blue-600 hover:bg-blue-700 text-white"
                                          onclick="openEditModal(<?= $lead['id'] ?>)"
                                          title="Edit Lead">
                                          <i class="fa-regular fa-pen-to-square text-[10px]"></i>
                                          <span>Edit</span>
                                      </button>

                                      <!-- Tombol Hapus -->
                                      <button type="button" 
                                          class="action-btn bg-rose-600 hover:bg-rose-700 text-white"
                                          onclick="openDeleteModal(<?= $lead['id'] ?>, '<?= esc($lead['vendor_name']) ?>')"
                                          title="Hapus Lead">
                                          <i class="fa-regular fa-trash-can text-[10px]"></i>
                                          <span>Hapus</span>
                                      </button>
                                  </div>
                              </td>
                          </tr>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <tr>
                          <td colspan="7" class="p-4 text-center text-gray-500">
                              <div class="py-8">
                                  <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                                  <p class="text-lg">Belum ada data leads.</p>
                                  <?php if (!empty($vendor_id)): ?>
                                      <p class="text-sm text-gray-500 mt-1">Vendor ini belum memiliki data leads.</p>
                                  <?php endif; ?>
                              </div>
                          </td>
                      </tr>
                  <?php endif; ?>
              </tbody>
          </table>
      </div>
    </div>
  </main>
</div>

<!-- BACKDROP BLUR -->
<div id="backdropBlur" class="fixed inset-0 bg-black/30 backdrop-blur-sm z-[9998] opacity-0 invisible transition-all duration-300"></div>

<!-- MODAL CREATE LEAD -->
<div id="createModal" class="modal-overlay">
  <div class="modal-container">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-blue-600">
      <h2 class="text-lg font-semibold text-white">Tambah Lead Baru</h2>
      <button onclick="closeCreateModal()" class="text-white hover:text-gray-200">
        <i class="fa fa-times text-lg"></i>
      </button>
    </div>

    <!-- Form -->
    <form action="<?= site_url('admin/leads/store') ?>" method="post" id="createLeadForm" class="p-5 space-y-4">
      <?= csrf_field() ?>

      <!-- Vendor -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
        <select name="vendor_id" required
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
          <option value="">-- Pilih Vendor --</option>
          <?php foreach ($vendors as $v): ?>
            <option value="<?= esc($v['id']) ?>"><?= esc($v['business_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Jumlah Leads Masuk -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Leads Masuk *</label>
        <input type="number" name="jumlah_leads_masuk" required min="0"
               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Jumlah Leads Closing -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Leads Closing *</label>
        <input type="number" name="jumlah_leads_closing" required min="0"
               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Periode Tanggal -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Tanggal Mulai -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
          <input type="date" name="tanggal_mulai" required
                 class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Tanggal Selesai -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai"
                 class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
        <button type="button" onclick="closeCreateModal()"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
          Batal
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
          Simpan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL EDIT LEAD -->
<div id="editModal" class="modal-overlay">
  <div class="modal-container">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 bg-blue-600">
      <h2 class="text-lg font-semibold text-white">Edit Lead</h2>
      <button onclick="closeEditModal()" class="text-white hover:text-gray-200">
        <i class="fa fa-times text-lg"></i>
      </button>
    </div>

    <!-- Form -->
    <form id="editLeadForm" class="p-5 space-y-4">
      <?= csrf_field() ?>
      <input type="hidden" name="id" id="editLeadId">
      
      <!-- Vendor -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Vendor *</label>
        <select name="vendor_id" required id="editVendorId"
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
          <?php foreach ($vendors as $v): ?>
            <option value="<?= esc($v['id']) ?>"><?= esc($v['business_name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Jumlah Leads Masuk -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Leads Masuk *</label>
        <input type="number" name="jumlah_leads_masuk" required min="0"
               id="editLeadsMasuk"
               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Jumlah Leads Closing -->
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Leads Closing *</label>
        <input type="number" name="jumlah_leads_closing" required min="0"
               id="editLeadsClosing"
               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
      </div>

      <!-- Periode Tanggal -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <!-- Tanggal Mulai -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
          <input type="date" name="tanggal_mulai" required
                id="editTanggalMulai"
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Tanggal Selesai -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai"
                id="editTanggalSelesai"
                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-blue-500">
        </div>
      </div>

      <!-- Footer Buttons -->
      <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
        <button type="button" onclick="closeEditModal()"
                class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
          Batal
        </button>
        <button type="submit"
                class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
          Update
        </button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL DELETE LEAD -->
<div id="deleteModal" class="modal-overlay">
  <div class="modal-container">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200">
      <h2 class="text-lg font-semibold text-gray-800">Hapus Lead</h2>
      <button onclick="closeDeleteModal()" class="text-gray-400 hover:text-gray-600">
        <i class="fa fa-times text-lg"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="p-5">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600">
          <i class="fa-regular fa-trash-can"></i>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Apakah anda yakin ingin menghapus lead "<span id="deleteLeadName"></span>"?</h3>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-5 py-3 border-t border-gray-200 flex justify-end gap-2">
      <button type="button" onclick="closeDeleteModal()"
              class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">
        Batal
      </button>
      <button type="button" onclick="confirmDelete()"
              class="px-4 py-2 rounded-lg bg-rose-600 text-white hover:bg-rose-700">
        Hapus
      </button>
    </div>
  </div>
</div>

<style>
/* Tombol aksi dengan teks */
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 6px 10px;
    font-size: 11px;
    font-weight: 500;
    border-radius: 6px;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    border: none;
    outline: none;
}

.action-btn:hover {
    transform: translateY(-1px);
}

.action-btn:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Container tombol aksi */
.action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: center;
    align-items: center;
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-btn {
        justify-content: center;
        font-size: 10px;
        padding: 5px 8px;
    }
}

/* MODAL STYLES - DENGAN BACKGROUND BLUR */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.3);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    overflow: hidden;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal-container {
    background-color: white;
    border-radius: 0.75rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 255, 255, 0.1);
    max-width: 32rem;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    transform: scale(0.9);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin: auto;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}

.modal-overlay.active .modal-container {
    transform: scale(1);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.2);
}

/* Backdrop blur styles */
#backdropBlur {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(0, 0, 0, 0.3);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    z-index: 9998;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    opacity: 0;
    visibility: hidden;
}

#backdropBlur.active {
    opacity: 1;
    visibility: visible;
}

@supports not (backdrop-filter: blur(8px)) {
    #backdropBlur {
        background-color: rgba(0, 0, 0, 0.5);
    }
    
    .modal-container {
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
}

@supports not (display: flex) {
    .modal-overlay {
        display: block;
        text-align: center;
    }
    
    .modal-overlay:before {
        content: '';
        display: inline-block;
        height: 100%;
        vertical-align: middle;
        margin-right: -0.25em;
    }
    
    .modal-container {
        display: inline-block;
        vertical-align: middle;
        text-align: left;
    }
}

body.modal-open {
    overflow: hidden;
}

@media (max-width: 640px) {
    .modal-overlay {
        padding: 0.5rem;
    }
    
    .modal-container {
        max-width: calc(100% - 1rem);
        margin: 0.5rem;
    }
    
    #backdropBlur {
        backdrop-filter: blur(4px);
        -webkit-backdrop-filter: blur(4px);
    }
}

.btn-loading {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}

.btn-loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    margin: auto;
    border: 2px solid transparent;
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.modal-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
    border-radius: 0.75rem;
    pointer-events: none;
}
</style>

<script>
// Data leads untuk modal edit
const leadsData = <?= json_encode($leads) ?>;
let currentDeleteId = null;

// Fungsi untuk membuka modal create
function openCreateModal() {
    showModalWithBackdrop('createModal');
}

// Fungsi untuk menutup modal create
function closeCreateModal() {
    hideModalWithBackdrop('createModal');
}

// Fungsi untuk membuka modal edit
function openEditModal(id) {
    const lead = leadsData.find(l => l.id == id);
    if (!lead) return;
    
    // Isi form dengan data lead
    document.getElementById('editLeadId').value = lead.id;
    document.getElementById('editVendorId').value = lead.vendor_id;
    document.getElementById('editLeadsMasuk').value = lead.jumlah_leads_masuk;
    document.getElementById('editLeadsClosing').value = lead.jumlah_leads_closing;
    document.getElementById('editTanggalMulai').value = lead.tanggal_mulai ? lead.tanggal_mulai.split(' ')[0] : '';
    document.getElementById('editTanggalSelesai').value = lead.tanggal_selesai ? lead.tanggal_selesai.split(' ')[0] : '';
    
    // Set action form
    document.getElementById('editLeadForm').action = '<?= site_url('admin/leads/update') ?>/' + lead.id;
    
    // Tampilkan modal dengan backdrop blur
    showModalWithBackdrop('editModal');
}

// Fungsi untuk menutup modal edit
function closeEditModal() {
    hideModalWithBackdrop('editModal');
}

// Fungsi untuk membuka modal delete
function openDeleteModal(id, name) {
    currentDeleteId = id;
    document.getElementById('deleteLeadName').textContent = name;
    
    // Tampilkan modal dengan backdrop blur
    showModalWithBackdrop('deleteModal');
}

// Fungsi untuk menutup modal delete
function closeDeleteModal() {
    hideModalWithBackdrop('deleteModal');
    currentDeleteId = null;
}

// Fungsi universal untuk menampilkan modal dengan backdrop blur
function showModalWithBackdrop(modalId) {
    const modal = document.getElementById(modalId);
    const backdrop = document.getElementById('backdropBlur');
    
    if (!modal || !backdrop) return;
    
    // Prevent body scroll
    document.body.classList.add('modal-open');
    
    // Tampilkan backdrop blur terlebih dahulu
    requestAnimationFrame(() => {
        backdrop.classList.add('active');
        
        // Tampilkan modal dengan sedikit delay untuk efek yang lebih smooth
        setTimeout(() => {
            modal.classList.add('active');
        }, 50);
    });
}

// Fungsi universal untuk menyembunyikan modal dengan backdrop blur
function hideModalWithBackdrop(modalId, callback) {
    const modal = document.getElementById(modalId);
    const backdrop = document.getElementById('backdropBlur');
    
    if (!modal || !backdrop) return;
    
    // Sembunyikan modal terlebih dahulu
    modal.classList.remove('active');
    
    // Sembunyikan backdrop dengan delay
    setTimeout(() => {
        backdrop.classList.remove('active');
        
        // Restore body scroll dan jalankan callback setelah animasi selesai
        setTimeout(() => {
            document.body.classList.remove('modal-open');
            if (callback && typeof callback === 'function') {
                callback();
            }
        }, 300);
    }, 100);
}

// Fungsi untuk konfirmasi delete dengan urutan yang benar
function confirmDelete() {
    if (!currentDeleteId) return;
    
    // Tambahkan loading state pada button
    const deleteButton = event.target;
    const originalText = deleteButton.innerHTML;
    deleteButton.classList.add('btn-loading');
    deleteButton.disabled = true;
    
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch(`<?= site_url('admin/leads/delete') ?>/${currentDeleteId}`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        // Kembalikan state button
        deleteButton.classList.remove('btn-loading');
        deleteButton.disabled = false;
        deleteButton.innerHTML = originalText;
        
        if (data.success) {
            // Tutup modal terlebih dahulu, lalu tampilkan notifikasi
            hideModalWithBackdrop('deleteModal', () => {
                showNotification('success', 'Berhasil', data.message);
                // Reload halaman setelah notifikasi muncul
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            });
        } else {
            showNotification('error', 'Gagal', data.message);
        }
    })
    .catch(error => {
        // Kembalikan state button
        deleteButton.classList.remove('btn-loading');
        deleteButton.disabled = false;
        deleteButton.innerHTML = originalText;
        
        showNotification('error', 'Error', 'Terjadi kesalahan pada server');
        console.error('Error:', error);
    });
}

// Fungsi untuk menampilkan notifikasi SweetAlert mini
function showNotification(type, title, message) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        width: '280px',
        padding: '0.5rem',
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: type,
        title: title,
        text: message
    });
}

// Handle create form
document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('createLeadForm');
    if (createForm) {
        createForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validasi tanggal
            const tanggalMulai = this.querySelector('input[name="tanggal_mulai"]').value;
            const tanggalSelesai = this.querySelector('input[name="tanggal_selesai"]').value;
            
            if (!tanggalMulai) {
                showNotification('error', 'Validasi Gagal', 'Tanggal mulai wajib diisi');
                return;
            }
            
            if (tanggalSelesai && new Date(tanggalSelesai) < new Date(tanggalMulai)) {
                showNotification('error', 'Validasi Gagal', 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
                return;
            }
            
            // Tambahkan loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.classList.add('btn-loading');
            submitButton.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                // Kembalikan state button
                submitButton.classList.remove('btn-loading');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                
                if (data.success) {
                    // Tutup modal create terlebih dahulu
                    hideModalWithBackdrop('createModal', () => {
                        showNotification('success', 'Berhasil', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    });
                } else {
                    showNotification('error', 'Gagal', data.message);
                }
            })
            .catch(error => {
                // Kembalikan state button
                submitButton.classList.remove('btn-loading');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                
                showNotification('error', 'Error', 'Terjadi kesalahan pada server');
                console.error('Error:', error);
            });
        });
    }

    // Handle update form
    const updateForm = document.getElementById('editLeadForm');
    if (updateForm) {
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Tambahkan loading state
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.classList.add('btn-loading');
            submitButton.disabled = true;
            
            fetch(this.action, {
                method: 'POST',
                body: new FormData(this)
            })
            .then(response => response.json())
            .then(data => {
                // Kembalikan state button
                submitButton.classList.remove('btn-loading');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                
                if (data.success) {
                    // Tutup modal terlebih dahulu, lalu tampilkan notifikasi
                    hideModalWithBackdrop('editModal', () => {
                        showNotification('success', 'Berhasil', data.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    });
                } else {
                    showNotification('error', 'Gagal', data.message);
                }
            })
            .catch(error => {
                // Kembalikan state button
                submitButton.classList.remove('btn-loading');
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
                
                showNotification('error', 'Error', 'Terjadi kesalahan pada server');
                console.error('Error:', error);
            });
        });
    }

    // Handle delete all form dengan backdrop blur
    const deleteAllForm = document.querySelector('#deleteAllForm');
    if (deleteAllForm) {
        deleteAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const vendorFilter = '<?= !empty($vendor_id) ? " untuk vendor ini" : " semua vendor" ?>';
            
            // Tampilkan backdrop blur sebelum SweetAlert
            const backdrop = document.getElementById('backdropBlur');
            document.body.classList.add('modal-open');
            
            requestAnimationFrame(() => {
                backdrop.classList.add('active');
                
                // Tampilkan SweetAlert dengan delay
                setTimeout(() => {
                    Swal.fire({
                        title: 'Hapus Semua Leads?',
                        html: `Anda akan menghapus <strong>semua data leads${vendorFilter}</strong>.<br><br>Tindakan ini <strong>tidak dapat dibatalkan</strong>!`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, Hapus Semua!',
                        cancelButtonText: 'Batal',
                        width: '300px',
                        padding: '0.6rem',
                        customClass: {
                            popup: 'small-swal-popup',
                            title: 'small-swal-title',
                            htmlContainer: 'small-swal-content',
                            confirmButton: 'small-swal-confirm',
                            cancelButton: 'small-swal-cancel'
                        },
                        didOpen: () => {
                            // Pastikan SweetAlert di atas backdrop
                            const swalContainer = document.querySelector('.swal2-container');
                            if (swalContainer) {
                                swalContainer.style.zIndex = '10000';
                            }
                        },
                        willClose: () => {
                            // Sembunyikan backdrop saat SweetAlert ditutup
                            backdrop.classList.remove('active');
                            setTimeout(() => {
                                document.body.classList.remove('modal-open');
                            }, 300);
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Tambahkan loading state
                            const submitButton = this.querySelector('button[type="submit"]');
                            const originalText = submitButton.innerHTML;
                            submitButton.classList.add('btn-loading');
                            submitButton.disabled = true;
                            
                            // Submit form
                            this.submit();
                        } else {
                            // Sembunyikan backdrop jika batal
                            backdrop.classList.remove('active');
                            setTimeout(() => {
                                document.body.classList.remove('modal-open');
                            }, 300);
                        }
                    });
                }, 100);
            });
        });
    }

    // Tutup modal saat klik di luar
    document.getElementById('createModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeCreateModal();
        }
    });

    document.getElementById('editModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeEditModal();
        }
    });

    document.getElementById('deleteModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeDeleteModal();
        }
    });

    // Tutup modal dengan tombol ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            // Cek apakah ada modal yang aktif
            const createModal = document.getElementById('createModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (createModal.classList.contains('active')) {
                closeCreateModal();
            }
            if (editModal.classList.contains('active')) {
                closeEditModal();
            }
            if (deleteModal.classList.contains('active')) {
                closeDeleteModal();
            }
        }
    });
});
</script>

<style>
/* Style untuk SweetAlert yang lebih kecil */
.small-swal-popup {
    font-size: 0.75rem !important;
    border-radius: 10px !important;
}

.small-swal-title {
    font-size: 0.9rem !important;
    margin-bottom: 0.3rem !important;
    padding: 0 !important;
}

.small-swal-content {
    font-size: 0.7rem !important;
    line-height: 1.2 !important;
    padding: 0 !important;
}

.small-swal-confirm,
.small-swal-cancel {
    font-size: 0.7rem !important;
    padding: 0.3rem 0.8rem !important;
    margin: 0 0.15rem !important;
}

.swal2-toast {
    font-size: 0.75rem !important;
}

.swal2-title {
    font-size: 0.85rem !important;
    margin-bottom: 0.15rem !important;
}

.swal2-html-container {
    font-size: 0.7rem !important;
    margin-top: 0.15rem !important;
}

/* Pastikan SweetAlert di atas backdrop */
.swal2-container {
    z-index: 10000 !important;
}

.swal2-popup {
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
</style>

<?= $this->include('admin/layouts/footer'); ?>