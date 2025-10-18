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

/* ===== Ambil data Vendor dari vendor_profiles ===== */
 $vendorProfilesById = [];
try {
  if (!empty($users)) {
    $db = db_connect();
    if ($db->tableExists('vendor_profiles')) {
      $vendorIds = array_values(array_filter(array_map(function($u) {
        $groups = normalize_groups($u);
        return in_array('vendor', $groups, true) ? (int)($u['id'] ?? 0) : 0;
      }, $users)));

      if (!empty($vendorIds)) {
        $vendorProfiles = $db->table('vendor_profiles')
          ->whereIn('user_id', $vendorIds)
          ->get()
          ->getResultArray();

        foreach ($vendorProfiles as $vp) {
          $vendorProfilesById[(int)$vp['user_id']] = $vp;
        }
      }
    }
  }
} catch (\Throwable $e) {
  log_message('error', 'Gagal ambil vendor_profiles: ' . $e->getMessage());
}

// Inisialisasi variabel
$users = $users ?? [];
$usersVendor = [];

// Filter users Vendor - SEDERHANAKAN
if (!empty($users)) {
    foreach ($users as $user) {
        $groups = normalize_groups($user);
        
        if (in_array('vendor', $groups, true)) {
            $id = (int)($user['id'] ?? 0);
            
            // Data action_by sudah langsung dari controller
            if (isset($vendorProfilesById[$id])) {
                $profile = $vendorProfilesById[$id];
                $user['business_name'] = $profile['business_name'] ?? '-';
                $user['owner_name'] = $profile['owner_name'] ?? '-';
                $user['phone'] = $user['phone'] ?? '-';
                $user['whatsapp_number'] = $profile['whatsapp_number'] ?? '-';
                $user['vendor_status'] = $profile['status'] ?? 'pending';
                $user['commission_type'] = $profile['commission_type'] ?? 'nominal';
                $user['requested_commission'] = $profile['requested_commission'] ?? null;
                $user['requested_commission_nominal'] = $profile['requested_commission_nominal'] ?? null;
            }
            $usersVendor[] = $user;
        }
    }
}

// Inisialisasi variabel
 $users = $users ?? [];
 $usersVendor = [];

