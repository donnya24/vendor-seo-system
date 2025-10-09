<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<?php
/* ===== Helpers ===== */
if (!function_exists('normalize_groups')) {
  function normalize_groups($user) {
    $g = $user['groups'] ?? [];
    if (is_string($g)) $g = preg_split('/[,\s]+/', $g, -1, PREG_SPLIT_NO_EMPTY);
    $out = [];
    foreach ((array)$g as $item) {
      if (is_string($item)) $out[] = strtolower(trim($item));
      elseif (is_array($item) && isset($item['name'])) $out[] = strtolower(trim($item['name']));
    }
    return $out;
  }
}
if (!function_exists('in_groups')) {
  function in_groups($user, $names) {
    $groups = normalize_groups($user);
    foreach ((array)$names as $n) if (in_array(strtolower($n), $groups, true)) return true;
    return false;
  }
}
if (!function_exists('user_id')) {
  function user_id($u) { return $u['id'] ?? $u['user_id'] ?? $u['uid'] ?? null; }
}
if (!function_exists('full_name_of')) {
  function full_name_of(array $u) {
    $get = fn($k) => isset($u[$k]) ? trim((string)$u[$k]) : '';
    foreach (['fullname','full_name','nama_lengkap','name','display_name','nickname','nick','panggilan'] as $k) {
      $v = $get($k); if ($v !== '') return preg_replace('/\s+/', ' ', $v);
    }
    $first = ''; foreach (['first_name','firstname','given_name','givenname','nama_depan'] as $k) if ($get($k)!==''){ $first=$get($k); break; }
    $last  = ''; foreach (['last_name','lastname','family_name','familyname','nama_belakang'] as $k) if ($get($k)!==''){ $last =$get($k); break; }
    $combo = trim($first.' '.$last); if ($combo!=='') return preg_replace('/\s+/', ' ', $combo);
    foreach ($u as $key=>$val) if (is_string($val) && preg_match('/\b(name|nama)\b/', str_replace(['_','-'],' ',strtolower($key)))) { $v=trim($val); if ($v!=='') return preg_replace('/\s+/', ' ', $v); }
    if ($get('username')!=='') return $get('username');
    if ($get('email')!==''){ $local = strstr($get('email'),'@',true) ?: $get('email'); if (trim($local)!=='') return trim($local); }
    return '-';
  }
}
function row_key($role, $u, $index) {
  $parts = [strtolower($role),(string)(user_id($u)??''),strtolower((string)($u['username']??'')),strtolower((string)($u['email']??'')),strtolower((string)($u['phone']??($u['no_tlp']??''))),strtolower((string)($u['fullname']??($u['name']??''))),(string)$index];
  return substr(sha1(implode('|',$parts)),0,24);
}

/* ===== Ambil EMAIL dari Shield (auth_identities.secret) ===== */
 $emailById = [];
try {
  $db = db_connect();
  if (!empty($users) && $db->tableExists('auth_identities')) {
    $ids = array_values(array_filter(array_map(fn($u)=> (int)($u['id'] ?? 0), $users)));
    if ($ids) {
      $rows = $db->table('auth_identities')
        ->select('user_id, type, secret')
        ->whereIn('user_id', $ids)
        ->where('type','email_password')
        ->get()->getResultArray();
      foreach ($rows as $r) {
        if (filter_var($r['secret'] ?? '', FILTER_VALIDATE_EMAIL)) {
          $emailById[(int)$r['user_id']] = (string)$r['secret'];
        }
      }
    }
  }
} catch (\Throwable $e) {}

/* ===== Ambil data SEO dari seo_profiles ===== */
 $seoProfilesById = [];
try {
  if (!empty($users)) {
    $db = db_connect();
    if ($db->tableExists('seo_profiles')) {
      $seoIds = array_values(array_filter(array_map(function($u) {
        $groups = normalize_groups($u);
        return in_array('seoteam', $groups, true) ? (int)($u['id'] ?? 0) : 0;
      }, $users)));

      if (!empty($seoIds)) {
        $seoProfiles = $db->table('seo_profiles')
          ->whereIn('user_id', $seoIds)
          ->get()
          ->getResultArray();

        foreach ($seoProfiles as $sp) {
          $seoProfilesById[(int)$sp['user_id']] = $sp;
        }
      }
    }
  }
} catch (\Throwable $e) {
  log_message('error', 'Gagal ambil seo_profiles: ' . $e->getMessage());
}

