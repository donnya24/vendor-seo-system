<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<style>
  #pageWrap, #pageMain { 
    color:#111827; 
    width: 100%;
    margin-left: 0;
    padding-left: 0;
  }
  
  .content-wrapper {
    width: 100%;
    margin: 0;
    padding: 0;
  }
  
  .main-content-section {
    width: 100%;
    max-width: none;
    margin: 0;
    padding: 0;
  }
  
  #pageWrap a:not([class*="text-"]){ color:inherit!important; }
  
  /* MODAL FIXES */
  .modal-hidden { 
    display: none !important; 
  }
  
  .modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: flex-start;
    justify-content: center;
    z-index: 9999;
    padding: 2rem 1rem;
  }
  
  .modal-overlay.modal-active {
    display: flex !important;
  }
  
  .modal-content {
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 42rem;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    margin-top: 2rem;
  }
  
  @media (prefers-reduced-motion:no-preference){
    .fade-up{ opacity:0; transform:translate3d(0,18px,0); animation:fadeUp var(--dur,.55s) cubic-bezier(.22,.9,.24,1) forwards; animation-delay:var(--delay,0s); }
    .fade-up-soft{ opacity:0; transform:translate3d(0,12px,0); animation:fadeUp var(--dur,.45s) ease-out forwards; animation-delay:var(--delay,0s); }
    @keyframes fadeUp{ to{opacity:1; transform:none} }
  }
  
  /* Modal backdrop */
  .modal-backdrop {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
  }
  
  /* Toast notification styling */
  .toast-notification {
    z-index: 99999;
  }

  /* Center alignment for table content */
  .table-content-center td {
    text-align: center;
    vertical-align: middle;
  }

  .table-content-center th {
    text-align: center;
    vertical-align: middle;
  }

  /* Action buttons alignment */
  .action-buttons-container {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 0.375rem;
    width: 100%;
  }
</style>