// Filter users Vendor
if (!empty($users)) {
    foreach ($users as $user) {
        $groups = normalize_groups($user);
        
        if (in_array('vendor', $groups, true)) {
            $id = (int)($user['id'] ?? 0);
            if (isset($vendorProfilesById[$id])) {
                $profile = $vendorProfilesById[$id];
                $user['business_name'] = $profile['business_name'] ?? '-';
                $user['owner_name'] = $profile['owner_name'] ?? '-';
                $user['phone'] = $user['phone'] ?? '-';
                $user['whatsapp_number'] = $profile['whatsapp_number'] ?? '-';
                $user['vendor_status'] = $profile['status'] ?? 'pending';
                $user['commission_type'] = $profile['commission_type'] ?? 'nominal';
                $user['requested_commission'] = $profile['requested_commission'] ?? null;
                $user['requested_commission_nominal'] = $profile['requested_commission_nominal'] ?? null;
            }
            $usersVendor[] = $user;
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
  }

  .action-btn:hover {
      transform: translateY(-1px);
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
</style>
<script>document.addEventListener('DOMContentLoaded',()=>{document.documentElement.classList.remove('error','error-theme','with-sidebar-fallback');});</script>

<!-- Main Content Area - Menggunakan struktur yang sudah ada di header -->
<div class="content-area flex-1" x-data>
  <!-- Header -->
  <div class="px-3 md:px-6 pt-4 md:pt-6 max-w-screen-xl mx-auto w-full fade-up-soft" style="--delay:.02s">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg md:text-xl font-bold text-gray-900">Users Management - Vendor</h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Vendor</p>
      </div>
    </div>
  </div>

  <!-- Main -->
  <main id="pageMain" class="flex-1 px-3 md:px-6 pb-6 mt-3 space-y-6 max-w-screen-xl mx-auto w-full fade-up" style="--dur:.60s; --delay:.06s">

    <!-- Tabel Vendor -->
    <section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
      <div class="px-3 py-2 md:px-4 md:py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-store text-blue-600"></i> User Vendor
        </h2>
        <button type="button"
          class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm"
          @click="openCreateModal()">
          <i class="fa fa-plus text-[11px]"></i> Add Vendor
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm" data-table-role="vendor">
            <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
                <tr>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">ID</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">NAMA VENDOR</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">PEMILIK</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">USERNAME</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">NO. TLP</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">WHATSAPP</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">EMAIL</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">KOMISI DIAJUKAN</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">STATUS</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">DIPROSES OLEH</th>
                    <th class="px-2 md:px-4 py-2 md:py-3 text-center font-semibold text-white uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody id="tbody-vendor" class="divide-y divide-gray-100">
                <?php if (!empty($usersVendor)): ?>
                    <?php foreach ($usersVendor as $i => $u): 
                        $id = (int)($u['id'] ?? 0); 
                        $verificationStatus = $u['vendor_status'] ?? 'pending';
                        $isActive = !in_array($verificationStatus, ['inactive', 'rejected']);
                        $isVerified = $verificationStatus === 'verified';
                        $isPending = $verificationStatus === 'pending';
                        $isRejected = $verificationStatus === 'rejected';
                        $isInactive = $verificationStatus === 'inactive';
                        
                        $suspendLabel = $isActive ? 'Suspend' : 'Unsuspend';
                        $suspendIcon = $isActive ? 'fa-pause' : 'fa-play';
                        
                        // Format komisi
                        $commission = '-';
                        if (isset($u['commission_type'])) {
                            if ($u['commission_type'] === 'nominal' && !empty($u['requested_commission_nominal']) && $u['requested_commission_nominal'] > 0) {
                                $commission = 'Rp ' . number_format($u['requested_commission_nominal'], 0, ',', '.');
                            } elseif ($u['commission_type'] === 'percent' && !empty($u['requested_commission']) && $u['requested_commission'] > 0) {
                                $commission = number_format($u['requested_commission'], 1) . '%';
                            }
                        }
                        
                        // Format Action By - langsung dari vendor_profiles.action_by
                        $actionByDisplay = $u['action_by_display'] ?? '-';
                        $actionDate = $u['action_date'] ?? null;
                    ?>
                        <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.22 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="vendor_<?= $id ?>">
                            <td class="px-2 md:px-4 py-2 md:py-3 font-semibold text-gray-900 text-center"><?= esc($id ?: '-') ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900 text-center"><?= esc($u['business_name'] ?? '-') ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900 text-center"><?= esc($u['owner_name'] ?? '-') ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 text-center"><?= esc($u['username'] ?? '-') ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 text-center"><?= esc($u['phone'] ?? '-') ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 text-center"><?= esc($u['whatsapp_number'] ?? '-') ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 text-center"><?= esc($emailById[$id] ?? ($u['email'] ?? '-')) ?></td>
                            <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 font-medium text-center">
                                <?= $commission ?>
                            </td>
                            
                            <!-- Status Badge -->
                            <td class="px-2 md:px-4 py-2 md:py-3 text-center">
                                <div class="flex items-center gap-2 flex-wrap justify-center">
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

                            <!-- Kolom Action By - SEDERHANA -->
                            <td class="px-2 md:px-4 py-2 md:py-3 text-center text-gray-600 text-xs">
                                <?php if (!empty($actionByDisplay) && $actionByDisplay !== '-'): ?>
                                    <div class="flex flex-col items-center">
                                        <div class="font-medium text-gray-900 text-xs">
                                            <?= esc($actionByDisplay) ?>
                                        </div>
                                        <?php if (!empty($actionDate)): ?>
                                            <div class="text-gray-500 text-[10px] mt-1">
                                                <?= date('d M Y', strtotime($actionDate)) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Action Buttons -->
                            <td class="px-2 md:px-4 py-2 md:py-3 text-center">
                                <div class="action-buttons">
                                    <!-- Tombol Edit -->
                                    <button type="button" 
                                        class="action-btn bg-blue-600 hover:bg-blue-700 text-white edit-user-btn"
                                        onclick="openEditModal(<?= $id ?>)"
                                        title="Edit Vendor">
                                        <i class="fa-regular fa-pen-to-square text-[10px]"></i>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Tombol Verify - Hanya tampil untuk vendor pending -->
                                    <?php if ($isPending): ?>
                                        <button type="button" 
                                            onclick="verifyVendor(<?= $id ?>, this)"
                                            class="action-btn bg-green-600 hover:bg-green-700 text-white"
                                            title="Verify Vendor">
                                            <i class="fa-solid fa-check-circle text-[10px]"></i>
                                            <span>Verify</span>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Tombol Reject - Hanya tampil untuk vendor pending -->
                                    <?php if ($isPending): ?>
                                        <button type="button" 
                                            onclick="showRejectModal(<?= $id ?>)"
                                            class="action-btn bg-orange-600 hover:bg-orange-700 text-white"
                                            title="Reject Vendor">
                                            <i class="fa-solid fa-times-circle text-[10px]"></i>
                                            <span>Reject</span>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Tombol Suspend - Untuk semua vendor kecuali yang rejected -->
                                    <?php if (!$isRejected): ?>
                                        <button type="button" 
                                            onclick="toggleSuspendVendor(<?= $id ?>, this)"
                                            class="action-btn bg-slate-700 hover:bg-slate-800 text-white"
                                            title="<?= $suspendLabel ?> Vendor">
                                            <i class="fa-solid <?= $suspendIcon ?> text-[10px]"></i>
                                            <span><?= $suspendLabel ?></span>
                                        </button>
                                    <?php endif; ?>

                                    <!-- Tombol Delete -->
                                    <button type="button" 
                                        class="action-btn bg-rose-600 hover:bg-rose-700 text-white"
                                        data-user-name="<?= esc($u['business_name'] ?? 'Vendor') ?>" 
                                        data-role="Vendor" 
                                        onclick="UMDel.open(this)"
                                        title="Delete Vendor">
                                        <i class="fa-regular fa-trash-can text-[10px]"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr data-empty-state="true" class="fade-up-soft" style="--delay:.22s">
                        <td colspan="11" class="px-4 md:px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-14 h-14 rounded-2xl bg-gray-100 grid place-items-center"><i class="fa-solid fa-store text-xl text-gray-400"></i></div>
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

<!-- MODAL REJECT VENDOR -->
<div id="rejectVendorModal" class="modal-hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
  <button type="button" class="absolute inset-0 z-10 bg-black/40 backdrop-blur-[1.5px]" data-overlay aria-label="Tutup"></button>
  <div class="relative z-20 w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
    <div class="p-6">
      <div class="flex items-start gap-3">
        <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-orange-600">
          <i class="fa-solid fa-times-circle"></i>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Tolak Vendor</h3>
          <p class="mt-1 text-sm text-gray-600">Berikan alasan penolakan untuk vendor "<span id="rejectVendorName" class="font-semibold"></span>"</p>
        </div>
        <button id="rejectModalClose" type="button" class="shrink-0 p-1.5 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100" aria-label="Tutup">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>
      
      <div class="mt-4">
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
            <button type="button" id="rejectModalCancel" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
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
</div>

<!-- Tambahkan di index.php sebelum script -->
<div id="csrfData" style="display: none;">
    <input type="hidden" id="csrfTokenName" value="<?= csrf_token() ?>">
    <input type="hidden" id="csrfTokenValue" value="<?= csrf_hash() ?>">
    <input type="hidden" id="csrfHeaderName" value="<?= csrf_header() ?>">
</div>
</div>

<script>
// ===== CSRF TOKEN MANAGEMENT =====
function getCsrfToken() {
    return document.getElementById('csrfTokenValue')?.value || '<?= csrf_hash() ?>';
}

function getCsrfHeaderName() {
    return document.getElementById('csrfHeaderName')?.value || 'X-CSRF-TOKEN';
}

// Fungsi showToast yang diperbaiki
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
    const modal = document.getElementById('createUserModal');
    if (modal) {
        modal.classList.remove('modal-hidden');
        document.body.style.overflow = 'hidden';
        loadCreateForm();
    }
}

function openEditModal(userId) {
    const modal = document.getElementById('editUserModal');
    if (modal) {
        modal.classList.remove('modal-hidden');
        document.body.style.overflow = 'hidden';
        loadEditForm(userId);
    }
}

// Fungsi closeModal yang diperbaiki
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('modal-hidden');
        document.body.style.overflow = '';
        
        // Clear modal content
        const contentDiv = modalId === 'createUserModal' ? 'createModalContent' : 'editModalContent';
        const contentElement = document.getElementById(contentDiv);
        if (contentElement) {
            contentElement.innerHTML = `
                <div class="text-center py-8">
                    <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
                    <p class="mt-2 text-gray-600">Memuat form...</p>
                </div>
            `;
        }
    }
}

