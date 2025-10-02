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

<style>
  #pageWrap, #pageMain { color:#111827; }
  #pageWrap a:not([class*="text-"]){ color:inherit!important; }
  .modal-hidden { display:none; }
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
</style>
<script>document.addEventListener('DOMContentLoaded',()=>{document.documentElement.classList.remove('error','error-theme','with-sidebar-fallback');});</script>

<div id="pageWrap" class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out" :class="($store.ui.sidebar && (typeof $store.layout.isDesktop==='undefined' || $store.layout.isDesktop)) ? 'md:ml-64' : 'ml-0'">
  
  <!-- Header -->
  <div class="px-3 md:px-6 pt-4 md:pt-6 max-w-screen-xl mx-auto w-full fade-up-soft" style="--delay:.02s">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg md:text-xl font-bold text-gray-900">Users Management</h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO dan Vendor</p>
      </div>
    </div>
    
    <!-- Tab Navigation -->
    <div class="mt-4 flex space-x-1 bg-gray-100 p-1 rounded-lg w-fit">
      <a href="<?= site_url('admin/users?tab=seo') ?>" 
         class="px-4 py-2 text-sm font-medium rounded-md transition-colors <?= $currentTab === 'seo' ? 'tab-active' : 'text-gray-600 hover:text-gray-900' ?>">
        Tim SEO
      </a>
      <a href="<?= site_url('admin/users?tab=vendor') ?>" 
         class="px-4 py-2 text-sm font-medium rounded-md transition-colors <?= $currentTab === 'vendor' ? 'tab-active' : 'text-gray-600 hover:text-gray-900' ?>">
        Vendor
      </a>
    </div>
  </div>

  <!-- Main -->
  <main id="pageMain" class="flex-1 px-3 md:px-6 pb-6 mt-3 space-y-6 max-w-screen-xl mx-auto w-full fade-up" style="--dur:.60s; --delay:.06s">

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
            <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.16 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="seo_<?= $id ?>">
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
                  <button type="button" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                          data-user-name="<?= esc($u['name'] ?? 'User SEO') ?>" data-role="Tim SEO" onclick="UMDel.open(this)">
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
<?php endif; ?>