<div class="content-wrapper">
  <div id="pageWrap" class="main-content-section">
    
    <!-- Header Content -->
    <div class="px-4 md:px-6 pt-4 md:pt-6 w-full fade-up-soft" style="--delay:.02s">
      <div class="flex flex-col gap-3">
        <div class="text-left">
          <h1 class="text-lg md:text-xl font-bold text-gray-900">Vendor Management</h1>
          <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Vendor</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main id="pageMain" class="flex-1 px-4 md:px-6 pb-6 mt-3 space-y-6 w-full fade-up" style="--dur:.60s; --delay:.06s">

    <!-- User Vendor -->
    <section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-store text-blue-600"></i> User Vendor
        </h2>
        <button type="button"
          onclick="loadCreateForm('<?= site_url('admin/uservendor/create'); ?>')"
          class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
          <i class="fa fa-plus text-[11px]"></i> Add Vendor
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm table-content-center" data-table-role="vendor">
          <thead class="bg-gradient-to-r from-emerald-600 to-teal-700">
            <tr>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">NAMA VENDOR</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">PEMILIK</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">USERNAME</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">NO. TLP</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">WHATSAPP</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">EMAIL</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">KOMISI</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">STATUS</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">AKSI</th>
            </tr>
          </thead>
          <tbody id="tbody-vendor" class="divide-y divide-gray-100">
            <?php if (!empty($usersVendor)): ?>
              <?php foreach ($usersVendor as $i => $u): 
                // Ambil ID langsung dari array yang sudah disiapkan controller
                $id = (int)($u['id'] ?? 0); 
                $verificationStatus = $u['vendor_status'] ?? 'pending';
                $isActive = !in_array($verificationStatus, ['inactive', 'rejected']);
                $isVerified = $verificationStatus === 'verified';
                $isPending = $verificationStatus === 'pending';
                $isRejected = $verificationStatus === 'rejected';
                $isInactive = $verificationStatus === 'inactive';
                
                $suspendLabel = $isActive ? 'Suspend' : 'Unsuspend';
                $suspendIcon = $isActive ? 'fa-regular fa-circle-pause' : 'fa-regular fa-circle-play';
                
                // Format komisi
                $commission = '-';
                if (isset($u['commission_type'])) {
                    if ($u['commission_type'] === 'nominal' && !empty($u['requested_commission_nominal'])) {
                        $commission = 'Rp ' . number_format($u['requested_commission_nominal'], 0, ',', '.');
                    } elseif ($u['commission_type'] === 'percent' && !empty($u['requested_commission'])) {
                        $commission = number_format($u['requested_commission'], 1) . '%';
                    }
                }
              ?>
                <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.22 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="vendor_<?= $id ?>">
                  <td class="px-4 py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
                  <td class="px-4 py-3 text-gray-900"><?= esc($u['business_name'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-900"><?= esc($u['owner_name'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['phone'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['whatsapp_number'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800 font-medium"><?= $commission ?></td>
                  
                  <!-- Status Badge -->
                  <td class="px-4 py-3">
                    <div class="flex items-center justify-center gap-2 flex-wrap">
                      <!-- Badge Status Verification -->
                      <?php if ($isVerified): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          <i class="fa-solid fa-check-circle mr-1"></i> Verified
                        </span>
                      <?php elseif ($isPending): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                          <i class="fa-solid fa-clock mr-1"></i> Pending
                        </span>
                      <?php elseif ($isRejected): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                          <i class="fa-solid fa-times-circle mr-1"></i> Rejected
                        </span>
                      <?php endif; ?>

                      <!-- Badge Status Active/Inactive (jika bukan rejected) -->
                      <?php if (!$isRejected): ?>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $isActive ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' ?>">
                          <i class="fa-solid <?= $isActive ? 'fa-play-circle' : 'fa-pause-circle' ?> mr-1"></i>
                          <?= $isActive ? 'Active' : 'Inactive' ?>
                        </span>
                      <?php endif; ?>
                    </div>
                  </td>

                  <!-- Action Buttons -->
                  <td class="px-4 py-3">
                    <div class="action-buttons-container">
                      <!-- TOMBOL EDIT -->
                      <button type="button" 
                        class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm edit-user-btn"
                        data-user-id="<?= $id ?>"
                        data-role="vendor"
                        title="Edit">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                      </button>

                      <!-- Tombol Delete -->
                      <button type="button" 
                        class="inline-flex items-center justify-center w-8 h-8 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm"
                        data-user-name="<?= esc($u['business_name'] ?? 'Vendor') ?>" 
                        data-role="Vendor" 
                        onclick="UMDel.open(this)"
                        title="Delete">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                      </button>

                      <!-- Tombol Verify - Hanya tampil untuk vendor pending -->
                      <?php if ($isPending): ?>
                        <button type="button" 
                          onclick="verifyVendor(<?= $id ?>, this)"
                          class="inline-flex items-center justify-center w-8 h-8 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm"
                          title="Verify">
                          <i class="fa-solid fa-check-circle text-xs"></i>
                        </button>
                      <?php endif; ?>

                      <!-- Tombol Reject - Hanya tampil untuk vendor pending -->
                      <?php if ($isPending): ?>
                        <button type="button" 
                          onclick="showRejectModal(<?= $id ?>)"
                          class="inline-flex items-center justify-center w-8 h-8 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-lg shadow-sm"
                          title="Reject">
                          <i class="fa-solid fa-times-circle text-xs"></i>
                        </button>
                      <?php endif; ?>

                      <!-- Tombol Suspend - Untuk semua vendor kecuali yang rejected -->
                      <?php if (!$isRejected): ?>
                        <button type="button" 
                          onclick="toggleSuspendVendor(<?= $id ?>, this)"
                          class="inline-flex items-center justify-center w-8 h-8 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm"
                          title="<?= $suspendLabel ?>">
                          <i class="<?= $suspendIcon ?> text-xs"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr data-empty-state="true" class="fade-up-soft" style="--delay:.22s">
                <td colspan="10" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 grid place-items-center"><i class="fa-solid fa-bullhorn text-xl text-gray-400"></i></div>
                    <p class="mt-3 text-base md:text-lg font-semibold text-gray-400">Tidak ada data Vendor</p>
                    <p class="text-sm text-gray-400">Tambahkan user vendor untuk memulai</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
    </main>
  </div>
</div>

<!-- POPUP DELETE -->
<div id="confirmDelete" class="modal-hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
  <button type="button" class="absolute inset-0 z-10 bg-black/40 backdrop-blur-[1.5px]" data-overlay aria-label="Tutup"></button>
  <div class="relative z-20 w-full max-w-sm rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
    <div class="p-4">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600"><i class="fa-regular fa-trash-can"></i></div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Apakah anda yakin ingin menghapus vendor "<span id="cdName" class="font-semibold"></span>"?</h3>
        </div>
        <button id="cdClose" type="button" class="shrink-0 p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100" aria-label="Tutup"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="mt-4 flex items-center justify-end gap-2">
        <button id="cdNo"  type="button" class="px-3 py-1.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</button>
        <button id="cdYes" type="button" class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-rose-600 text-white hover:bg-rose-700">Hapus</button>
      </div>
    </div>
  </div>
</div>

<!-- MODAL CREATE VENDOR -->
<div id="createUserModal" class="modal-hidden modal-overlay">
  <div class="modal-content">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2 close-modal"
            onclick="closeCreateModal()">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div id="createModalContent" class="p-6">
      <!-- Content akan diisi via JavaScript -->
    </div>
  </div>
</div>

<!-- MODAL EDIT VENDOR -->
<div id="editUserModal" class="modal-hidden modal-overlay">
  <div class="modal-content">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2 close-modal"
            onclick="closeEditModal()">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div id="editModalContent" class="p-6">
      <!-- Content akan diisi via JavaScript -->
    </div>
  </div>
</div>

<!-- MODAL REJECT VENDOR -->
<div id="rejectVendorModal" class="modal-hidden modal-overlay">
  <div class="modal-content">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2 close-modal"
            onclick="closeRejectModal()">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div class="p-6">
      <div class="flex items-start gap-3 mb-4">
        <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-orange-600">
          <i class="fa-solid fa-times-circle"></i>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Tolak Vendor</h3>
          <p class="mt-1 text-sm text-gray-600">Berikan alasan penolakan untuk vendor "<span id="rejectVendorName" class="font-semibold"></span>"</p>
        </div>
      </div>
      
      <form id="rejectVendorForm">
        <?= csrf_field() ?>
        <input type="hidden" id="rejectVendorId" name="vendor_id">
        <div class="mb-4">
          <label for="rejectReason" class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan</label>
          <textarea 
            id="rejectReason" 
            name="reject_reason" 
            rows="4" 
            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-orange-500 focus:border-orange-500"
            placeholder="Masukkan alasan penolakan..."
            required></textarea>
        </div>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeRejectModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
            Batal
          </button>
          <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700">
            Tolak Vendor
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Tambahkan di index.php sebelum script -->
<div id="csrfData" style="display: none;">
    <input type="hidden" id="csrfTokenName" value="<?= csrf_token() ?>">
    <input type="hidden" id="csrfTokenValue" value="<?= csrf_hash() ?>">
    <input type="hidden" id="csrfHeaderName" value="<?= csrf_header() ?>">
</div>

<script>
// ===== MODAL MANAGEMENT =====
let currentOpenModal = null;

function closeAllModals() {
    const modals = ['createUserModal', 'editUserModal', 'rejectVendorModal', 'confirmDelete'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('modal-hidden');
            modal.classList.remove('modal-active');
        }
    });
    document.body.style.overflow = '';
    currentOpenModal = null;
}