// Inisialisasi variabel
 $users = $users ?? [];
 $usersSeo = [];

// Filter users SEO
if (!empty($users)) {
    foreach ($users as $user) {
        $groups = normalize_groups($user);
        
        if (in_array('seoteam', $groups, true)) {
            $id = (int)($user['id'] ?? 0);
            if (isset($seoProfilesById[$id])) {
                $profile = $seoProfilesById[$id];
                $user['name'] = $profile['name'] ?? $user['name'] ?? '-';
                $user['phone'] = $profile['phone'] ?? $user['phone'] ?? '-';
                $user['seo_status'] = $profile['status'] ?? 'active';
            }
            $usersSeo[] = $user;
        }
    }
}
?>

<style>
  #pageMain { color:#111827; }
  #pageMain a:not([class*="text-"]){ color:inherit!important; }
  .modal-hidden { display:none; }
  @media (prefers-reduced-motion:no-preference){
    .fade-up{ opacity:0; transform:translate3d(0,18px,0); animation:fadeUp var(--dur,.55s) cubic-bezier(.22,.9,.24,1) forwards; animation-delay:var(--delay,0s); }
    .fade-up-soft{ opacity:0; transform:translate3d(0,12px,0); animation:fadeUp var(--dur,.45s) ease-out forwards; animation-delay:var(--delay,0s); }
    @keyframes fadeUp{ to{opacity:1; transform:none} }
  }
  
  /* Loading animation */
  .fa-spinner {
      animation: spin 1s linear infinite;
  }

  @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
  }

  /* Disabled state improvements */
  button:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none !important;
  }

  /* Form focus states */
  input:focus, select:focus, textarea:focus {
      outline: none;
      ring: 2px;
      ring-color: #3b82f6;
  }
</style>
<script>document.addEventListener('DOMContentLoaded',()=>{document.documentElement.classList.remove('error','error-theme','with-sidebar-fallback');});</script>

<!-- Main Content Area - Menggunakan struktur yang sudah ada di header -->
<div class="content-area flex-1" x-data>
  <!-- Header -->
  <div class="px-3 md:px-6 pt-4 md:pt-6 max-w-screen-xl mx-auto w-full fade-up-soft" style="--delay:.02s">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg md:text-xl font-bold text-gray-900">Users Management - Tim SEO</h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO</p>
      </div>
    </div>
  </div>

  <!-- Main -->
  <main id="pageMain" class="flex-1 px-3 md:px-6 pb-6 mt-3 space-y-6 max-w-screen-xl mx-auto w-full fade-up" style="--dur:.60s; --delay:.06s">

    <!-- Tabel SEO -->
    <section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
      <div class="px-3 py-2 md:px-4 md:py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-users text-blue-600"></i> User Tim SEO
        </h2>
        <button type="button"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm"
          @click="openCreateModal()">
          <i class="fa fa-plus text-[11px]"></i> Add Tim SEO
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm" data-table-role="seo">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">NAMA LENGKAP</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">USERNAME</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">NO. TLP</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">EMAIL</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">STATUS</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-right font-semibold text-white uppercase tracking-wider">AKSI</th>
            </tr>
          </thead>
          <tbody id="tbody-seo" class="divide-y divide-gray-100">
            <?php if (!empty($usersSeo)): ?>
                <?php foreach ($usersSeo as $i => $u): 
                    $id = (int)($u['id'] ?? 0); 
                    // PERBAIKAN: Gunakan 'seo_status' bukan 'suspend'
                    $status = strtolower((string)($u['seo_status'] ?? 'active'));
                    $isSuspended = $status === 'inactive';
                    $suspendLabel = $isSuspended ? 'Unsuspend' : 'Suspend';
                    $suspendIcon = $isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';
                ?>
                <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.22 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="seo_<?= $id ?>">
                  <td class="px-2 md:px-4 py-2 md:py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc($u['name'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['phone'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($emailById[$id] ?? ($u['email'] ?? '-')) ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                      <?= esc(ucfirst($status)) ?>
                    </span>
                  </td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-right">
                    <div class="inline-flex items-center gap-1.5">
                      <button type="button" 
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm edit-user-btn"
                        data-user-id="<?= $id ?>">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                      </button>
                      <button type="button" 
                        class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                        data-user-name="<?= esc($u['name'] ?? 'User SEO') ?>" 
                        data-user-id="<?= $id ?>"
                        data-role="Tim SEO" 
                        onclick="UMDel.open(this)">
                        <i class="fa-regular fa-trash-can text-[11px]"></i> Delete
                      </button>
                      <button type="button" 
                        onclick="toggleSuspendSeo(<?= $id ?>, this)"
                        class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm">
                        <i class="<?= $suspendIcon ?> text-[11px]"></i> 
                        <span><?= $suspendLabel ?></span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr data-empty-state="true" class="fade-up-soft" style="--delay:.18s">
                <td colspan="7" class="px-4 md:px-6 py-16">
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

