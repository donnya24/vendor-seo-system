<?php
// APPPATH/Views/vendoruser/layouts/sidebar.php

$vp               = $vp ?? [];
$vendorName       = $vendorName ?? ($vp['business_name'] ?? 'Vendor');
$profileImagePath = $profileImagePath ?? base_url('assets/img/default-avatar.png');
$isVerified       = $isVerified ?? (($vp['status'] ?? '') === 'verified');

// helper: cek active menu
if (!function_exists('isActive')) {
  function isActive(...$segments){
    $cur = current_url();
    foreach ($segments as $seg) {
      if (strpos($cur, site_url($seg)) !== false) return true;
    }
    return false;
  }
}

// helper: render nav item
if (!function_exists('navItem')) {
  function navItem($canAccess, $url, $icon, $label, $active=false, $badge=null){
    $href  = $canAccess ? site_url($url) : 'javascript:void(0)';
    $class = 'block py-2.5 px-4 rounded-xl mb-1.5 flex items-center nav-item transition-all duration-200 text-[13px] ';
    $class .= $active ? 'active bg-white/20 text-white shadow-lg ' : 'text-blue-100 hover:bg-white/10 hover:text-white ';
    $class .= !$canAccess ? 'opacity-50 cursor-not-allowed ' : 'cursor-pointer ';
    $title = !$canAccess ? ' title="Akun belum diverifikasi"' : '';

    $badgeHtml = $badge
      ? '<span class="ml-auto bg-red-500 text-white text-[10px] px-2 py-0.5 rounded-full min-w-[1.25rem] text-center">'.$badge.'</span>'
      : '';

    return '<a href="'.$href.'" class="'.$class.'"'.$title.'
              @click="$store.ui.loading = true; setTimeout(() => { window.location.href=\''.$href.'\' }, 150)">
              <i class="'.$icon.' w-5 mr-3 text-center flex-shrink-0"></i>
              <span class="flex-1 font-medium">'.$label.'</span>'
           . $badgeHtml
           . '</a>';
  }
}
?>

