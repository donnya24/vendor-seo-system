<?= $this->include('admin/layouts/header'); ?>
<<<<<<< HEAD
=======
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
        // Check if secret is an email (not a password hash)
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
      // Filter user SEO (hanya seoteam)
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


/* ===== Ambil NO. TLP Vendor dari vendor_profiles ===== */
 $vendorPhoneById = [];
 $vendorStatusById = [];
 $vendorIsVerifiedById = [];
 $vendorCommissionById = [];
try {
  if (!empty($users)) {
    $db = db_connect();
    if ($db->tableExists('vendor_profiles')) {

      $vendorIds = array_values(array_filter(array_map(function($u){
        $groups = normalize_groups($u);
        return in_array('vendor', $groups, true) ? (int)($u['id'] ?? 0) : 0;
      }, $users)));

      if ($vendorIds) {
        $fieldNames = array_map('strtolower', $db->getFieldNames('vendor_profiles'));
        $candidates = ['phone','no_telp','no_hp','telepon','hp','wa','whatsapp','phone_number','contact_phone'];
        $present    = array_values(array_intersect($candidates, $fieldNames));

        if ($present) {
          $expr = 'COALESCE(' . implode(',', array_map(fn($c)=>"$c", $present)) . ') AS phone';
          $rows = $db->table('vendor_profiles')
            ->select("user_id, $expr, status, is_verified, commission_rate")
            ->whereIn('user_id', $vendorIds)
            ->get()->getResultArray();

          foreach ($rows as $r) {
            if (!empty($r['phone'])) $vendorPhoneById[(int)$r['user_id']] = (string)$r['phone'];
            if (!empty($r['status'])) $vendorStatusById[(int)$r['user_id']] = (string)$r['status'];
            $vendorIsVerifiedById[(int)$r['user_id']] = (int)($r['is_verified'] ?? 0) === 1;
            if (isset($r['commission_rate']) && $r['commission_rate'] !== '') {
              $vendorCommissionById[(int)$r['user_id']] = (float)$r['commission_rate'];
            }
          }
        }
      }
    }
  }
} catch (\Throwable $e) {}

// Inisialisasi variabel untuk menghindari error
 $users = $users ?? [];
 $usersSeo = [];
 $usersVendor = [];
 $currentTab = $currentTab ?? 'seo';