<!-- POPUP DELETE -->
<div id="confirmDelete" class="modal-hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
  <button type="button" class="absolute inset-0 z-10 bg-black/40 backdrop-blur-[1.5px]" data-overlay aria-label="Tutup"></button>
  <div class="relative z-20 w-full max-w-sm rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
    <div class="p-4">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-rose-100 text-rose-600"><i class="fa-regular fa-trash-can"></i></div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Apakah anda yakin ingin menghapus user "<span id="cdName" class="font-semibold"></span>"?</h3>
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

<!-- MODAL CREATE USER -->
<div id="createUserModal" class="modal-hidden fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2 close-modal"
            data-modal-id="createUserModal">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div id="createModalContent" class="p-6">
      <div class="text-center py-8">
        <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
        <p class="mt-2 text-gray-600">Memuat form...</p>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDIT USER -->
<div id="editUserModal" class="modal-hidden fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2 close-modal"
            data-modal-id="editUserModal">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div id="editModalContent" class="p-6">
      <div class="text-center py-8">
        <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
        <p class="mt-2 text-gray-600">Memuat form...</p>
      </div>
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
// ===== CSRF TOKEN MANAGEMENT =====
function getCsrfToken() {
    return document.getElementById('csrfTokenValue')?.value || '<?= csrf_hash() ?>';
}

function getCsrfHeaderName() {
    return document.getElementById('csrfHeaderName')?.value || 'X-CSRF-TOKEN';
}

// ===== TOAST NOTIFICATION =====
// ===== IMPROVED TOAST FUNCTION =====
function showToast(message, type = 'info') {
    // Hapus toast sebelumnya jika ada
    const existingToasts = document.querySelectorAll('.custom-toast');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    const types = {
        success: 'bg-green-500 border-green-600',
        error: 'bg-red-500 border-red-600',
        warning: 'bg-yellow-500 border-yellow-600', 
        info: 'bg-blue-500 border-blue-600'
    };
    
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-triangle',
        warning: 'fa-exclamation-circle',
        info: 'fa-info-circle'
    };
    
    toast.className = `custom-toast fixed top-4 right-4 z-[10000] px-4 py-3 rounded-lg text-white shadow-lg border ${types[type] || types.info} transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="fas ${icons[type] || icons.info}"></i>
            <span class="text-sm font-medium">${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 10);
    
    // Auto remove
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

// ===== MODAL MANAGEMENT =====
function openCreateModal() {
    console.log('Opening create modal...');
    const modal = document.getElementById('createUserModal');
    if (modal) {
        modal.classList.remove('modal-hidden');
        document.body.style.overflow = 'hidden';
        loadCreateForm();
    }
}

function openEditModal(userId) {
    console.log('Opening edit modal for user:', userId);
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.classList.remove('modal-hidden');
        document.body.style.overflow = 'hidden';
        loadEditForm(userId);
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('modal-hidden');
        document.body.style.overflow = '';
        
        // Clear modal content
        const contentDiv = modalId === 'createUserModal' ? 'createModalContent' : 'editModalContent';
        document.getElementById(contentDiv).innerHTML = `
            <div class="text-center py-8">
                <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
                <p class="mt-2 text-gray-600">Memuat form...</p>
            </div>
        `;
    }
}

// Perbaikan fungsi loadCreateForm
async function loadCreateForm() {
    try {
        console.log('Loading create form...');
        const response = await fetch('<?= site_url('admin/userseo/create') ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const html = await response.text();
        document.getElementById('createModalContent').innerHTML = html;
        
        // Re-attach event listeners setelah form dimuat
        const form = document.getElementById('createSeoForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitSeoForm(this, false);
            });
        }
        
    } catch (error) {
        console.error('Error loading create form:', error);
        document.getElementById('createModalContent').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                <p class="mt-2">Gagal memuat form. Silakan refresh halaman.</p>
                <button onclick="loadCreateForm()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Coba Lagi
                </button>
            </div>
        `;
    }
}