<!-- Sidebar -->
<aside
  class="sidebar fixed inset-y-0 left-0 z-50 w-64 p-4 flex flex-col
         bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 text-white shadow-2xl
         transform transition-transform duration-300 ease-out"
  :class="$store.ui.sidebar ? 'translate-x-0' : '-translate-x-full'"
  @keydown.escape.window="$store.ui.sidebar = false">

  <!-- Header -->
  <div class="flex-shrink-0 px-4 py-6 border-b border-white/10">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
          <i class="fas fa-store text-white text-lg"></i>
        </div>
        <div>
          <h1 class="text-base font-bold text-white leading-tight">Vendor Area</h1>
          <p class="text-blue-200 text-[10px] leading-tight">Partnership Dashboard</p>
        </div>
      </div>
      <!-- ✅ Tombol X khusus mobile -->
      <button
        class="md:hidden p-2 rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/30 transition"
        @click="$store.ui.sidebar = false"
        aria-label="Tutup sidebar">
        <i class="fas fa-times text-white text-lg"></i>
      </button>
    </div>
  </div>

  <!-- Profile -->
  <div class="flex-shrink-0 px-4 py-4 border-b border-white/10">
    <div class="flex items-center space-x-3 p-3 rounded-xl bg-white/10 backdrop-blur-sm">
      <div class="w-12 h-12 rounded-full overflow-hidden bg-white/20 border-2 border-white/30 flex-shrink-0">
        <?php if ($profileImagePath && $profileImagePath !== base_url('assets/img/default-avatar.png')): ?>
          <img src="<?= $profileImagePath ?>" class="w-full h-full object-cover" alt="Profile">
        <?php else: ?>
          <div class="w-full h-full flex items-center justify-center">
            <i class="fas fa-user text-white/70 text-lg"></i>
          </div>
        <?php endif; ?>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-white font-semibold text-[13px] truncate leading-tight"><?= esc($vendorName) ?></p>
        <div class="flex items-center space-x-2 mt-1">
          <?php if ($isVerified): ?>
            <span class="inline-flex items-center text-[10px] bg-green-500/20 text-green-300 px-2 py-0.5 rounded-full">
              <i class="fas fa-check-circle mr-1 text-[10px]"></i> Verified
            </span>
          <?php else: ?>
            <span class="inline-flex items-center text-[10px] bg-yellow-500/20 text-yellow-300 px-2 py-0.5 rounded-full">
              <i class="fas fa-exclamation-circle mr-1 text-[10px]"></i> Pending
            </span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Navigation -->
  <nav class="flex-1 px-4 py-4 overflow-y-auto">
    <div class="mb-5">
      <p class="text-blue-300 uppercase text-[11px] font-bold mb-3 px-2 tracking-wider">Menu Utama</p>

      <?= navItem(true, 'vendoruser/dashboard', 'fas fa-home', 'Dashboard', isActive('vendoruser/dashboard')) ?>

      <?php if (!$isVerified): ?>
        <!-- ✅ Alert verifikasi dipindah tepat di bawah Dashboard -->
        <div class="mt-2 mb-3 px-2">
          <div class="rounded-xl bg-yellow-500/15 border border-yellow-500/30 p-3">
            <div class="flex items-start">
              <i class="fas fa-exclamation-triangle text-yellow-300 text-xs mt-0.5 mr-2"></i>
              <div class="leading-snug">
                <p class="text-yellow-200 text-[12px] font-semibold">Akun Belum Diverifikasi</p>
                <p class="text-yellow-200/90 text-[11px] mt-0.5">
                  Beberapa fitur terbatas sampai akun diverifikasi.
                </p>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?= navItem(
        $isVerified,
        'vendoruser/leads',
        'fas fa-bullseye',
        'Leads Saya',
        isActive('vendoruser/leads'),
        (isset($stats['leads_new']) && $stats['leads_new'] > 0) ? $stats['leads_new'] : null
      ) ?>

      <?= navItem($isVerified, 'vendoruser/services-products', 'fas fa-boxes', 'Layanan & Produk', isActive('vendoruser/services-products')) ?>
      <?= navItem($isVerified, 'vendoruser/areas', 'fas fa-map-marker-alt', 'Area Layanan', isActive('vendoruser/areas')) ?>
      <?= navItem($isVerified, 'vendoruser/commissions', 'fas fa-coins', 'Komisi', isActive('vendoruser/commissions')) ?>
    </div>

    <div class="mb-5">
      <p class="text-blue-300 uppercase text-[11px] font-bold mb-3 px-2 tracking-wider">Aktivitas</p>
      <?= navItem(true, 'vendoruser/activity_logs', 'fas fa-clock-rotate-left', 'Riwayat Aktivitas', isActive('vendoruser/activity_logs')) ?>
    </div>
  </nav>

  <!-- Footer -->
  <div class="flex-shrink-0 px-4 py-4 border-t border-white/10">
    <button
      class="w-full py-2.5 px-4 rounded-xl bg-red-500/20 hover:bg-red-500/30 text-red-200 hover:text-white transition-all duration-200 flex items-center justify-center space-x-2 mb-3 text-[13px]"
      @click.prevent="$store.ui.modal='logout'">
      <i class="fas fa-sign-out-alt"></i><span class="font-medium">Logout</span>
    </button>
    <div class="text-center text-[11px] text-blue-300/70">
      <div>&copy; <?= date('Y') ?> Imersa. All rights reserved.</div>
    </div>
  </div>
</aside>

<!-- Overlay mobile (klik untuk tutup) -->
<div
  x-show="$store.ui.sidebar"
  @click="$store.ui.sidebar = false"
  class="fixed inset-0 z-40 md:hidden bg-black/40"
  x-transition.opacity
  style="display:none;">
</div>
