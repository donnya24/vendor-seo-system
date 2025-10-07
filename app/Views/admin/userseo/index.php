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
          <h1 class="text-lg md:text-xl font-bold text-gray-900">SEO Team Management</h1>
          <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main id="pageMain" class="flex-1 px-4 md:px-6 pb-6 mt-3 space-y-6 w-full fade-up" style="--dur:.60s; --delay:.06s">

    <!-- Tabel SEO -->
    <section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-users text-blue-600"></i> User Tim SEO
        </h2>
        <button type="button"
          onclick="loadCreateForm('<?= site_url('admin/userseo/create'); ?>')"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
          <i class="fa fa-plus text-[11px]"></i> Add Tim SEO
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm table-content-center" data-table-role="seo">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">NAMA LENGKAP</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">USERNAME</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">NO. TLP</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">EMAIL</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">STATUS</th>
              <th class="px-4 py-3 font-semibold text-white uppercase tracking-wider">AKSI</th>
            </tr>
          </thead>
          <tbody id="tbody-seo" class="divide-y divide-gray-100">
            <?php if (!empty($usersSeo)): ?>
              <?php foreach ($usersSeo as $i => $u): 
                // Ambil ID langsung dari array yang sudah disiapkan controller
                $id = (int)($u['id'] ?? 0); 
                $status = strtolower((string)($u['seo_status'] ?? 'active'));
                $isSuspended = $status === 'inactive';
                $suspendLabel = $isSuspended ? 'Unsuspend' : 'Suspend';
                $suspendIcon = $isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';
              ?>
                <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.22 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="seo_<?= $id ?>">
                  <td class="px-4 py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
                  <td class="px-4 py-3 text-gray-900"><?= esc($u['name'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['phone'] ?? '-') ?></td>
                  <td class="px-4 py-3 text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
                  <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                      <?= esc(ucfirst($status)) ?>
                    </span>
                  </td>
                  <td class="px-4 py-3">
                    <div class="action-buttons-container">
                      <!-- TOMBOL EDIT -->
                      <button type="button" 
                        class="inline-flex items-center justify-center w-8 h-8 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm edit-user-btn"
                        data-user-id="<?= $id ?>"
                        data-role="seoteam"
                        title="Edit">
                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                      </button>
                      <button type="button" 
                        class="inline-flex items-center justify-center w-8 h-8 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm"
                        data-user-name="<?= esc($u['name'] ?? 'User SEO') ?>" 
                        data-role="Tim SEO" 
                        onclick="UMDel.open(this)"
                        title="Delete">
                        <i class="fa-regular fa-trash-can text-xs"></i>
                      </button>
                      <button type="button" 
                        onclick="toggleSuspendSeo(<?= $id ?>, this)"
                        class="inline-flex items-center justify-center w-8 h-8 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm"
                        title="<?= $suspendLabel ?>">
                        <i class="<?= $suspendIcon ?> text-xs"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr data-empty-state="true" class="fade-up-soft" style="--delay:.18s">
                <td colspan="7" class="px-6 py-16 text-center">
                  <div class="flex flex-col items-center justify-center text-center">
                    <div class="w-14 h-14 rounded-2xl bg-gray-100 grid place-items-center"><i class="fa-solid fa-bullhorn text-xl text-gray-400"></i></div>
                    <p class="mt-3 text-base md:text-lg font-semibold text-gray-400">Tidak ada data Tim SEO</p>
                    <p class="text-sm text-gray-400">Buat user Tim SEO baru untuk memulai</p>
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
          <h3 class="text-sm font-semibold text-gray-900">Apakah anda yakin ingin menghapus Tim SEO "<span id="cdName" class="font-semibold"></span>"?</h3>
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

<!-- MODAL CREATE SEO -->
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

<!-- MODAL EDIT SEO -->
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
    const modals = ['createUserModal', 'editUserModal', 'confirmDelete'];
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
    console.log('=== SEO MANAGEMENT INITIALIZED ===');
    
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
            console.log('Edit button clicked. SEO ID:', userId, 'Role:', role);
            
            if (!userId || !role) {
                showToast('Data Tim SEO tidak lengkap.', 'error');
                return;
            }
            
            // Bangun URL yang benar
            const url = `<?= site_url('admin/userseo/') ?>${userId}/edit`;
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
            const isSeoForm = form.querySelector('input[name="role"][value="seoteam"]') !== null;
            
            let isValid = false;
            
            if (isSeoForm) {
                isValid = handleSeoFormSubmit(form, isEdit);
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
function handleSeoFormSubmit(form, isEdit = false) {
    const formData = new FormData(form);
    
    // Validasi password
    const password = formData.get('password');
    const passwordConfirm = formData.get('password_confirm');
    
    if (!isEdit && (!password || password.length < 8)) {
        showToast('Password minimal 8 karakter!', 'error');
        return false;
    }
    
    if (password && password !== passwordConfirm) {
        showToast('Konfirmasi password tidak sama!', 'error');
        return false;
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
                window.location.href = `<?= site_url('admin/userseo') ?>`;
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

// ===== SUSPEND FUNCTIONALITY FOR SEO =====
async function toggleSuspendSeo(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        const response = await fetch(`<?= site_url('admin/userseo/toggle-suspend-seo/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                updateSuspendUISeo(userId, result.new_status, result.new_label, button);
                showToast(result.message, 'success');
                
                setTimeout(() => {
                    window.location.reload();
                }, 10);
            } else {
                showToast(result.message, 'error');
            }
        } else {
            showToast('Terjadi kesalahan server', 'error');
        }
        
    } catch (error) {
        console.error('Network error:', error);
        showToast('Terjadi kesalahan jaringan', 'error');
    } finally {
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

function updateSuspendUISeo(userId, newStatus, newLabel, button) {
    const row = document.querySelector(`tr[data-rowkey="seo_${userId}"]`);
    if (row) {
        const statusCell = row.querySelector('td:nth-child(6)');
        if (statusCell) {
            let badge = statusCell.querySelector('span');
            if (badge) {
                const badgeClass = newStatus === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                badge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
                badge.textContent = newLabel;
            }
        }
    }
    
    if (button) {
        const isSuspended = newStatus === 'inactive';
        const newTitle = isSuspended ? 'Unsuspend' : 'Suspend';
        const newIcon = isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';
        
        const icon = button.querySelector('i');
        if (icon) {
            icon.className = `${newIcon} text-xs`;
        }
        button.title = newTitle;
    }
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
    const userName = btn.getAttribute('data-user-name') || 'User SEO';
    const userId = getUserIdFromRow(row);
    
    deleteUrl = `<?= site_url('admin/userseo/') ?>${userId}/delete`;
    
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
        showToast('Tim SEO berhasil dihapus', 'success');
        
        setTimeout(() => {
          window.location.reload();
        }, 1000);
        
      } else {
        const result = await response.json();
        showToast(result.message || 'Gagal menghapus Tim SEO', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('Terjadi kesalahan saat menghapus Tim SEO', 'error');
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
</script>

<?= $this->include('admin/layouts/footer'); ?>