async function loadEditForm(userId) {
    try {
        console.log('Loading edit form for user:', userId);
        // PERBAIKAN: Gunakan URL yang konsisten dengan definisi rute
        const response = await fetch(`<?= site_url('admin/userseo/edit/') ?>${userId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const html = await response.text();
        document.getElementById('editModalContent').innerHTML = html;
        
        // Re-attach event listeners setelah form dimuat
        const form = document.getElementById('editSeoForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitSeoForm(this, true); // true = edit
            });
        }
        
    } catch (error) {
        console.error('Error loading edit form:', error);
        document.getElementById('editModalContent').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                <p class="mt-2">Gagal memuat form. Silakan refresh halaman.</p>
                <button onclick="loadEditForm(${userId})" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Coba Lagi
                </button>
            </div>
        `;
    }
}

// ===== AJAX FORM SUBMISSION - YANG DIPERBAIKI =====
async function submitSeoForm(formElement, isEdit = false) {
    const submitButton = formElement.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Clear previous errors
    clearFieldErrors(formElement);
    
    // Show loading
    submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    submitButton.disabled = true;
    
    try {
        const formData = new FormData(formElement);
        
        // Validasi password match untuk create form
        if (!isEdit) {
            const password = formElement.querySelector('input[name="password"]')?.value;
            const passwordConfirm = formElement.querySelector('input[name="password_confirm"]')?.value;
            
            if (password && passwordConfirm && password !== passwordConfirm) {
                showFieldError(formElement, 'password_confirm', 'Konfirmasi password tidak sesuai');
                // Restore button
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                return;
            }
        } else {
            // Validasi password untuk edit form
            const password = formElement.querySelector('input[name="password"]')?.value;
            const passwordConfirm = formElement.querySelector('input[name="password_confirm"]')?.value;
            
            // Jika password diisi, pastikan konfirmasi password sesuai
            if (password && password !== passwordConfirm) {
                showFieldError(formElement, 'password_confirm', 'Konfirmasi password tidak sesuai');
                // Restore button
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                return;
            }
            
            // Jika password diisi, pastikan minimal 8 karakter
            if (password && password.length < 8) {
                showFieldError(formElement, 'password', 'Password minimal 8 karakter');
                // Restore button
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                return;
            }
        }
        
        const response = await fetch(formElement.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Response result:', result);
        
        if (result.status === 'success') {
            showToast(result.message, 'success');
            
            // Close modal and refresh page after delay
            setTimeout(() => {
                const modalId = isEdit ? 'editUserModal' : 'createUserModal';
                closeModal(modalId);
                // Perbaikan: Gunakan pengalihan yang lebih andal
                window.location.href = window.location.href;
            }, 1500);
            
        } else {
            // Handle field-specific errors
            if (result.field) {
                showFieldError(formElement, result.field, result.message);
            } else {
                // Handle validation errors
                if (result.errors) {
                    let errorMessage = 'Terjadi kesalahan validasi: ';
                    for (const field in result.errors) {
                        errorMessage += result.errors[field].join(', ') + ' ';
                    }
                    showToast(errorMessage, 'error');
                } else {
                    showToast(result.message || 'Terjadi kesalahan saat menyimpan data', 'error');
                }
            }
        }
        
    } catch (error) {
        console.error('Error submitting form:', error);
        showToast('Terjadi kesalahan jaringan. Silakan coba lagi.', 'error');
    } finally {
        // Restore button
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// Function to show field-specific errors
function showFieldError(formElement, fieldName, message) {
    const input = formElement.querySelector(`input[name="${fieldName}"]`);
    if (input) {
        // Add error styling to input
        input.classList.add('border-red-500');
        
        // Find or create error message container
        let errorContainer = input.parentNode.querySelector('.field-error');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.className = 'field-error text-red-500 text-xs mt-1';
            input.parentNode.appendChild(errorContainer);
        }
        
        errorContainer.textContent = message;
        errorContainer.classList.remove('hidden');
        
        // Remove error when user starts typing
        input.addEventListener('input', function() {
            clearFieldError(input);
        }, { once: true });
    }
}

// Function to clear all field errors
function clearFieldErrors(formElement) {
    const errorInputs = formElement.querySelectorAll('input.border-red-500');
    errorInputs.forEach(input => {
        clearFieldError(input);
    });
}

// Function to clear a single field error
function clearFieldError(input) {
    input.classList.remove('border-red-500');
    const errorContainer = input.parentNode.querySelector('.field-error');
    if (errorContainer) {
        errorContainer.classList.add('hidden');
    }
}

// ===== SUSPEND FUNCTIONALITY =====
async function toggleSuspendSeo(userId, button) {
    console.log('Toggle suspend SEO called for user:', userId);
    
    const originalHTML = button.innerHTML;
    
    try {
        // Show loading
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> <span>Loading...</span>';
        button.disabled = true;
        
        // PERBAIKAN: Gunakan FormData yang benar dengan CSRF token
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', getCsrfToken());
        
        const response = await fetch(`<?= site_url('admin/userseo/toggle-suspend-seo/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        console.log('Toggle suspend response:', result);
        
        // PERBAIKAN: Gunakan result.status bukan result.success
        if (result.status === 'success') {
            updateSuspendUISeo(userId, result.new_status, result.new_label, button);
            showToast(result.message, 'success');
            
            // Auto refresh setelah 1.5 detik
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showToast(result.message, 'error');
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
    // Update status badge di table
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
        const userName = btn.getAttribute('data-user-name') || 'User';
        const userId = btn.getAttribute('data-user-id');
        
        // PERBAIKAN: Gunakan URL yang benar sesuai dengan rute
        deleteUrl = `<?= site_url('admin/userseo/delete/') ?>${userId}`;
        
        nameEl.textContent = userName;
        document.body.style.overflow = 'hidden';
        modal.classList.remove('modal-hidden');
    }

    function close() {
        modal.classList.add('modal-hidden');
        document.body.style.overflow = '';
        targetRow = null;
        deleteUrl = '';
    }

    async function confirmDelete() {
        if (!targetRow || !deleteUrl) return;

        try {
            const formData = new FormData();
            formData.append('<?= csrf_token() ?>', getCsrfToken());

            const response = await fetch(deleteUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (result.status === 'success') {
                targetRow.remove();
                showToast(result.message, 'success');
                
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
                
            } else {
                showToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('Terjadi kesalahan saat menghapus user', 'error');
        } finally {
            close();
        }
    }

    // Event listeners
    if (yesEl) yesEl.addEventListener('click', confirmDelete);
    if (noEl) noEl.addEventListener('click', close);
    if (xEl) xEl.addEventListener('click', close);
    if (overlay) overlay.addEventListener('click', close);

    document.addEventListener('keydown', (e) => {
        if (modal.classList.contains('modal-hidden')) return;
        if (e.key === 'Escape') close();
        if (e.key === 'Enter') confirmDelete();
    });

    return { open, close };
})();

// ===== PASSWORD TOGGLE FUNCTION =====
window.togglePasswordDirect = function(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fa-regular fa-eye-slash text-sm';
        } else {
            input.type = 'password';
            icon.className = 'fa-regular fa-eye text-sm';
        }
    }
};

// ===== IMPROVED EVENT LISTENERS =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing event listeners...');
    
    // Event delegation untuk semua interaksi
    document.addEventListener('click', function(e) {
        // Edit buttons
        if (e.target.closest('.edit-user-btn')) {
            e.preventDefault();
            const button = e.target.closest('.edit-user-btn');
            const userId = button.getAttribute('data-user-id');
            if (userId) {
                openEditModal(parseInt(userId));
            }
        }
        
        // Close modal buttons
        if (e.target.closest('.close-modal')) {
            e.preventDefault();
            const button = e.target.closest('.close-modal');
            const modalId = button.dataset.modalId;
            if (modalId) {
                closeModal(modalId);
            }
        }
        
        // Overlay click untuk close modal
        if (e.target.hasAttribute('data-overlay')) {
            const modal = e.target.closest('.modal-hidden');
            if (!modal) {
                closeModal('createUserModal');
                closeModal('editUserModal');
                closeModal('confirmDelete');
            }
        }
    });
    
    // Escape key untuk close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('createUserModal');
            closeModal('editUserModal');
            closeModal('confirmDelete');
        }
    });
});

// Fallback untuk onclick attributes
window.openCreateModal = openCreateModal;
window.openEditModal = openEditModal;
window.toggleSuspendSeo = toggleSuspendSeo;
</script>

<?= $this->include('admin/layouts/footer'); ?>