// Perbaikan fungsi loadEditForm
async function loadEditForm(userId) {
    try {
        // PERBAIKAN: Gunakan URL yang benar sesuai routing CodeIgniter
        const editUrl = `<?= site_url('admin/uservendor/edit') ?>/${userId}`;
        
        const response = await fetch(editUrl, {
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
        
        // Initialize commission toggle after a short delay
        setTimeout(() => {
            initializeCommissionToggle();
            initializeNominalFormatting();
        }, 100);
        
        // Re-attach event listeners setelah form dimuat
        const form = document.getElementById('editVendorForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitVendorForm(this, true); // true = edit
            });
        }
        
    } catch (error) {
        document.getElementById('editModalContent').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                <p class="mt-2">Gagal memuat form edit. Silakan refresh halaman.</p>
                <p class="text-xs mt-1">Error: ${error.message}</p>
                <button onclick="loadEditForm(${userId})" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Coba Lagi
                </button>
            </div>
        `;
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

// Fungsi toggle password yang diperbaiki
function togglePasswordDirect(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (!input || !icon) {
        return;
    }
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash text-sm';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye text-sm';
    }
}

// ===== SUSPEND FUNCTIONALITY FOR VENDOR =====
async function toggleSuspendVendor(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        // Disable semua tombol sementara
        disableAllSuspendButtons(true);
        
        // Tampilkan loading
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        // Perbaikan URL
        const response = await fetch(`<?= site_url('admin/uservendor/') ?>${userId}/suspend`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.success) {
            // Update UI sementara
            await updateSuspendUIVendor(userId, result.new_status, result.is_active, button);
            showToast(result.message, 'success');
            
            // Auto refresh setelah 1.5 detik
            setTimeout(() => {
                window.location.reload();
            }, 10);
            
        } else {
            showToast(result.message, 'error');
            button.innerHTML = originalHTML;
        }
        
    } catch (error) {
        showToast('Terjadi kesalahan: ' + error.message, 'error');
        button.innerHTML = originalHTML;
    } finally {
        disableAllSuspendButtons(false);
        button.disabled = false;
    }
}

// Fungsi untuk disable/enable semua tombol suspend
function disableAllSuspendButtons(disabled) {
    const allSuspendButtons = document.querySelectorAll('button[onclick*="toggleSuspendVendor"]');
    allSuspendButtons.forEach(btn => {
        if (disabled) {
            btn.disabled = true;
            btn.style.opacity = '0.6';
        } else {
            btn.disabled = false;
            btn.style.opacity = '1';
        }
    });
}

// Fungsi update UI untuk dua status
async function updateSuspendUIVendor(userId, newStatus, isActive, button) {
    return new Promise((resolve) => {
        const row = document.querySelector(`tr[data-rowkey="vendor_${userId}"]`);
        if (row) {
            const statusCell = row.querySelector('td:nth-child(9)'); // Kolom status
            
            if (statusCell) {
                // Update dua badge status
                const badges = statusCell.querySelectorAll('span');
                
                if (badges.length >= 2) {
                    // Badge 1: Verification Status (tetap sama)
                    const verificationBadge = badges[0];
                    // Biarkan verification badge seperti semula
                    
                    // Badge 2: Active/Inactive Status
                    const activeBadge = badges[1];
                    if (isActive) {
                        activeBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800';
                        activeBadge.innerHTML = '<i class="fa-solid fa-play-circle mr-1"></i> Active';
                    } else {
                        activeBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800';
                        activeBadge.innerHTML = '<i class="fa-solid fa-pause-circle mr-1"></i> Inactive';
                    }
                }
            }
        }
        
        // Update tombol suspend
        if (button) {
            const newText = isActive ? 'Suspend' : 'Unsuspend';
            const newIcon = isActive ? 'fa-pause' : 'fa-play';
            
            // Update tombol yang diklik
            button.innerHTML = `<i class="fa-solid ${newIcon} text-[10px]"></i><span>${newText}</span>`;
            
            // Update semua tombol suspend untuk user ini
            const allButtonsForUser = document.querySelectorAll(`button[onclick*="toggleSuspendVendor(${userId}"]`);
            allButtonsForUser.forEach(btn => {
                if (btn !== button) {
                    btn.innerHTML = `<i class="fa-solid ${newIcon} text-[10px]"></i><span>${newText}</span>`;
                }
            });
        }
        
        resolve();
    });
}

// Perbaikan fungsi delete
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
    const userId = getUserIdFromRow(row);
    
    // PERBAIKAN: Set delete URL yang benar
    deleteUrl = `<?= site_url('admin/uservendor') ?>/${userId}/delete`;
    
    nameEl.textContent = userName;
    document.documentElement.style.overflow = 'hidden';
    modal.classList.remove('modal-hidden');
  }

  function getUserIdFromRow(row) {
    // Ambil ID dari kolom pertama
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
      // Tampilkan loading pada tombol
      yesEl.disabled = true;
      yesEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menghapus...';
      
      const formData = new FormData();
      formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

      const response = await fetch(deleteUrl, {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      });

      const result = await response.json();
      
      if (result.success) {
        // Hapus row dari tabel dengan animasi
        targetRow.style.transition = 'opacity 0.3s, transform 0.3s';
        targetRow.style.opacity = '0';
        targetRow.style.transform = 'translateX(-20px)';
        
        setTimeout(() => {
          targetRow.remove();
          
          // Periksa apakah ada data tersisa
          const tbody = document.querySelector('#tbody-vendor');
          const remainingRows = tbody.querySelectorAll('tr:not([data-empty-state="true"])');
          
          if (remainingRows.length === 0) {
            // Tampilkan pesan tidak ada data
            const emptyRow = document.createElement('tr');
            emptyRow.setAttribute('data-empty-state', 'true');
            emptyRow.className = 'fade-up-soft';
            emptyRow.innerHTML = `
              <td colspan="10" class="px-4 md:px-6 py-16">
                <div class="flex flex-col items-center justify-center text-center">
                  <div class="w-14 h-14 rounded-2xl bg-gray-100 grid place-items-center">
                    <i class="fa-solid fa-store text-xl text-gray-400"></i>
                  </div>
                  <p class="mt-3 text-base md:text-lg font-semibold text-gray-400">Tidak ada data Vendor</p>
                  <p class="text-sm text-gray-400">Tambahkan user vendor untuk memulai</p>
                </div>
              </td>
            `;
            tbody.appendChild(emptyRow);
          }
        }, 300);
        
        // Tampilkan pesan sukses
        showToast(result.message, 'success');
        
        // PERBAIKAN: Auto refresh jika server mengirimkan flag refresh
        if (result.refresh) {
          setTimeout(() => {
            window.location.reload();
          }, 10);
        }
        
      } else {
        showToast(result.message || 'Gagal menghapus user', 'error');
      }
    } catch (error) {
      showToast('Terjadi kesalahan saat menghapus user', 'error');
    } finally {
      // Kembalikan tombol ke keadaan semula
      yesEl.disabled = false;
      yesEl.innerHTML = 'Hapus';
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

// ===== VERIFY VENDOR FUNCTION =====
async function verifyVendor(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[10px]"></i>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        // Perbaikan URL
        const response = await fetch(`<?= site_url('admin/uservendor/') ?>${userId}/verify`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showToast(result.message, 'success');
            // Refresh page setelah 1.5 detik
            setTimeout(() => {
                window.location.reload();
            }, 10);
        } else {
            showToast(result.message, 'error');
            button.innerHTML = originalHTML;
            button.disabled = false;
        }
        
    } catch (error) {
        showToast('Terjadi kesalahan: ' + error.message, 'error');
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}


// ===== REJECT VENDOR MODAL & FUNCTION =====
let currentRejectVendorId = null;

function showRejectModal(vendorId) {
    currentRejectVendorId = vendorId;
    
    // Dapatkan nama vendor untuk ditampilkan di modal
    const row = document.querySelector(`tr[data-rowkey="vendor_${vendorId}"]`);
    const vendorName = row ? row.querySelector('td:nth-child(2)').textContent : 'Vendor';
    
    document.getElementById('rejectVendorName').textContent = vendorName;
    document.getElementById('rejectVendorId').value = vendorId;
    document.getElementById('rejectReason').value = '';
    
    // Tampilkan modal
    document.getElementById('rejectVendorModal').classList.remove('modal-hidden');
    document.documentElement.style.overflow = 'hidden';
}

function closeRejectModal() {
    document.getElementById('rejectVendorModal').classList.add('modal-hidden');
    document.documentElement.style.overflow = '';
    currentRejectVendorId = null;
}

// Handle form reject submission
document.getElementById('rejectVendorForm').addEventListener('submit', async function(e) {
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
        
        // Perbaikan URL
        const response = await fetch(`<?= site_url('admin/uservendor/') ?>${vendorId}/reject`, {
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
            // Refresh page setelah 1.5 detik
            setTimeout(() => {
                window.location.reload();
            }, 10);
        } else {
            showToast(result.message, 'error');
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        }
        
    } catch (error) {
        showToast('Terjadi kesalahan: ' + error.message, 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
});

// Event listeners untuk modal reject
document.getElementById('rejectModalClose').addEventListener('click', closeRejectModal);
document.getElementById('rejectModalCancel').addEventListener('click', closeRejectModal);
document.querySelector('#rejectVendorModal [data-overlay]').addEventListener('click', closeRejectModal);

// Tambahkan di bagian event listener di index.php
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation untuk semua interaksi
    document.addEventListener('click', function(e) {
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
                closeRejectModal();
            }
        }
    });
    
    // Event listener khusus untuk tombol close di header modal
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal-id');
            if (modalId) {
                closeModal(modalId);
            }
        });
    });
    
    // Escape key untuk close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal('createUserModal');
            closeModal('editUserModal');
            closeModal('confirmDelete');
            closeRejectModal();
        }
    });
});

// ===== GLOBAL EVENT LISTENERS FOR MODAL =====
document.addEventListener('DOMContentLoaded', function() {
    // Event delegation untuk tombol batal di dalam modal
    document.addEventListener('click', function(e) {
        // Cek apakah yang diklik adalah tombol batal
        if (e.target.id === 'cancelBtn' || e.target.id === 'cancelEditBtn') {
            e.preventDefault();
            
            // Cari modal container terdekat
            let modalContainer = e.target.closest('.modal-hidden');
            if (!modalContainer) {
                // Coba cari dari parent
                modalContainer = e.target.closest('[id$="Modal"]');
            }
            
            if (modalContainer) {
                const modalId = modalContainer.id;
                closeModal(modalId);
            } else {
                // Fallback: cari modal yang sedang aktif
                const activeModals = document.querySelectorAll('.modal-hidden:not(.modal-hidden)');
                if (activeModals.length > 0) {
                    closeModal(activeModals[0].id);
                }
            }
        }
        
        // Close modal buttons (tombol X di header)
        if (e.target.closest('.close-modal')) {
            e.preventDefault();
            const button = e.target.closest('.close-modal');
            const modalId = button.getAttribute('data-modal-id');
            if (modalId) {
                closeModal(modalId);
            }
        }
        
        // Overlay click untuk close modal
        if (e.target.hasAttribute('data-overlay')) {
            e.preventDefault();
            const modals = ['createUserModal', 'editUserModal', 'confirmDelete', 'rejectVendorModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('modal-hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });
    
    // Event listener khusus untuk tombol close di header modal
    document.querySelectorAll('.close-modal').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const modalId = this.getAttribute('data-modal-id');
            if (modalId) {
                closeModal(modalId);
            }
        });
    });
    
    // Escape key untuk close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modals = ['createUserModal', 'editUserModal', 'confirmDelete', 'rejectVendorModal'];
            modals.forEach(modalId => {
                const modal = document.getElementById(modalId);
                if (modal && !modal.classList.contains('modal-hidden')) {
                    closeModal(modalId);
                }
            });
        }
    });
});

// Perbaikan fungsi toggleCommissionInput
function toggleCommissionInput() {
    const percentInput = document.getElementById('percent-input');
    const nominalInput = document.getElementById('nominal-input');
    const percentRadio = document.querySelector('input[name="commission_type"][value="percent"]');
    const nominalRadio = document.querySelector('input[name="commission_type"][value="nominal"]');
    
    if (!percentInput || !nominalInput || !percentRadio || !nominalRadio) {
        return;
    }
    
    if (percentRadio.checked) {
        percentInput.style.display = 'block';
        nominalInput.style.display = 'none';
        
        // Set required attributes
        const percentField = document.querySelector('input[name="requested_commission"]');
        const nominalField = document.querySelector('input[name="requested_commission_nominal"]');
        
        if (percentField) {
            percentField.setAttribute('required', 'required');
        }
        if (nominalField) {
            nominalField.removeAttribute('required');
        }
    } else {
        percentInput.style.display = 'none';
        nominalInput.style.display = 'block';
        
        // Set required attributes
        const percentField = document.querySelector('input[name="requested_commission"]');
        const nominalField = document.querySelector('input[name="requested_commission_nominal"]');
        
        if (percentField) {
            percentField.removeAttribute('required');
        }
        if (nominalField) {
            nominalField.setAttribute('required', 'required');
        }
    }
}

// Perbaikan fungsi initializeCommissionToggle
function initializeCommissionToggle() {
    // Remove existing listeners to avoid duplicates
    const radios = document.querySelectorAll('input[name="commission_type"]');
    radios.forEach(radio => {
        radio.removeEventListener('change', handleCommissionChange);
    });
    
    // Add new listeners
    radios.forEach(radio => {
        radio.addEventListener('change', handleCommissionChange);
    });
    
    // Initial toggle
    toggleCommissionInput();
}

// Handler for commission radio change
function handleCommissionChange() {
    toggleCommissionInput();
}

// Format nominal input
function initializeNominalFormatting() {
    const nominalFields = document.querySelectorAll('#nominal-field');
    nominalFields.forEach(field => {
        if (field) {
            // Remove existing listeners
            field.removeEventListener('input', handleNominalInput);
            field.removeEventListener('paste', handleNominalPaste);
            
            // Add new listeners
            field.addEventListener('input', handleNominalInput);
            field.addEventListener('paste', handleNominalPaste);
        }
    });
}

function handleNominalInput(e) {
    let value = e.target.value.replace(/[^\d]/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    e.target.value = value;
}

function handleNominalPaste(e) {
    setTimeout(function() {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
        }
        e.target.value = value;
    }, 1);
}

// Perbaikan fungsi validateVendorForm
function validateVendorForm(formElement, isEdit = false) {
    // Validasi password (hanya jika diisi untuk edit)
    const password = formElement.querySelector('input[name="password"]')?.value;
    const passwordConfirm = formElement.querySelector('input[name="password_confirm"]')?.value;
    
    if (!isEdit || password !== '') {
        if (password !== passwordConfirm) {
            showToast('Konfirmasi password tidak sama!', 'error');
            formElement.querySelector('input[name="password_confirm"]')?.focus();
            return false;
        }
        
        if (password.length < 8) {
            showToast('Password minimal 8 karakter!', 'error');
            formElement.querySelector('input[name="password"]')?.focus();
            return false;
        }
    }
    
    // Validasi komisi
    const commissionType = formElement.querySelector('input[name="commission_type"]:checked')?.value;
    
    if (commissionType === 'percent') {
        const percentInput = formElement.querySelector('input[name="requested_commission"]');
        const percentValue = parseFloat(percentInput?.value);
        
        if (isNaN(percentValue) || percentValue <= 0) {
            showToast('Harap isi nilai komisi persentase!', 'error');
            percentInput?.focus();
            return false;
        }
        
        if (percentValue > 100) {
            showToast('Persentase komisi tidak boleh lebih dari 100%!', 'error');
            percentInput?.focus();
            return false;
        }
    } else {
        const nominalInput = formElement.querySelector('input[name="requested_commission_nominal"]');
        const nominalValue = nominalInput?.value.replace(/\D/g, '');
        
        if (!nominalValue || nominalValue === '0') {
            showToast('Harap isi nilai komisi nominal!', 'error');
            nominalInput?.focus();
            return false;
        }
    }
    
    return true;
}

// Perbaikan fungsi setCommissionValues
function setCommissionValues(formElement) {
    const commissionType = formElement.querySelector('input[name="commission_type"]:checked')?.value;
    
    if (commissionType === 'percent') {
        const percentInput = formElement.querySelector('input[name="requested_commission"]');
        const nominalInput = formElement.querySelector('input[name="requested_commission_nominal"]');
        
        // Pastikan nilai persentase tersimpan
        if (percentInput && percentInput.value) {
            // Nilai sudah tersimpan
        }
        
        // Kosongkan nilai nominal
        if (nominalInput) {
            nominalInput.value = '';
        }
    } else {
        const nominalInput = formElement.querySelector('input[name="requested_commission_nominal"]');
        const percentInput = formElement.querySelector('input[name="requested_commission"]');
        
        // Hapus format ribuan dari nilai nominal
        if (nominalInput && nominalInput.value) {
            const cleanValue = nominalInput.value.replace(/\D/g, '');
            nominalInput.value = cleanValue;
        }
        
        // Kosongkan nilai persentase
        if (percentInput) {
            percentInput.value = '';
        }
    }
}

// Update loadCreateForm function
async function loadCreateForm() {
    try {
        const response = await fetch('<?= site_url('admin/uservendor/create') ?>', {
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
        
        // Initialize commission toggle after a short delay
        setTimeout(() => {
            initializeCommissionToggle();
            initializeNominalFormatting();
            initializePhoneInputs();
        }, 100);
        
        // Re-attach event listeners setelah form dimuat
        const form = document.getElementById('createVendorForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitVendorForm(this, false);
            });
        }
        
    } catch (error) {
        document.getElementById('createModalContent').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                <p class="mt-2">Gagal memuat form. Silakan refresh halaman.</p>
                <p class="text-xs mt-1">Error: ${error.message}</p>
                <button onclick="loadCreateForm()" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Coba Lagi
                </button>
            </div>
        `;
    }
}