// Filter users based on groups
if (!empty($users)) {
    foreach ($users as $user) {
        $groups = normalize_groups($user);
        
        if (in_array('seoteam', $groups, true)) {
            // Tambahkan data dari seo_profiles jika ada
            $id = (int)($user['id'] ?? 0);
            if (isset($seoProfilesById[$id])) {
                $profile = $seoProfilesById[$id];
                $user['name'] = $profile['name'] ?? $user['name'] ?? '-';
                $user['phone'] = $profile['phone'] ?? $user['phone'] ?? '-';
                $user['seo_status'] = $profile['status'] ?? 'active';
            }
            $usersSeo[] = $user;
        }

        if (in_array('vendor', $groups, true)) {
            // Tambahkan data dari vendor_profiles jika ada
            $id = (int)($user['id'] ?? 0);
            if (isset($vendorPhoneById[$id])) {
                $user['phone'] = $vendorPhoneById[$id];
            }
            if (isset($vendorStatusById[$id])) {
                $user['vendor_status'] = $vendorStatusById[$id];
            }
            if (isset($vendorIsVerifiedById[$id])) {
                $user['is_verified'] = $vendorIsVerifiedById[$id];
            }
            if (isset($vendorCommissionById[$id])) {
                $user['commission_rate'] = $vendorCommissionById[$id];
            }
            $usersVendor[] = $user;
        }
    }
}
?>
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a

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
  
  /* Tab styling */
  .tab-active {
    background-color: #2563eb;
    color: white;
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

<<<<<<< HEAD
<div class="content-wrapper">
  <div id="pageWrap" class="main-content-section">
    
    <!-- Header Content -->
    <div class="px-4 md:px-6 pt-4 md:pt-6 w-full fade-up-soft" style="--delay:.02s">
      <div class="flex flex-col gap-3">
        <div class="text-left">
          <h1 class="text-lg md:text-xl font-bold text-gray-900">Users Management</h1>
          <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO dan Vendor</p>
        </div>
=======
<div id="pageWrap" class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out" :class="($store.ui.sidebar && (typeof $store.layout.isDesktop==='undefined' || $store.layout.isDesktop)) ? 'md:ml-64' : 'ml-0'">
  
  <!-- Header -->
  <div class="px-3 md:px-6 pt-4 md:pt-6 max-w-screen-xl mx-auto w-full fade-up-soft" style="--delay:.02s">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg md:text-xl font-bold text-gray-900">Users Management</h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO dan Vendor</p>
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
      </div>
    </div>

    <!-- Main Content -->
    <main id="pageMain" class="flex-1 px-4 md:px-6 pb-6 mt-3 space-y-6 w-full fade-up" style="--dur:.60s; --delay:.06s">

<<<<<<< HEAD
    <!-- Tabel SEO -->
    <?php if ($currentTab == 'seo'): ?>
    <section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-users text-blue-600"></i> User Tim SEO
        </h2>
        <button type="button"
          onclick="loadCreateForm('<?= site_url('admin/users/create?role=seoteam'); ?>')"
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
=======
<!-- Tabel SEO -->
<?php if ($currentTab == 'seo'): ?>
<section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
  <div class="px-3 py-2 md:px-4 md:py-3 border-b border-gray-100 flex items-center justify-between">
    <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-users text-blue-600"></i> User Tim SEO
    </h2>
    <a href="<?= site_url('admin/users/create?role=seoteam'); ?>"
      class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm"
      onclick="loadCreateForm('<?= site_url('admin/users/create?role=seoteam'); ?>'); return false;">
      <i class="fa fa-plus text-[11px]"></i> Add Tim SEO
    </a>
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
                    onclick="loadEditForm('<?= site_url('admin/users/') . $id . '/edit?role=seoteam'; ?>')">
                    <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                  </button>
                  <button type="button" 
                    class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                    data-user-name="<?= esc($u['name'] ?? 'User SEO') ?>" 
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
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
                      <!-- TOMBOL EDIT - PASTIKAN ID YANG BENAR DIPAKAI -->
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
    <?php endif; ?>

    <!-- User Vendor -->
    <?php if ($currentTab == 'vendor'): ?>
    <section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-store text-blue-600"></i> User Vendor
        </h2>
<<<<<<< HEAD
        <button type="button"
          onclick="loadCreateForm('<?= site_url('admin/users/create?role=vendor'); ?>')"
          class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
          <i class="fa fa-plus text-[11px]"></i> Add Vendor
        </button>
=======
        <a href="<?= site_url('admin/users/create?role=vendor'); ?>"
          class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm"
          onclick="loadCreateForm('<?= site_url('admin/users/create?role=vendor'); ?>'); return false;">
          <i class="fa fa-plus text-[11px]"></i> Add Vendor
        </a>
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm table-content-center" data-table-role="vendor">
          <thead class="bg-gradient-to-r from-emerald-600 to-teal-700">
            <tr>
<<<<<<< HEAD
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
=======
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">NAMA VENDOR</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">PEMILIK</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">USERNAME</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">NO. TLP</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">WHATSAPP</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">EMAIL</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">KOMISI</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">STATUS</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-right font-semibold text-white uppercase tracking-wider">AKSI</th>
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
            </tr>
          </thead>
          <tbody id="tbody-vendor" class="divide-y divide-gray-100">
            <?php if (!empty($usersVendor)): ?>
              <?php foreach ($usersVendor as $i => $u): 
<<<<<<< HEAD
                // Ambil ID langsung dari array yang sudah disiapkan controller
=======
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
<<<<<<< HEAD
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
=======
                  <td class="px-2 md:px-4 py-2 md:py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc($u['business_name'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc($u['owner_name'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['phone'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['whatsapp_number'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 font-medium"><?= $commission ?></td>
                  
                  <!-- Status Badge -->
                  <td class="px-2 md:px-4 py-2 md:py-3">
                    <div class="flex items-center gap-2 flex-wrap">
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
<<<<<<< HEAD
                  <td class="px-4 py-3">
                    <div class="action-buttons-container">
                      <!-- TOMBOL EDIT - PASTIKAN ID YANG BENAR DIPAKAI -->
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

=======
                  <td class="px-2 md:px-4 py-2 md:py-3 text-right">
                    <div class="inline-flex items-center gap-1.5 flex-wrap">
                      <!-- Tombol Edit -->
                      <button type="button" 
                        class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm edit-user-btn"
                        onclick="loadEditForm('<?= site_url('admin/users/') . $id . '/edit?role=vendor'; ?>')">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                      </button>

                      <!-- Tombol Delete -->
                      <button type="button" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                              data-user-name="<?= esc($u['business_name'] ?? 'Vendor') ?>" data-role="Vendor" onclick="UMDel.open(this)">
                        <i class="fa-regular fa-trash-can text-[11px]"></i> Delete
                      </button>

>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
                      <!-- Tombol Verify - Hanya tampil untuk vendor pending -->
                      <?php if ($isPending): ?>
                        <button type="button" 
                          onclick="verifyVendor(<?= $id ?>, this)"
<<<<<<< HEAD
                          class="inline-flex items-center justify-center w-8 h-8 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm"
                          title="Verify">
                          <i class="fa-solid fa-check-circle text-xs"></i>
=======
                          class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm">
                          <i class="fa-solid fa-check-circle text-[11px]"></i> Verify
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
                        </button>
                      <?php endif; ?>

                      <!-- Tombol Reject - Hanya tampil untuk vendor pending -->
                      <?php if ($isPending): ?>
                        <button type="button" 
                          onclick="showRejectModal(<?= $id ?>)"
<<<<<<< HEAD
                          class="inline-flex items-center justify-center w-8 h-8 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-lg shadow-sm"
                          title="Reject">
                          <i class="fa-solid fa-times-circle text-xs"></i>
=======
                          class="inline-flex items-center gap-1.5 bg-orange-600 hover:bg-orange-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm">
                          <i class="fa-solid fa-times-circle text-[11px]"></i> Reject
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
                        </button>
                      <?php endif; ?>

                      <!-- Tombol Suspend - Untuk semua vendor kecuali yang rejected -->
                      <?php if (!$isRejected): ?>
                        <button type="button" 
                          onclick="toggleSuspendVendor(<?= $id ?>, this)"
<<<<<<< HEAD
                          class="inline-flex items-center justify-center w-8 h-8 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-sm"
                          title="<?= $suspendLabel ?>">
                          <i class="<?= $suspendIcon ?> text-xs"></i>
=======
                          class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm">
                          <i class="<?= $suspendIcon ?> text-[11px]"></i> 
                          <span><?= $suspendLabel ?></span>
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr data-empty-state="true" class="fade-up-soft" style="--delay:.22s">
<<<<<<< HEAD
                <td colspan="10" class="px-6 py-16 text-center">
=======
                <td colspan="10" class="px-4 md:px-6 py-16">
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
    <?php endif; ?>
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

<!-- MODAL EDIT USER -->
<<<<<<< HEAD
<div id="editUserModal" class="modal-hidden modal-overlay">
  <div class="modal-content">
=======
<div id="editUserModal"
     class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4"
     x-data="editUserModal" 
     x-show="open"
     x-cloak>
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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

<<<<<<< HEAD
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
=======
<!-- ⭐⭐ MODAL REJECT VENDOR - Tambahkan sebelum </body> ⭐⭐ -->
<div id="rejectVendorModal" class="modal-hidden fixed inset-0 z-[9999] flex items-center justify-center p-4">
  <button type="button" class="absolute inset-0 z-10 bg-black/40 backdrop-blur-[1.5px]" data-overlay aria-label="Tutup"></button>
  <div class="relative z-20 w-full max-w-md rounded-2xl bg-white shadow-xl ring-1 ring-black/5">
    <div class="p-6">
      <div class="flex items-start gap-3">
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
        <div class="mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-full bg-orange-100 text-orange-600">
          <i class="fa-solid fa-times-circle"></i>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-semibold text-gray-900">Tolak Vendor</h3>
          <p class="mt-1 text-sm text-gray-600">Berikan alasan penolakan untuk vendor "<span id="rejectVendorName" class="font-semibold"></span>"</p>
        </div>
<<<<<<< HEAD
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
=======
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
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
    console.log('=== USER MANAGEMENT INITIALIZED ===');
    
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
            console.log('Edit button clicked. User ID:', userId, 'Role:', role);
            
            if (!userId || !role) {
                showToast('Data user tidak lengkap.', 'error');
                return;
            }
            
            // Bangun URL yang benar
            const url = `<?= site_url('admin/users/') ?>${userId}/edit?role=${role}`;
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
            const isVendorForm = form.querySelector('input[name="role"][value="vendor"]') !== null;
            
            let isValid = false;
            
            if (isSeoForm) {
                isValid = handleSeoFormSubmit(form, isEdit);
            } else if (isVendorForm) {
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

<<<<<<< HEAD
// ===== FORM SUBMISSION HANDLERS =====
function handleSeoFormSubmit(form, isEdit = false) {
    const formData = new FormData(form);
=======
  // Tambahkan di bagian script form vendor (create dan edit)
function toggleCommissionFields() {
    const commissionType = document.getElementById('commission_type').value;
    const percentField = document.getElementById('percent_commission_field');
    const nominalField = document.getElementById('nominal_commission_field');
    
    if (commissionType === 'percent') {
        percentField.style.display = 'block';
        nominalField.style.display = 'none';
        // Clear nominal field when switching to percent
        document.getElementById('requested_commission_nominal').value = '';
    } else {
        percentField.style.display = 'none';
        nominalField.style.display = 'block';
        // Clear percent field when switching to nominal
        document.getElementById('requested_commission').value = '';
    }
}

// Panggil saat halaman load untuk set initial state
document.addEventListener('DOMContentLoaded', function() {
    toggleCommissionFields();
});

  // SEO Form Component (untuk create)
  Alpine.data('seoForm', () => ({
    loading: false,
    showPassword: false,
    showConfirmPassword: false,
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
    
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
                const role = form.querySelector('input[name="role"]').value;
                window.location.href = `<?= site_url('admin/users?tab=') ?>${role === 'seoteam' ? 'seo' : 'vendor'}`;
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
        
        const response = await fetch(`<?= site_url('admin/users/toggle-suspend-seo/') ?>${userId}`, {
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

// ===== SUSPEND FUNCTIONALITY FOR VENDOR =====
async function toggleSuspendVendor(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        const response = await fetch(`<?= site_url('admin/users/toggle-suspend/') ?>${userId}`, {
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

<<<<<<< HEAD
=======
// Fallback jika Alpine gagal (create)
function fallbackLoadCreateForm(url) {
  console.log('Menggunakan fallback method untuk:', url);
  
  fetch(url, { 
    headers: { 'X-Requested-With': 'XMLHttpRequest' } 
  })
  .then(response => response.text())
  .then(html => {
    const tempModal = document.createElement('div');
    tempModal.innerHTML = `
      <div class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
          <button type="button" class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2" onclick="this.closest('.fixed').remove()">
            <i class="fa-solid fa-times text-lg"></i>
          </button>
          <div class="p-6">${html}</div>
        </div>
      </div>
    `;
    document.body.appendChild(tempModal);
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Gagal memuat form');
  });
}

// Fallback untuk edit form
function fallbackLoadEditForm(url) {
  console.log('Menggunakan fallback method untuk edit form:', url);
  
  fetch(url, { 
    headers: { 'X-Requested-With': 'XMLHttpRequest' } 
  })
  .then(response => response.text())
  .then(html => {
    const tempModal = document.createElement('div');
    tempModal.innerHTML = `
      <div class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
          <button type="button" class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2" onclick="this.closest('.fixed').remove()">
            <i class="fa-solid fa-times text-lg"></i>
          </button>
          <div class="p-6">${html}</div>
        </div>
      </div>
    `;
    document.body.appendChild(tempModal);
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Gagal memuat form edit');
  });
}

// ===== SUSPEND FUNCTIONALITY FOR SEO =====
// Fungsi untuk toggle suspend SEO
async function toggleSuspendSeo(userId, button) {
    console.log('Toggle suspend SEO called for user:', userId);
    
    const originalHTML = button.innerHTML;
    
    try {
        // Tampilkan loading
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> <span>Loading...</span>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        console.log('Sending request to toggle suspend SEO:', userId);
        
        // ⭐⭐ GUNAKAN ROUTE YANG BENAR UNTUK SEO ⭐⭐
        const response = await fetch(`<?= site_url('admin/users/toggle-suspend-seo/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        
        if (response.ok) {
            const result = await response.json();
            console.log('Success result:', result);
            
            if (result.success) {
                updateSuspendUISeo(userId, result.new_status, result.new_label, button);
                showToast(result.message, 'success');
                
                // Auto refresh setelah 1.5 detik
                setTimeout(() => {
                    window.location.reload();
                }, 10);
            } else {
                showToast(result.message, 'error');
            }
        } else {
            const errorText = await response.text();
            console.error('Server error:', errorText);
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

// Fungsi untuk update UI SEO
function updateSuspendUISeo(userId, newStatus, newLabel, button) {
    console.log('Updating UI for SEO:', userId, 'New status:', newStatus);
    
    // Update status badge di table
    const row = document.querySelector(`tr[data-rowkey="seo_${userId}"]`);
    if (row) {
        const statusCell = row.querySelector('td:nth-child(6)'); // Kolom status SEO
        
        if (statusCell) {
            let badge = statusCell.querySelector('span');
            if (badge) {
                // Update class badge berdasarkan status
                const badgeClass = newStatus === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                badge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
                badge.textContent = newLabel;
                console.log('Updated SEO badge:', badgeClass, newLabel);
            }
        }
    }
    
    // Update suspend button
    if (button) {
        const isSuspended = newStatus === 'inactive';
        const newText = isSuspended ? 'Unsuspend' : 'Suspend';
        const newIcon = isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';
        
        console.log('Updating SEO button:', newText, newIcon);
        
        // Update icon dan text
        const icon = button.querySelector('i');
        const textSpan = button.querySelector('span');
        
        if (icon) {
            icon.className = `${newIcon} text-[11px]`;
        }
        if (textSpan) {
            textSpan.textContent = newText;
        }
    }
}

// ===== SUSPEND FUNCTIONALITY =====
// Fungsi untuk toggle suspend Vendor
async function toggleSuspendVendor(userId, button) {
    console.log('Toggle suspend Vendor called for user:', userId);
    
    const originalHTML = button.innerHTML;
    
    try {
        // Disable semua tombol sementara
        disableAllSuspendButtons(true);
        
        // Tampilkan loading
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> <span>Loading...</span>';
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        console.log('Sending request to toggle suspend vendor:', userId);
        
        const response = await fetch(`<?= site_url('admin/users/toggle-suspend/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Server response:', result);
        
        if (result.success) {
            // Update UI sementara
            await updateSuspendUIVendor(userId, result.new_status, result.new_label, button);
            showToast(result.message, 'success');
            
            // ⭐⭐ AUTO REFRESH SETELAH 1.5 DETIK ⭐⭐
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
        disableAllSuspendButtons(false);
        button.disabled = false;
    }
}

// ⭐⭐ FUNGSI UNTUK DISABLE/ENABLE SEMUA TOMBOL SUSPEND ⭐⭐
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
    console.log('Updating UI for vendor:', userId, 'New status:', newStatus, 'Is active:', isActive);
    
    return new Promise((resolve) => {
        const row = document.querySelector(`tr[data-rowkey="vendor_${userId}"]`);
        if (row) {
            const statusCell = row.querySelector('td:nth-child(9)'); // Kolom status
            
            if (statusCell) {
                // ⭐⭐ UPDATE DUA BADGE STATUS ⭐⭐
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
                        activeBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                        activeBadge.innerHTML = '<i class="fa-solid fa-pause-circle mr-1"></i> Inactive';
                    }
                    
                    console.log('Updated both status badges');
                }
            }
        }
        
        // Update tombol suspend
        if (button) {
            const newText = isActive ? 'Suspend' : 'Unsuspend';
            const newIcon = isActive ? 'fa-regular fa-circle-pause' : 'fa-regular fa-circle-play';
            
            console.log('Updating button to:', newText);
            
            // Update tombol yang diklik
            button.innerHTML = `<i class="${newIcon} text-[11px]"></i> <span>${newText}</span>`;
            
            // Update semua tombol suspend untuk user ini
            const allButtonsForUser = document.querySelectorAll(`button[onclick*="toggleSuspendVendor(${userId}"]`);
            allButtonsForUser.forEach(btn => {
                if (btn !== button) {
                    btn.innerHTML = `<i class="${newIcon} text-[11px]"></i> <span>${newText}</span>`;
                }
            });
        }
        
        resolve();
    });
}

// Test function untuk debug
window.debugSuspend = function(userId) {
    console.log('=== DEBUG SUSPEND ===');
    console.log('User ID:', userId);
    console.log('CSRF Token:', '<?= csrf_hash() ?>');
    console.log('Toggle URL:', `<?= site_url('admin/users/toggle-suspend/') ?>${userId}`);
    
    // Cek apakah row ditemukan
    const row = document.querySelector(`tr[data-rowkey="vendor_${userId}"]`);
    console.log('Row found:', !!row);
    
    if (row) {
        const statusCell = row.querySelector('td:nth-child(9)');
        console.log('Status cell:', statusCell);
    }
};

>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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
  let currentTab = '<?= $currentTab ?? "seo" ?>';

  function open(btn) {
    const row = btn.closest('tr[data-rowkey]');
    if (!row) return;
    
    targetRow = row;
    const userName = btn.getAttribute('data-user-name') || 'User';
    const userId = getUserIdFromRow(row);
    
    deleteUrl = `<?= site_url('admin/users/') ?>${userId}/delete`;
    
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
        showToast('User berhasil dihapus', 'success');
        
        setTimeout(() => {
          window.location.href = `<?= site_url('admin/users?tab=') ?>${currentTab}`;
        }, 1000);
        
      } else {
        const result = await response.json();
        showToast(result.message || 'Gagal menghapus user', 'error');
      }
    } catch (error) {
      console.error('Error:', error);
      showToast('Terjadi kesalahan saat menghapus user', 'error');
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

<<<<<<< HEAD
// ===== VENDOR VERIFICATION =====
async function verifyVendor(userId, button) {
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-xs"></i>';
=======
// ===== INITIALIZATION =====
document.addEventListener('DOMContentLoaded', function() {
  console.log('=== USER MANAGEMENT INITIALIZED ===');
  console.log('Alpine.js loaded:', typeof Alpine !== 'undefined');
  console.log('Create modal:', document.querySelector('#createUserModal'));
  console.log('Edit modal:', document.querySelector('#editUserModal'));
  
  // Event listener untuk tombol create
  document.addEventListener('click', function(e) {
    const createBtn = e.target.closest('a[onclick*="loadCreateForm"]');
    if (createBtn) {
      e.preventDefault();
      const onclickAttr = createBtn.getAttribute('onclick');
      const urlMatch = onclickAttr.match(/loadCreateForm\('([^']+)'\)/);
      
      if (urlMatch && urlMatch[1]) {
        console.log('Create button clicked, URL:', urlMatch[1]);
        loadCreateForm(urlMatch[1]);
      }
    }
  });

  // Event listener untuk tombol edit
  document.addEventListener('click', function(e) {
    const editBtn = e.target.closest('button[onclick*="loadEditForm"]');
    if (editBtn) {
      e.preventDefault();
      const onclickAttr = editBtn.getAttribute('onclick');
      const urlMatch = onclickAttr.match(/loadEditForm\('([^']+)'\)/);
      
      if (urlMatch && urlMatch[1]) {
        console.log('Edit button clicked, URL:', urlMatch[1]);
        loadEditForm(urlMatch[1]);
      }
    }
  });

  // Debug functions
  window.testLoadCreate = function(role = 'seoteam') {
    const url = `<?= site_url('admin/users/create?role=') ?>${role}`;
    console.log('Testing load create form:', url);
    loadCreateForm(url);
  };
  
  window.testLoadEdit = function(userId, role = 'seoteam') {
    const url = `<?= site_url('admin/users/') ?>${userId}/edit?role=${role}`;
    console.log('Testing load edit form:', url);
    loadEditForm(url);
  };
  
  window.debugModals = function() {
    console.log('=== MODAL DEBUG ===');
    const createModal = document.querySelector('#createUserModal');
    const editModal = document.querySelector('#editUserModal');
    console.log('Create Modal Alpine instance:', createModal?.__x);
    console.log('Edit Modal Alpine instance:', editModal?.__x);
  };
});

// ===== EXISTING FUNCTIONS =====
/* Patch dari localStorage jika phone/email masih '-' */
(function(){
  try{
    const cache = JSON.parse(localStorage.getItem('userInfoCache_v1') || '{}');
    if (!cache || typeof cache !== 'object') return;
    document.querySelectorAll('tbody .js-username').forEach(function(cell){
      const username = (cell.textContent || '').trim();
      if (!username || !cache[username]) return;
      const row = cell.closest('tr'); if (!row) return;
      const phoneEl = row.querySelector('.js-phone');
      const emailEl = row.querySelector('.js-email');
      if (phoneEl && (!phoneEl.textContent.trim() || phoneEl.textContent.trim()==='-') && cache[username].phone){
        phoneEl.textContent = cache[username].phone;
      }
      if (emailEl && (!emailEl.textContent.trim() || emailEl.textContent.trim()==='-') && cache[username].email){
        emailEl.textContent = cache[username].email;
      }
    });
  }catch(e){}
})();

// ===== TOAST NOTIFICATION =====
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    const types = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500', 
        info: 'bg-blue-500'
    };
    
    toast.className = `fixed top-4 right-4 z-[10000] px-6 py-3 rounded-lg text-white shadow-lg ${types[type] || types.info} transition-all duration-300 transform translate-x-full`;
    toast.innerHTML = `
        <div class="flex items-center gap-2">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'exclamation-triangle' : 'info'}"></i>
            <span>${message}</span>
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

// Helper function untuk mendapatkan CSRF token
function getCsrfToken() {
    return document.getElementById('csrfTokenValue')?.value || '<?= csrf_hash() ?>';
}

// Helper function untuk mendapatkan CSRF header name
function getCsrfHeaderName() {
    return document.getElementById('csrfHeaderName')?.value || 'X-CSRF-TOKEN';
}

// ⭐⭐ VERIFY VENDOR FUNCTION ⭐⭐
async function verifyVendor(userId, button) {
    console.log('Verify vendor called for user:', userId);
    
    const originalHTML = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin text-[11px]"></i> <span>Loading...</span>';
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
        button.disabled = true;
        
        const formData = new FormData();
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        const response = await fetch(`<?= site_url('admin/users/verify-vendor/') ?>${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
<<<<<<< HEAD
        
        if (response.ok && result.success) {
            showToast(result.message, 'success');
=======
        console.log('Verify vendor response:', result);
        
        if (response.ok && result.success) {
            showToast(result.message, 'success');
            // Refresh page setelah 1.5 detik
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
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

<<<<<<< HEAD
// ===== VENDOR REJECTION =====
=======
// ⭐⭐ REJECT VENDOR MODAL & FUNCTION ⭐⭐
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
let currentRejectVendorId = null;

function showRejectModal(vendorId) {
    currentRejectVendorId = vendorId;
    
<<<<<<< HEAD
=======
    // Dapatkan nama vendor untuk ditampilkan di modal
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
    const row = document.querySelector(`tr[data-rowkey="vendor_${vendorId}"]`);
    const vendorName = row ? row.querySelector('td:nth-child(2)').textContent : 'Vendor';
    
    document.getElementById('rejectVendorName').textContent = vendorName;
    document.getElementById('rejectVendorId').value = vendorId;
    document.getElementById('rejectReason').value = '';
    
<<<<<<< HEAD
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
                
                const response = await fetch(`<?= site_url('admin/users/reject-vendor/') ?>${vendorId}`, {
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
=======
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
        
        const response = await fetch(`<?= site_url('admin/users/reject-vendor/') ?>${vendorId}`, {
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
        console.error('Reject vendor error:', error);
        showToast('Terjadi kesalahan: ' + error.message, 'error');
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
});

// Event listeners untuk modal reject
document.getElementById('rejectModalClose').addEventListener('click', closeRejectModal);
document.getElementById('rejectModalCancel').addEventListener('click', closeRejectModal);
document.querySelector('#rejectVendorModal [data-overlay]').addEventListener('click', closeRejectModal);
>>>>>>> 5620e9ef5b9dcc016f302099c9a1eb329f12ba2a
</script>

<?= $this->include('admin/layouts/footer'); ?>