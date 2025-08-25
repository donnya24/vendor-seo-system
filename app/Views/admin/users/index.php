<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<?php
// ================== Helpers yang lebih robust ==================
if (!function_exists('normalize_groups')) {
  function normalize_groups($user) {
    $g = $user['groups'] ?? [];
    // kalau string: "seoteam,vendor"
    if (is_string($g)) {
      $g = preg_split('/[,\s]+/', $g, -1, PREG_SPLIT_NO_EMPTY);
    }
    $out = [];
    foreach ((array)$g as $item) {
      if (is_string($item)) {
        $out[] = strtolower(trim($item));
      } elseif (is_array($item) && isset($item['name'])) {
        $out[] = strtolower(trim($item['name']));
      }
    }
    return $out;
  }
}
if (!function_exists('in_groups')) {
  function in_groups($user, $names) {
    $groups = normalize_groups($user);
    foreach ((array)$names as $n) {
      if (in_array(strtolower($n), $groups, true)) return true;
    }
    return false;
  }
}
if (!function_exists('user_id')) {
  function user_id($u) {
    return $u['id'] ?? $u['user_id'] ?? $u['uid'] ?? null;
  }
}

// ================== Ambil & bagi data ==================
$hasUsers = isset($users) && is_array($users) && !empty($users);

$usersSeo    = $hasUsers ? array_values(array_filter($users, fn($u) =>
  in_groups($u, ['seoteam','seo','seo_team','team_seo'])
)) : [];

$usersVendor = $hasUsers ? array_values(array_filter($users, fn($u) =>
  in_groups($u, ['vendor'])
)) : [];

// ===== Dummy (opsional) hanya bila backend tidak kirim data =====
$isDummy = !$hasUsers;
if ($isDummy && empty($usersSeo)) {
  $usersSeo = [
    ['id'=>301,'fullname'=>'Alya Prameswari','username'=>'alya.seo','phone'=>'0812-8899-1122','email'=>'alya.prameswari@imersa.test','groups'=>['seoteam']],
    ['id'=>302,'fullname'=>'Bagas Ramadhan','username'=>'bagas.seo','phone'=>'0822-3344-5566','email'=>'bagas.ramadhan@imersa.test','groups'=>['seoteam']],
    ['id'=>303,'fullname'=>'Cindy Oktaviani','username'=>'cindy.seo','phone'=>'0857-7711-0099','email'=>'cindy.oktaviani@imersa.test','groups'=>['seoteam']],
  ];
}
if ($isDummy && empty($usersVendor)) {
  $usersVendor = [
    ['id'=>201,'fullname'=>'Dimas Kurniawan','username'=>'dimas.vendor','phone'=>'0813-2211-3344','email'=>'dimas@vendorx.test','vendor_status'=>'active','groups'=>['vendor']],
    ['id'=>202,'fullname'=>'Elisa Maharani','username'=>'elisa.vendor','phone'=>'0812-6677-8899','email'=>'elisa@vendory.test','vendor_status'=>'suspended','groups'=>['vendor']],
  ];
}
?>

