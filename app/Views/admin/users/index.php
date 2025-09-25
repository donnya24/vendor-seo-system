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
          $expr = 'COALESCE(' . implode(',', array_map(fn($c)=>"`$c`", $present)) . ') AS phone';
          $rows = $db->table('vendor_profiles')
            ->select("user_id, $expr")
            ->whereIn('user_id', $vendorIds)
            ->get()->getResultArray();

          foreach ($rows as $r) {
            if (!empty($r['phone'])) $vendorPhoneById[(int)$r['user_id']] = (string)$r['phone'];
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
        $groups = $user['groups'] ?? [];
        
        if (in_array('seoteam', $groups, true)) {
            $usersSeo[] = $user;
        }

        if (in_array('vendor', $groups, true)) {
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

<div id="pageWrap" class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out" :class="(sidebarOpen && (typeof isDesktop==='undefined' || isDesktop)) ? 'md:ml-64' : 'ml-0'">
  <!-- Header -->
  <div class="px-3 md:px-6 pt-4 md:pt-6 max-w-screen-xl mx-auto w-full fade-up-soft" style="--delay:.02s">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-lg md:text-xl font-bold text-gray-900">Users Management</h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO dan Vendor</p>
      </div>
    </div>
  </div>

  <!-- Main -->
  <main id="pageMain" class="flex-1 px-3 md:px-6 pb-6 mt-3 space-y-6 max-w-screen-xl mx-auto w-full fade-up" style="--dur:.60s; --delay:.06s">
    
    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 fade-up" style="--delay:.08s">
      <button class="py-2 px-4 text-sm font-medium rounded-t-lg transition-colors duration-200 <?= $currentTab == 'seo' ? 'tab-active' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>" 
              onclick="window.location.href='<?= site_url('admin/users?tab=seo'); ?>'">
        <i class="fas fa-users mr-2"></i> User Tim SEO
      </button>
      <button class="py-2 px-4 text-sm font-medium rounded-t-lg transition-colors duration-200 <?= $currentTab == 'vendor' ? 'tab-active' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50' ?>" 
              onclick="window.location.href='<?= site_url('admin/users?tab=vendor'); ?>'">
        <i class="fas fa-store mr-2"></i> User Vendor
      </button>
    </div>

   <!-- Dalam bagian tabel SEO -->
<?php if ($currentTab == 'seo'): ?>
<section class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.12s">
  <div class="px-3 py-2 md:px-4 md:py-3 border-b border-gray-100 flex items-center justify-between">
    <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
      <i class="fa-solid fa-users text-blue-600"></i> User Tim SEO
    </h2>
    <a href="<?= site_url('admin/users/create?role=seoteam'); ?>" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
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
          <?php foreach ($usersSeo as $i => $u): $id = (int)($u['id'] ?? 0); ?>
            <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.16 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="seo_<?= $id ?>">
              <td class="px-2 md:px-4 py-2 md:py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc($u['name'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['phone'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
              <td class="px-2 md:px-4 py-2 md:py-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?= ($u['seo_status'] ?? 'active') === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                  <?= esc(ucfirst($u['seo_status'] ?? 'active')) ?>
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
                  <form method="post" action="<?= site_url('admin/users/toggle-suspend-seo/' . $id) ?>" class="inline">
                    <button type="submit" class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm">
                      <i class="fa-regular fa-<?= ($u['seo_status'] ?? 'active') === 'active' ? 'pause' : 'play' ?> text-[11px]"></i> 
                      <?= ($u['seo_status'] ?? 'active') === 'active' ? 'Suspend' : 'Unsuspend' ?>
                    </button>
                  </form>
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
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-xs md:text-sm" data-table-role="vendor">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">NAMA LENGKAP</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">USERNAME</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">NO. TLP</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-left font-semibold text-white uppercase tracking-wider">EMAIL</th>
              <th class="px-2 md:px-4 py-2 md:py-3 text-right font-semibold text-white uppercase tracking-wider">AKSI</th>
            </tr>
          </thead>
          <tbody id="tbody-vendor" class="divide-y divide-gray-100">
            <?php if (!empty($usersVendor)): ?>
              <?php foreach ($usersVendor as $i => $u): $id = (int)user_id($u); $rk = row_key('vendor',$u,$i);
                $status= strtolower((string)($u['vendor_status'] ?? 'active'));
                $isSus = in_array($status, ['suspended','nonaktif','inactive'], true);
                $sLbl  = $isSus ? 'Unsuspend' : 'Suspend';
                $sIcon = $isSus ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';

                // Phone: dari users(phone/no_telp) -> vendor_profiles -> '-'
                $phoneVal = $u['phone'] ?? ($u['no_tlp'] ?? ($vendorPhoneById[$id] ?? '-'));
                // Email: dari auth_identities
                $emailVal = $emailById[$id] ?? ($u['email'] ?? '-');
              ?>
                <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= number_format(0.22 + 0.03*$i, 2, '.', '') ?>s" data-rowkey="<?= esc($rk) ?>">
                  <td class="px-2 md:px-4 py-2 md:py-3 font-semibold text-gray-900"><?= esc($id ?: '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-900"><?= esc(full_name_of($u)) ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800 js-username"><?= esc($u['username'] ?? '-') ?></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><span class="js-phone"><?= esc($phoneVal) ?></span></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-gray-800"><span class="js-email"><?= esc($emailVal) ?></span></td>
                  <td class="px-2 md:px-4 py-2 md:py-3 text-right">
                    <div class="inline-flex items-center gap-1.5">
                        <a href="<?= site_url('admin/users/') . $id . '/edit?role=vendor'; ?>" 
                          class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm edit-user-btn">
                          <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                        </a>
                      <button type="button" class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                              data-user-name="<?= esc(full_name_of($u)) ?>" data-role="Vendor" onclick="UMDel.open(this)">
                        <i class="fa-regular fa-trash-can text-[11px]"></i> Delete
                      </button>
                      <button type="button" class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-[11px] md:text-xs font-semibold px-2.5 md:px-3 py-1.5 rounded-lg shadow-sm"
                              data-state="<?= $isSus ? 'suspended' : 'active' ?>"
                              onclick="(function(btn){const i=btn.querySelector('i');const t=btn.querySelector('span');const st=btn.getAttribute('data-state')||'active';if(st==='active'){btn.setAttribute('data-state','suspended');if(i)i.className='fa-regular fa-circle-play text-[11px]';if(t)t.textContent='Unsuspend';}else{btn.setAttribute('data-state','active');if(i)i.className='fa-regular fa-circle-pause text-[11px]';if(t)t.textContent='Suspend';}})(this)">
                        <i class="<?= $sIcon ?> text-[11px]"></i> <span><?= $sLbl ?></span>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr data-empty-state="true" class="fade-up-soft" style="--delay:.22s">
                <td colspan="6" class="px-4 md:px-6 py-16">
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

<!-- POPUP DELETE (single sentence) -->
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
<!-- ================= MODAL EDIT INLINE ================= -->
<div id="editUserModal" class="fixed inset-0 z-[9999] flex items-start justify-center p-3 sm:p-4 hidden"
     x-data="editUserModal()">
  <div class="absolute inset-0 bg-black/60" @click="close()"></div>
  
  <div class="relative w-full sm:max-w-xl md:max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col"
       x-show="open" x-transition.scale.origin.top>
    <!-- Modal content akan di-load via AJAX -->
    <div id="editModalContent" class="flex-1 overflow-y-auto">
      <!-- Content akan diisi via AJAX -->
    </div>
  </div>
</div>

<script>
// Fungsi global untuk memuat form edit
function loadEditForm(url) {
  const modal = document.querySelector('#editUserModal');
  const alpineComponent = modal.__x;
  
  if (alpineComponent) {
    alpineComponent.loadEditForm(url);
  }
}

// Komponen Alpine untuk modal edit
function editUserModal() {
  return {
    open: false,
    loading: false,
    
    init() {
      // Event listener untuk tombol edit
      document.addEventListener('click', (e) => {
        const editBtn = e.target.closest('button[onclick*="loadEditForm"]');
        if (editBtn) {
          e.preventDefault();
          // Tidak perlu memanggil loadEditForm di sini karena sudah menggunakan onclick
        }
      });
    },
    
    async loadEditForm(url) {
      try {
        this.open = true;
        this.loading = true;
        document.body.style.overflow = 'hidden';
        
        const response = await fetch(url, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        });
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const html = await response.text();
        
        // Langsung gunakan HTML yang diterima
        document.getElementById('editModalContent').innerHTML = html;
        this.loading = false;
        
        // Re-initialize Alpine.js components
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
  }
}

// Initialize modal ketika halaman load
document.addEventListener('DOMContentLoaded', function() {
  // Tambahkan event listener untuk semua tombol edit
  document.querySelectorAll('button[onclick*="loadEditForm"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      
      const modal = document.querySelector('#editUserModal');
      const alpineComponent = modal.__x;
      
      if (alpineComponent) {
        // Ekstrak URL dari atribut onclick
        const onclickAttr = this.getAttribute('onclick');
        const urlMatch = onclickAttr.match(/loadEditForm\('([^']+)'\)/);
        
        if (urlMatch && urlMatch[1]) {
          alpineComponent.loadEditForm(urlMatch[1]);
        }
      }
    });
  });
});
</script>

<script>
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
</script>

<script>
/* ========= User Management Delete (Front-End Only) ========= */
window.UMDel = (function () {
  const STORAGE_KEY = 'userMgmtHidden_v5';
  const EVENTS = ['DOMContentLoaded','turbo:load','turbolinks:load','pageshow','htmx:afterSettle'];
  const modal  = document.getElementById('confirmDelete');
  const nameEl = document.getElementById('cdName');
  const yesEl  = document.getElementById('cdYes');
  const noEl   = document.getElementById('cdNo');
  const xEl    = document.getElementById('cdClose');
  const overlay= modal?.querySelector('[data-overlay]');
  let targetRow = null;
  let lastCloseTs = 0;

  function state(){ try{ const s = JSON.parse(localStorage.getItem(STORAGE_KEY)||'{}'); return (s&&typeof s==='object')?s:{seo:{},vendor:{}};}catch{return {seo:{},vendor:{}};} }
  function save(s){ try{ localStorage.setItem(STORAGE_KEY, JSON.stringify(s)); }catch{} }
  function roleOf(row){ const r=row.closest('table')?.getAttribute('data-table-role')||'seo'; return r==='vendor'?'vendor':'seo'; }
  function ensureEmpty(tbody,title,subtitle){
    const rows=[...tbody.querySelectorAll('tr[data-rowkey]')]; const empty=tbody.querySelector('[data-empty-state="true"]');
    if(rows.length===0 && !empty){
      const tr=document.createElement('tr'); tr.setAttribute('data-empty-state','true');
      tr.innerHTML=`<td colspan="${roleOf(tbody)==='seo' ? 7 : 6}" class="px-4 md:px-6 py-16"><div class="flex flex-col items-center justify-center text-center"><div class="w-14 h-14 rounded-2xl bg-gray-100 grid place-items-center"><i class="fa-solid fa-bullhorn text-xl text-gray-400"></i></div><p class="mt-3 text-base md:text-lg font-semibold text-gray-400">${title}</p><p class="text-sm text-gray-400">${subtitle}</p></div></td>`;
      tbody.appendChild(tr);
    } else if(rows.length>0 && empty){ empty.remove(); }
  }
  function apply(){
    const s=state();
    [['tbody-seo','seo','Tidak ada data Tim SEO','Buat user Tim SEO baru untuk memulai'],
     ['tbody-vendor','vendor','Tidak ada data Vendor','Tambahkan user vendor untuk memulai']].forEach(([id, role, t, sub])=>{
      const tb=document.getElementById(id); if(!tb) return;
      [...tb.querySelectorAll('tr[data-rowkey]')].forEach(tr=>{ const key=tr.getAttribute('data-rowkey'); if(s[role]&&s[role][key]) tr.remove(); });
      ensureEmpty(tb,t,sub);
    });
  }
  EVENTS.forEach(ev=>window.addEventListener(ev,apply));

  function open(btn){
    if (Date.now()-lastCloseTs<250) return;
    const row=btn.closest('tr[data-rowkey]'); if(!row) return;
    targetRow=row; nameEl.textContent = btn.getAttribute('data-user-name') || 'User';
    document.documentElement.style.overflow='hidden'; modal.classList.remove('modal-hidden');
  }
  function close(){ modal.classList.add('modal-hidden'); document.documentElement.style.overflow=''; targetRow=null; lastCloseTs=Date.now(); }
  function confirm(){
    if(!targetRow) return;
    const key=targetRow.getAttribute('data-rowkey'); const role=roleOf(targetRow); const s=state(); if(!s[role]) s[role]={}; s[role][key]=true; save(s);
    const tbody=targetRow.closest('tbody'); targetRow.remove(); close();
    if (role==='seo') ensureEmpty(tbody,'Tidak ada data Tim SEO','Buat user Tim SEO baru untuk memulai');
    else ensureEmpty(tbody,'Tidak ada data Vendor','Tambahkan user vendor untuk memulai');
  }
  if(yesEl) yesEl.addEventListener('click',(e)=>{ e.stopPropagation(); confirm(); },true);
  if(noEl)  noEl.addEventListener('click',(e)=>{ e.stopPropagation(); close();   },true);
  if(xEl)   xEl.addEventListener('click',(e)=>{ e.stopPropagation(); close();   },true);
  if(overlay) overlay.addEventListener('click',(e)=>{ e.stopPropagation(); close(); },true);
  document.addEventListener('keydown',(e)=>{ if(modal.classList.contains('modal-hidden')) return; if(e.key==='Escape'){e.preventDefault();close();} if(e.key==='Enter'){e.preventDefault();confirm();} });
  return { open, close };
})();
</script>

<?= $this->include('admin/layouts/footer'); ?>