function openModal(modalId) {
    closeAllModals();
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('modal-hidden');
        modal.classList.add('modal-active');
        document.body.style.overflow = 'hidden';
        currentOpenModal = modalId;
    }
}

function closeCreateModal() {
    closeAllModals();
}

function closeEditModal() {
    closeAllModals();
}

function closeRejectModal() {
    closeAllModals();
}

// ===== LOAD FORM FUNCTIONS =====
async function loadCreateForm(url) {
    try {
        openModal('createUserModal');
        
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const html = await response.text();
        document.getElementById('createModalContent').innerHTML = html;
        
    } catch (error) {
        console.error('Error loading create form:', error);
        closeCreateModal();
        showToast('Gagal memuat form create. Silakan coba lagi.', 'error');
    }
}

// ===== EVENT DELEGATION UNTUK TOMBOL EDIT =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== VENDOR MANAGEMENT INITIALIZED ===');
    
    // Event listener untuk semua tombol edit
    document.addEventListener('click', function(e) {
        // Cek apakah yang diklik adalah tombol edit
        const editButton = e.target.closest('.edit-user-btn');
        
        if (editButton) {
            e.preventDefault();
            
            // Ambil ID dan role dari atribut data
            const userId = editButton.getAttribute('data-user-id');
            const role = editButton.getAttribute('data-role');
            
            // Debug: Cetak ID dan role yang didapat
            console.log('Edit button clicked. Vendor ID:', userId, 'Role:', role);
            
            if (!userId || !role) {
                showToast('Data vendor tidak lengkap.', 'error');
                return;
            }
            
            // Bangun URL yang benar
            const url = `<?= site_url('admin/uservendor/') ?>${userId}/edit`;
            console.log('Loading edit form from URL:', url);
            
            // Panggil fungsi untuk load form
            loadEditForm(url);
        }
    });

    // Event delegation untuk form submissions
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        if (form.closest('#createModalContent') || form.closest('#editModalContent')) {
            e.preventDefault();
            
            const isEdit = form.closest('#editModalContent') !== null;
            const isVendorForm = form.querySelector('input[name="role"][value="vendor"]') !== null;
            
            let isValid = false;
            
            if (isVendorForm) {
                isValid = handleVendorFormSubmit(form, isEdit);
            }
            
            if (isValid) {
                submitForm(form, isEdit);
            }
        }
    });
    
    // Close modal ketika klik overlay
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-overlay')) {
            closeAllModals();
        }
    });
    
    // Close modal dengan ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && currentOpenModal) {
            closeAllModals();
        }
    });
});