<div class="flex-1 flex flex-col min-h-screen bg-gray-50"
     :class="sidebarOpen && (typeof isDesktop === 'undefined' || isDesktop) ? 'md:ml-64' : 'md:ml-0'">

  <div class="px-4 md:px-6 pt-4 md:pt-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
      <div>
        <h1 class="text-2xl md:text-[26px] font-bold text-gray-900">Users Management</h1>
        <p class="text-sm text-gray-500 mt-0.5">Kelola akun Tim SEO dan Vendor</p>
      </div>
      <div class="flex items-center gap-2 sm:gap-3">
        <a href="<?= site_url('admin/users/create'); ?>"
           class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs md:text-sm px-3 md:px-4 py-2 rounded-lg shadow-sm">
          <i class="fa fa-plus text-[12px]"></i> Add Tim SEO
        </a>
      </div>
    </div>
  </div>

  <main class="flex-1 px-4 md:px-6 pb-6 mt-3 space-y-6">

    <!-- ================== TABEL: USER TIM SEO ================== -->
    <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-users text-blue-600"></i> User Tim SEO
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Nama Lengkap</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Username</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">No. Tlp</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-right text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($usersSeo as $u): ?>
              <?php $id = user_id($u); ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?= esc($id ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($u['fullname'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800"><?= esc($u['phone'] ?? $u['no_telp'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <?php if ($id): ?>
                      <!-- Paksa full reload agar pasti membuka halaman edit (hindari “silent fail” Turbo) -->
                      <a href="<?= site_url('admin/users/') . $id . '/edit'; ?>"
                         data-turbo="false"
                         class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                      </a>
                    <?php else: ?>
                      <span class="inline-flex items-center gap-1.5 bg-gray-300 text-gray-600 text-xs font-semibold px-3 py-1.5 rounded-xl cursor-not-allowed"
                            title="ID user tidak ditemukan">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                      </span>
                    <?php endif; ?>

                    <?php if ($id): ?>
                      <form action="<?= site_url('admin/users/') . $id . '/delete'; ?>" method="post" class="inline"
                            onsubmit="return confirm('Hapus user ini?')">
                        <?= csrf_field() ?>
                        <button class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                          <i class="fa-regular fa-trash-can text-[11px]"></i> Delete
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($usersSeo)): ?>
              <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data Tim SEO.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ================== TABEL: USER VENDOR ================== -->
    <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
      <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
          <i class="fa-solid fa-store text-blue-600"></i> User Vendor
        </h2>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">ID</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Nama Lengkap</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Username</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">No. Tlp</th>
              <th class="px-4 py-3 text-left text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Email</th>
              <th class="px-4 py-3 text-right text-[11px] md:text-xs font-semibold text-white uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php foreach ($usersVendor as $u): ?>
              <?php
                $id          = user_id($u);
                $status      = strtolower((string)($u['vendor_status'] ?? 'active'));
                $isSuspended = in_array($status, ['suspended','nonaktif','inactive'], true);
                $suspendLbl  = $isSuspended ? 'Unsuspend' : 'Suspend';
                $suspendIcon = $isSuspended ? 'fa-regular fa-circle-play' : 'fa-regular fa-circle-pause';
              ?>
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-bold text-gray-900"><?= esc($id ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($u['fullname'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800"><?= esc($u['username'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800"><?= esc($u['phone'] ?? $u['no_telp'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-800"><?= esc($u['email'] ?? '-') ?></td>
                <td class="px-4 py-4 whitespace-nowrap text-right">
                  <div class="inline-flex items-center gap-1.5">
                    <?php if ($id): ?>
                      <a href="<?= site_url('admin/users/') . $id . '/edit'; ?>"
                         data-turbo="false"
                         class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                        <i class="fa-regular fa-pen-to-square text-[11px]"></i> Edit
                      </a>
                      <form action="<?= site_url('admin/users/') . $id . '/delete'; ?>" method="post" class="inline"
                            onsubmit="return confirm('Hapus user ini?')">
                        <?= csrf_field() ?>
                        <button class="inline-flex items-center gap-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                          <i class="fa-regular fa-trash-can text-[11px]"></i> Delete
                        </button>
                      </form>
                      <form action="<?= site_url('admin/users/') . $id . '/toggle-suspend'; ?>" method="post" class="inline"
                            onsubmit="return confirm('<?= $isSuspended ? 'Aktifkan kembali vendor ini?' : 'Suspend vendor ini?' ?>')">
                        <?= csrf_field() ?>
                        <button class="inline-flex items-center gap-1.5 bg-slate-700 hover:bg-slate-800 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                          <i class="<?= $suspendIcon ?> text-[11px]"></i> <?= $suspendLbl ?>
                        </button>
                      </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($usersVendor)): ?>
              <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data Vendor.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