<!-- User Vendor -->
<?php if ($currentTab == 'vendor'): ?>
<section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
  <div class="px-3 py-2 md:px-4 md:py-3 border-b border-gray-100 flex items-center justify-between">
    <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-store text-blue-600"></i> User Vendor
    </h2>
    <a href="<?= site_url('admin/users/create?role=vendor'); ?>"
      class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm"
      onclick="loadCreateForm('<?= site_url('admin/users/create?role=vendor'); ?>'); return false;">
      <i class="fa fa-plus text-[11px]"></i> Add Vendor
    </a>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full text-xs md:text-sm" data-table-role="vendor">
      <thead class="bg-gradient-to-r from-emerald-600 to-teal-700">
        <tr>
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
        </tr>
      </thead>
      <tbody id="tbody-vendor" class="divide-y divide-gray-100">
        <?php if (!empty($usersVendor)): ?>
          <?php foreach ($usersVendor as $i => $u): 
            $id = (int)($u['id'] ?? 0); 
            $status = strtolower((string)($u['vendor_status'] ?? 'pending'));
            $isSuspended = in_array($status, ['suspended','nonaktif','inactive'], true);
            $suspendLabel = $isSuspended ? 'Unsuspend' : 'Suspend';
            $suspendIcon = $isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';

            // Format komisi
            $commission = '-';
            if ($u['commission_type'] === 'nominal' && !empty($u['requested_commission_nominal'])) {
                $commission = 'Rp ' . number_format($u['requested_commission_nominal'], 0, ',', '.');
            } elseif ($u['commission_type'] === 'percent' && !empty($u['requested_commission'])) {
                $commission = number_format($u['requested_commission'], 1) . '%';
            }
          ?>
            <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.22 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="vendor_<?= $id ?>">
              <td class="px-2 md:px-4 py-2 md:py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc($u['business_name'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc($u['owner_name'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['phone'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['whatsapp_number'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 font-medium"><?= $commission ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3">
                <div class="flex items-center gap-2">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= $status === 'verified' ? 'bg-green-100 text-green-800' : ($status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') ?>">
                    <?= esc(ucfirst($status)) ?>
                  </span>
                  <?php if (isset($u['is_verified']) && $u['is_verified']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      <i class="fa-solid fa-check-circle mr-1"></i> Verified
                    </span>
                  <?php endif; ?>
                </div>
              </td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-right">
                <div class="inline-flex items-center gap-1.5">
                  <button type="button" 
                    class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm edit-user-btn"
                    onclick="loadEditForm('<?= site_url('admin/users/') . $id . '/edit?role=vendor'; ?>')">
                    <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                  </button>
                  <button type="button" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                          data-user-name="<?= esc($u['business_name'] ?? 'Vendor') ?>" data-role="Vendor" onclick="UMDel.open(this)">
                    <i class="fa-regular fa-trash-can text-[11px]"></i> Delete
                  </button>
                  <button type="button" 
                    onclick="toggleSuspendVendor(<?= $id ?>, this)"
                    class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm">
                    <i class="<?= $suspendIcon ?> text-[11px]"></i> 
                    <span><?= $suspendLabel ?></span>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr data-empty-state="true" class="fade-up-soft" style="--delay:.22s">
            <td colspan="10" class="px-4 md:px-6 py-16">
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
<div id="createUserModal"
     class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4"
     x-data="createUserModal" 
     x-show="open"
     x-cloak>
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2"
            @click="close()">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div id="createModalContent" class="p-6">
      <div x-show="loading" class="text-center py-8">
        <i class="fa-solid fa-spinner fa-spin text-blue-600 text-2xl"></i>
        <p class="mt-2 text-gray-600">Memuat form...</p>
      </div>
    </div>
  </div>
</div>

<!-- MODAL EDIT USER -->
<div id="editUserModal"
     class="fixed inset-0 z-[9999] flex items-start justify-center bg-black/50 p-4"
     x-data="editUserModal" 
     x-show="open"
     x-cloak>
  <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl relative mt-10 max-h-[90vh] overflow-y-auto">
    <button type="button"
            class="absolute top-3 right-3 z-10 text-gray-400 hover:text-gray-600 bg-white rounded-full p-2"
            @click="close()">
      <i class="fa-solid fa-times text-lg"></i>
    </button>
    <div id="editModalContent" class="p-6">
      <div x-show="loading" class="text-center py-8">
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
// ===== ALPINE.JS COMPONENTS =====
document.addEventListener('alpine:init', () => {
  // Create Modal Component
  Alpine.data('createUserModal', () => ({
    open: false,
    loading: false,
    
    init() {
      console.log('CreateUserModal component initialized');
    },
    
    async loadCreateForm(url) {
      try {
        this.open = true;
        this.loading = true;
        document.body.style.overflow = 'hidden';
        
        console.log('Loading create form from:', url);
        
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
        this.loading = false;
        
        // Re-initialize Alpine.js components dalam form
        if (window.Alpine) {
          Alpine.initTree(document.getElementById('createModalContent'));
        }
        
      } catch (error) {
        console.error('Error loading create form:', error);
        this.loading = false;
        this.close();
        alert('Gagal memuat form create. Silakan coba lagi.');
      }
    },
    
    close() {
      this.open = false;
      document.body.style.overflow = '';
      document.getElementById('createModalContent').innerHTML = '';
    }
  }));

  // Edit Modal Component
  Alpine.data('editUserModal', () => ({
    open: false,
    loading: false,
    
    init() {
      console.log('EditUserModal component initialized');
    },
    
    async loadEditForm(url) {
      try {
        this.open = true;
        this.loading = true;
        document.body.style.overflow = 'hidden';
        
        console.log('Loading edit form from:', url);
        
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
        this.loading = false;
        
        // Re-initialize Alpine.js components dalam form
        if (window.Alpine) {
          Alpine.initTree(document.getElementById('editModalContent'));
        }
        
      } catch (error) {
        console.error('Error loading edit form:', error);
        this.loading = false;
        this.close();
        alert('Gagal memuat form edit. Silakan coba lagi.');
      }
    },
    
    close() {
      this.open = false;
      document.body.style.overflow = '';
      document.getElementById('editModalContent').innerHTML = '';
    }
  }));

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
    
    closeModal() {
      const createModal = document.querySelector('#createUserModal');
      if (createModal && createModal.__x) {
        createModal.__x.close();
      }
    },
    
    async submitForm(e) {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);
      
      // Validasi password
      const password = formData.get('password');
      const passwordConfirm = formData.get('password_confirm');
      
      if (password !== passwordConfirm) {
        alert('Konfirmasi password tidak sama!');
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (response.ok) {
          window.location.href = '<?= site_url('admin/users?tab=seo') ?>';
        } else {
          const result = await response.json();
          alert(result.message || 'Gagal menyimpan user');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
      } finally {
        this.loading = false;
      }
    }
  }));

  // SEO Edit Form Component
  Alpine.data('editSeoForm', () => ({
    loading: false,
    showResetPassword: false,
    
    closeModal() {
      const editModal = document.querySelector('#editUserModal');
      if (editModal && editModal.__x) {
        editModal.__x.close();
      }
    },
    
    async submitForm(e) {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);
      
      this.loading = true;
      
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (response.ok) {
          window.location.href = '<?= site_url('admin/users?tab=seo') ?>';
        } else {
          const result = await response.json();
          alert(result.message || 'Gagal mengupdate user');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
      } finally {
        this.loading = false;
      }
    }
  }));

  // Vendor Form Component (untuk create)
  Alpine.data('vendorForm', () => ({
    loading: false,
    showPassword: false,
    showConfirmPassword: false,
    
    closeModal() {
      const createModal = document.querySelector('#createUserModal');
      if (createModal && createModal.__x) {
        createModal.__x.close();
      }
    },
    
    async submitForm(e) {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);
      
      // Validasi password
      const password = formData.get('password');
      const passwordConfirm = formData.get('password_confirm');
      
      if (password !== passwordConfirm) {
        alert('Konfirmasi password tidak sama!');
        return;
      }
      
      this.loading = true;
      
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (response.ok) {
          window.location.href = '<?= site_url('admin/users?tab=vendor') ?>';
        } else {
          const result = await response.json();
          alert(result.message || 'Gagal menyimpan user');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
      } finally {
        this.loading = false;
      }
    }
  }));

  // Vendor Edit Form Component
  Alpine.data('editVendorForm', () => ({
    loading: false,
    showResetPassword: false,
    
    closeModal() {
      const editModal = document.querySelector('#editUserModal');
      if (editModal && editModal.__x) {
        editModal.__x.close();
      }
    },
    
    async submitForm(e) {
      e.preventDefault();
      const form = e.target;
      const formData = new FormData(form);
      
      this.loading = true;
      
      try {
        const response = await fetch(form.action, {
          method: 'POST',
          body: formData,
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (response.ok) {
          window.location.href = '<?= site_url('admin/users?tab=vendor') ?>';
        } else {
          const result = await response.json();
          alert(result.message || 'Gagal mengupdate user');
        }
      } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
      } finally {
        this.loading = false;
      }
    }
  }));
});