// ===== FUNGSI loadEditForm yang sudah diperbaiki =====
async function loadEditForm(url) {
    try {
        openModal('editUserModal');
        
        const response = await fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const html = await response.text();
        document.getElementById('editModalContent').innerHTML = html;
        
    } catch (error) {
        console.error('Error loading edit form:', error);
        closeEditModal();
        showToast('Gagal memuat form edit. Silakan coba lagi.', 'error');
    }
}

// ===== FORM SUBMISSION HANDLERS =====
function handleVendorFormSubmit(form, isEdit = false) {
    const formData = new FormData(form);
    
    // Validasi password untuk create
    if (!isEdit) {
        const password = formData.get('password');
        const passwordConfirm = formData.get('password_confirm');
        
        if (password !== passwordConfirm) {
            showToast('Konfirmasi password tidak sama!', 'error');
            return false;
        }
        
        if (password.length < 8) {
            showToast('Password minimal 8 karakter!', 'error');
            return false;
        }
    }
    
    return true;
}

// ===== FORM SUBMISSION =====
async function submitForm(form, isEdit = false) {
    const submitButton = form.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    try {
        submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
        submitButton.disabled = true;
        
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showToast(result.message, 'success');
            closeAllModals();
            
            // Redirect setelah delay
            setTimeout(() => {
                window.location.href = `<?= site_url('admin/uservendor') ?>`;
            }, 1000);
        } else {
            showToast(result.message || 'Gagal menyimpan data', 'error');
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast('Terjadi kesalahan saat menyimpan data', 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// ===== SUSPEND FUNCTIONALITY FOR VENDOR =====
async function toggleSuspendVendor(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        const response = await fetch(`<?= site_url('admin/uservendor/toggle-suspend/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            await updateSuspendUIVendor(userId, result.new_status, result.new_label, button);
            showToast(result.message, 'success');
            
            if (result.should_refresh) {
                setTimeout(() => {
                    window.location.reload();
                }, 10);
            }
            
        } else {
            showToast(result.message, 'error');
            button.innerHTML = originalHTML;
        }
        
    } catch (error) {
        console.error('Error:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
        button.innerHTML = originalHTML;
    } finally {
        button.disabled = false;
    }
}

async function updateSuspendUIVendor(userId, newStatus, isActive, button) {
    return new Promise((resolve) => {
        const row = document.querySelector(`tr[data-rowkey="vendor_${userId}"]`);
        if (row) {
            const statusCell = row.querySelector('td:nth-child(9)');
            if (statusCell) {
                const badges = statusCell.querySelectorAll('span');
                if (badges.length >= 2) {
                    const activeBadge = badges[1];
                    if (isActive) {
                        activeBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                        activeBadge.innerHTML = '<i class="fa-solid fa-play-circle mr-1"></i> Active';
                    } else {
                        activeBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                        activeBadge.innerHTML = '<i class="fa-solid fa-pause-circle mr-1"></i> Inactive';
                    }
                }
            }
        }
        
        if (button) {
            const newTitle = isActive ? 'Suspend' : 'Unsuspend';
            const newIcon = isActive ? 'fa-regular fa-circle-pause' : 'fa-regular fa-circle-play';
            
            button.innerHTML = `<i class="${newIcon} text-xs"></i>`;
            button.title = newTitle;
            
            const allButtonsForUser = document.querySelectorAll(`button[onclick*="toggleSuspendVendor(${userId}"]`);
            allButtonsForUser.forEach(btn => {
                if (btn !== button) {
                    btn.innerHTML = `<i class="${newIcon} text-xs"></i>`;
                    btn.title = newTitle;
                }
            });
        }
        
        resolve();
    });
}

// ===== DELETE FUNCTIONALITY =====
window.UMDel = (function () {
  const modal = document.getElementById('confirmDelete');
  const nameEl = document.getElementById('cdName');
  const yesEl = document.getElementById('cdYes');
  const noEl = document.getElementById('cdNo');
  const xEl = document.getElementById('cdClose');
  const overlay = modal?.querySelector('[data-overlay]');
  
  let targetRow = null;
  let deleteUrl = '';

  function open(btn) {
    const row = btn.closest('tr[data-rowkey]');
    if (!row) return;
    
    targetRow = row;
    const userName = btn.getAttribute('data-user-name') || 'Vendor';
    const userId = getUserIdFromRow(row);
    
    deleteUrl = `<?= site_url('admin/uservendor/') ?>${userId}/delete`;
    
    nameEl.textContent = userName;
    document.documentElement.style.overflow = 'hidden';
    modal.classList.remove('modal-hidden');
  }

  function getUserIdFromRow(row) {
    const idCell = row.querySelector('td:first-child');
    return idCell ? idCell.textContent.trim() : '';
  }

  function close() {
    modal.classList.add('modal-hidden');
    document.documentElement.style.overflow = '';
    targetRow = null;
    deleteUrl = '';
  }

  async function confirmDelete() {
    if (!targetRow || !deleteUrl) return;

    try {
      const response = await fetch(deleteUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        }
      });

      if (response.ok) {
        targetRow.remove();
        showToast('Vendor berhasil dihapus', 'success');
        
        setTimeout(() => {
          window.location.reload();
        }, 1000);
        
      } else {
        const result = await response.json();
        showToast(result.message || 'Gagal menghapus vendor', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('Terjadi kesalahan saat menghapus vendor', 'error');
    } finally {
      close();
    }
  }

  // Event listeners
  if (yesEl) yesEl.addEventListener('click', (e) => {
    e.stopPropagation();
    confirmDelete();
  }, true);

  if (noEl) noEl.addEventListener('click', (e) => {
    e.stopPropagation();
    close();
  }, true);

  if (xEl) xEl.addEventListener('click', (e) => {
    e.stopPropagation();
    close();
  }, true);

  if (overlay) overlay.addEventListener('click', (e) => {
    e.stopPropagation();
    close();
  }, true);

  document.addEventListener('keydown', (e) => {
    if (modal.classList.contains('modal-hidden')) return;
    if (e.key === 'Escape') {
      e.preventDefault();
      close();
    }
    if (e.key === 'Enter') {
      e.preventDefault();
      confirmDelete();
    }
  });

  return { open, close };
})();

// ===== VENDOR VERIFICATION =====
async function verifyVendor(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        const response = await fetch(`<?= site_url('admin/uservendor/verify-vendor/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showToast(result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 10);
        } else {
            showToast(result.message, 'error');
            button.innerHTML = originalHTML;
            button.disabled = false;
        }
        
    } catch (error) {
        console.error('Verify vendor error:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

// ===== VENDOR REJECTION =====
let currentRejectVendorId = null;

function showRejectModal(vendorId) {
    currentRejectVendorId = vendorId;
    
    const row = document.querySelector(`tr[data-rowkey="vendor_${vendorId}"]`);
    const vendorName = row ? row.querySelector('td:nth-child(2)').textContent : 'Vendor';
    
    document.getElementById('rejectVendorName').textContent = vendorName;
    document.getElementById('rejectVendorId').value = vendorId;
    document.getElementById('rejectReason').value = '';
    
    openModal('rejectVendorModal');
}

// Handle form reject submission
document.addEventListener('DOMContentLoaded', function() {
    const rejectVendorForm = document.getElementById('rejectVendorForm');
    if (rejectVendorForm) {
        rejectVendorForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const vendorId = currentRejectVendorId;
            const rejectReason = document.getElementById('rejectReason').value;
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            if (!vendorId || !rejectReason) {
                showToast('Alasan penolakan harus diisi', 'error');
                return;
            }
            
            try {
                submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
                submitButton.disabled = true;
                
                const formData = new FormData(this);
                
                const response = await fetch(`<?= site_url('admin/uservendor/reject-vendor/') ?>${vendorId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    showToast(result.message, 'success');
                    closeRejectModal();
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(result.message, 'error');
                    submitButton.innerHTML = originalText;
                    submitButton.disabled = false;
                }
                
            } catch (error) {
                console.error('Reject vendor error:', error);
                showToast('Terjadi kesalahan: ' + error.message, 'error');
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
            }
        });
    }
});

// ===== HELPER FUNCTIONS =====
function showToast(message, type = 'info') {
    const existingToast = document.querySelector('.toast-notification');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    const types = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500', 
        info: 'bg-blue-500'
    };
    
    toast.className = `toast-notification fixed top-4 right-4 z-[10000] px-6 py-3 rounded-lg text-white shadow-lg ${types[type] || types.info} transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

function getCsrfToken() {
    return document.getElementById('csrfTokenValue')?.value || '<?= csrf_hash() ?>';
}

function getCsrfHeaderName() {
    return document.getElementById('csrfHeaderName')?.value || 'X-CSRF-TOKEN';
}

// Commission fields toggle
function toggleCommissionFields() {
    const commissionType = document.getElementById('commission_type');
    if (commissionType) {
        commissionType.addEventListener('change', function() {
            const percentField = document.getElementById('percent_commission_field');
            const nominalField = document.getElementById('nominal_commission_field');
            
            if (this.value === 'percent') {
                if (percentField) percentField.style.display = 'block';
                if (nominalField) nominalField.style.display = 'none';
            } else {
                if (percentField) percentField.style.display = 'none';
                if (nominalField) nominalField.style.display = 'block';
            }
        });
    }
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    toggleCommissionFields();
});
</script>

<?= $this->include('admin/layouts/footer'); ?>