// Perbaikan fungsi loadEditForm
async function loadEditForm(userId) {
    try {
        // PERBAIKAN: Gunakan URL yang benar sesuai routing
        const editUrl = `<?= site_url('admin/uservendor') ?>/${userId}/edit`;
        
        const response = await fetch(editUrl, {
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
        
        // Initialize commission toggle after a short delay
        setTimeout(() => {
            initializeCommissionToggle();
            initializeNominalFormatting();
            initializePhoneInputs();
        }, 100);
        
        // Re-attach event listeners setelah form dimuat
        const form = document.getElementById('editVendorForm');
        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();
                submitVendorForm(this, true); // true = edit
            });
        }
        
    } catch (error) {
        document.getElementById('editModalContent').innerHTML = `
            <div class="text-center py-8 text-red-600">
                <i class="fa-solid fa-exclamation-triangle text-2xl"></i>
                <p class="mt-2">Gagal memuat form edit. Silakan refresh halaman.</p>
                <p class="text-xs mt-1">Error: ${error.message}</p>
                <button onclick="loadEditForm(${userId})" class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Coba Lagi
                </button>
            </div>
        `;
    }
}

// Perbaikan fungsi submitVendorForm
async function submitVendorForm(formElement, isEdit = false) {
    const submitButton = formElement.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    
    // Clear previous errors
    clearFieldErrors(formElement);
    
    // Validate form
    if (!validateVendorForm(formElement, isEdit)) {
        return;
    }
    
    // Set commission values
    setCommissionValues(formElement);
    
    // Show loading
    submitButton.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';
    submitButton.disabled = true;
    
    try {
        const formData = new FormData(formElement);
        
        // PERBAIKAN: Gunakan URL yang benar untuk update
        let submitUrl = formElement.action;
        if (isEdit) {
            // Untuk edit, gunakan URL yang benar sesuai routing
            const userId = formData.get('id');
            submitUrl = `<?= site_url('admin/uservendor') ?>/${userId}/update`;
        }
        
        const response = await fetch(submitUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            // Coba baca response body untuk error detail
            const errorText = await response.text();
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        
        if (result.status === 'success') {
            showToast(result.message, 'success');
            
            // Close modal and refresh page after delay
            setTimeout(() => {
                const modalId = isEdit ? 'editUserModal' : 'createUserModal';
                closeModal(modalId);
                window.location.reload();
            }, 10);
            
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
        // Tampilkan error yang lebih spesifik
        let errorMessage = 'Terjadi kesalahan: ';
        if (error.message.includes('404')) {
            errorMessage += 'URL tidak ditemukan. Periksa routing.';
        } else if (error.message.includes('403')) {
            errorMessage += 'Akses ditolak. Periksa CSRF token.';
        } else if (error.message.includes('500')) {
            errorMessage += 'Error server. Periksa log server.';
        } else {
            errorMessage += error.message;
        }
        
        showToast(errorMessage, 'error');
    } finally {
        // Restore button
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

// Fungsi untuk inisialisasi input telepon
function initializePhoneInputs() {
    const phoneInputs = document.querySelectorAll('.phone-input');
    
    phoneInputs.forEach(input => {
        // Hapus event listener yang sudah ada untuk mencegah duplikasi
        input.removeEventListener('input', handlePhoneInput);
        input.removeEventListener('paste', handlePhonePaste);
        
        // Tambahkan event listener baru
        input.addEventListener('input', handlePhoneInput);
        input.addEventListener('paste', handlePhonePaste);
    });
}

// Handler untuk input telepon
function handlePhoneInput(e) {
    // Hapus semua karakter non-angka
    let value = e.target.value.replace(/[^\d]/g, '');
    e.target.value = value;
}

// Handler untuk paste di input telepon
function handlePhonePaste(e) {
    setTimeout(function() {
        // Hapus semua karakter non-angka setelah paste
        let value = e.target.value.replace(/[^\d]/g, '');
        e.target.value = value;
    }, 1);
}
</script>

<?= $this->include('admin/layouts/footer'); ?>