// ===== GLOBAL FUNCTIONS =====
// Fungsi untuk memuat form create
function loadCreateForm(url) {
  const modal = document.querySelector('#createUserModal');
  
  if (!modal) {
    console.error('Create modal element tidak ditemukan');
    return;
  }
  
  if (modal.__x) {
    modal.__x.loadCreateForm(url);
  } else {
    console.error('Alpine component untuk create modal tidak ditemukan');
    fallbackLoadCreateForm(url);
  }
}

// Fungsi untuk memuat form edit
function loadEditForm(url) {
  const modal = document.querySelector('#editUserModal');
  
  if (!modal) {
    console.error('Edit modal element tidak ditemukan');
    return;
  }
  
  if (modal.__x) {
    modal.__x.loadEditForm(url);
  } else {
    console.error('Alpine component untuk edit modal tidak ditemukan');
    fallbackLoadEditForm(url);
  }
}

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

// ===== SUSPEND FUNCTIONALITY =====
// Fungsi untuk toggle suspend SEO
async function toggleSuspendSeo(userId, button) {
    console.log('Toggle suspend SEO called for user:', userId);
    
    const originalText = button.innerHTML;
    
    try {
        // Tampilkan loading
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        // Gunakan FormData dengan nama field yang benar
        const formData = new FormData();
        // Perbaiki: gunakan nama CSRF token yang benar
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        
        console.log('CSRF Token Name:', '<?= csrf_token() ?>');
        console.log('CSRF Token Value:', '<?= csrf_hash() ?>');
        
        const response = await fetch(`<?= site_url('admin/users/toggle-suspend/') ?>${userId}`, {
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
                updateSuspendUI(userId, result.new_status, result.new_label, 'seo', button);
                showToast(result.message, 'success');
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
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Fungsi untuk toggle suspend Vendor
async function toggleSuspendVendor(userId, button) {
    console.log('Toggle suspend Vendor called for user:', userId);
    
    const originalText = button.innerHTML;
    
    try {
        button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
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
        
        console.log('Response status:', response.status);
        
        if (response.ok) {
            const result = await response.json();
            console.log('Success result:', result);
            
            if (result.success) {
                updateSuspendUI(userId, result.new_status, result.new_label, 'vendor', button);
                showToast(result.message, 'success');
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
        button.innerHTML = originalText;
        button.disabled = false;
    }
}

// Update UI setelah suspend
function updateSuspendUI(userId, newStatus, newLabel, type, button) {
    console.log('Updating UI for user:', userId, 'New status:', newStatus);
    
    // Update status badge
    // Untuk Tim SEO, kita menggunakan data-rowkey dengan format "seo_[id]"
    let row = document.querySelector(`tr[data-rowkey="seo_${userId}"]`);
    if (!row) {
        // Jika tidak ditemukan, coba cari dengan contains
        const rows = document.querySelectorAll(`tr[data-rowkey*="${userId}"]`);
        if (rows.length > 0) {
            row = rows[0];
        }
    }
    
    if (row) {
        const statusCell = row.querySelector('td:nth-child(6)'); // Kolom status adalah kolom ke-6
        if (statusCell) {
            let badge = statusCell.querySelector('span');
            if (!badge) {
                // Jika tidak ada span, buat yang baru
                badge = document.createElement('span');
                statusCell.innerHTML = '';
                statusCell.appendChild(badge);
            }
            
            // Update class berdasarkan status
            const badgeClass = newStatus === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
            badge.className = `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${badgeClass}`;
            badge.textContent = newLabel;
            console.log('Updated badge:', badgeClass, newLabel);
        }
    }
    
    // Update suspend button yang diklik
    if (button) {
        const isSuspended = newStatus === 'suspended' || newStatus === 'inactive';
        const newText = isSuspended ? 'Unsuspend' : 'Suspend';
        const newIcon = isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';
        
        console.log('Updating button:', newText, newIcon);
        
        // Update icon
        const icon = button.querySelector('i');
        if (icon) {
            icon.className = `${newIcon} text-[11px]`;
        }
        
        // Update text
        let textSpan = button.querySelector('span');
        if (textSpan) {
            textSpan.textContent = newText;
        } else {
            // Jika tidak ada span, update seluruh innerHTML
            button.innerHTML = `<i class="${newIcon} text-[11px]"></i> <span>${newText}</span>`;
        }
    }
}

// ===== DELETE FUNCTIONALITY =====
/* ========= User Management Delete (REAL DELETE) ========= */
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
    
    // Set delete URL berdasarkan role
    deleteUrl = `<?= site_url('admin/users/') ?>${userId}/delete`;
    
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
      const response = await fetch(deleteUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': getCsrfToken()
        }
      });

      if (response.ok) {
        // Hapus row dari tabel
        targetRow.remove();
        
        // Tampilkan pesan sukses
        showToast('User berhasil dihapus', 'success');
        
        // Redirect ke tab yang sesuai
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
</script>

<?= $this->include('admin/layouts/footer'); ?>