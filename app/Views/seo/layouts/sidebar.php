<?php
// File: app/Views/seo/layouts/sidebar.php

// Ambil status SEO dari model
 $seoProfileModel = new \App\Models\SeoProfilesModel();
 $seoProfile = $seoProfileModel->getByUserId(user_id()); // Sesuaikan dengan cara Anda mendapatkan user_id
 $isActive = $seoProfile && ($seoProfile['status'] ?? '') === 'active';

// Helper untuk cek menu aktif
if (!function_exists('isActive')) {
    function isActive(...$segments) {
        $cur = current_url();
        foreach ($segments as $seg) {
            if (strpos($cur, site_url($seg)) !== false) return true;
        }
        return false;
    }
}

// Helper untuk render nav item dengan pengecekan status
if (!function_exists('navItem')) {
    function navItem($canAccess, $url, $icon, $label, $active = false) {
        $href = $canAccess ? site_url($url) : 'javascript:void(0)';
        $class = 'flex items-center px-4 py-3 rounded-lg transition-all duration-200 group ';
        $class .= $active ? 'bg-blue-700 ' : 'hover:bg-blue-700/50 ';
        $class .= !$canAccess ? 'opacity-50 cursor-not-allowed ' : 'cursor-pointer ';
        $title = !$canAccess ? ' title="Akun tidak aktif"' : '';

        return '<a href="'.$href.'" class="'.$class.'"'.$title.'>
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-700/30 group-hover:bg-blue-600/30 mr-3">
                    <i class="'.$icon.'"></i>
                </div>
                <span class="font-medium">'.$label.'</span>
                '.($active ? '<div class="ml-auto w-2 h-2 rounded-full bg-white"></div>' : '').'
            </a>';
    }
}
?>

<!-- SIDEBAR -->
<aside 
    x-data="{ confirmLogout: false }"
    class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex-col z-40 transform transition-transform duration-300 ease-in-out"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
  <!-- Logo / Judul -->
  <div class="p-5 border-b border-blue-700">
    <div class="flex items-center gap-3">
      <div class="p-2 bg-blue-700 rounded-lg">
        <i class="fas fa-chart-line text-xl"></i>
      </div>
      <div>
        <h1 class="text-xl font-bold">SEO Panel</h1>
        <p class="text-xs text-blue-300">Admin Dashboard</p>
      </div>
    </div>
  </div>

  <!-- Alert jika akun inactive -->
  <?php if (!$isActive): ?>
  <div class="mx-4 mt-4 p-3 bg-red-500/20 border border-red-500/30 rounded-lg">
    <div class="flex items-start">
      <i class="fas fa-exclamation-triangle text-red-300 text-sm mt-0.5 mr-2"></i>
      <div class="leading-snug">
        <p class="text-red-200 text-sm font-semibold">Akun Tidak Aktif</p>
        <p class="text-red-200/90 text-xs mt-0.5">
          Akses Anda terhadap fitur-fitur dashboard dibatasi.
        </p>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Navigasi Sidebar -->
  <nav class="flex-1 py-4 px-3 overflow-y-auto">
    <div class="space-y-1">
      <?php 
      $menuItems = [
          ['url' => 'seo/dashboard', 'icon' => 'fas fa-gauge-high', 'label' => 'Dashboard', 'key' => 'dashboard'],
          ['url' => 'seo/targets', 'icon' => 'fas fa-bullseye', 'label' => 'Targets', 'key' => 'targets'],
          ['url' => 'seo/reports', 'icon' => 'fas fa-file-lines', 'label' => 'Reports', 'key' => 'reports'],
          ['url' => 'seo/leads', 'icon' => 'fas fa-users', 'label' => 'Leads', 'key' => 'leads'],
          ['url' => 'seo/commissions', 'icon' => 'fas fa-money-bill-wave', 'label' => 'Komisi', 'key' => 'commissions'],
          ['url' => 'seo/vendor', 'icon' => 'fas fa-building', 'label' => 'Vendors', 'key' => 'vendor'],
          ['url' => 'seo/logs', 'icon' => 'fas fa-history', 'label' => 'Log Aktivitas', 'key' => 'logs'],
      ];
      
      foreach($menuItems as $item):
          $active = ($activeMenu ?? '') === $item['key'];
          // Hanya dashboard yang bisa diakses jika inactive, sisanya tidak
          $canAccess = $isActive || $item['key'] === 'dashboard';
          echo navItem($canAccess, $item['url'], $item['icon'], $item['label'], $active);
      endforeach;
      ?>
    </div>
  </nav>

  <!-- Tombol Logout -->
  <div class="p-4 border-t border-blue-700">
    <button @click.stop="confirmLogout = true"
      class="flex items-center w-full px-4 py-3 rounded-lg bg-blue-700/30 hover:bg-blue-700/50 transition-all duration-200 group">
      <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-700/50 group-hover:bg-blue-600/50 mr-3">
        <i class="fas fa-sign-out-alt"></i>
      </div>
      <span class="font-medium">Logout</span>
    </button>
  </div>

  <!-- Modal Konfirmasi Logout -->
  <div x-show="confirmLogout" x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
       x-cloak
       @click.outside="confirmLogout = false"
       class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-96 p-6 transform transition-all"
         @click.stop>
      <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
        <i class="fas fa-sign-out-alt text-red-600 text-2xl"></i>
      </div>
      <h2 class="text-xl font-bold text-center text-gray-800 mb-2">Konfirmasi Logout</h2>
      <p class="text-gray-600 text-center mb-6">Apakah Anda yakin ingin keluar dari sistem?</p>
      <div class="flex justify-center gap-3">
        <button @click="confirmLogout = false"
          class="px-5 py-2.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition">
          Batal
        </button>
        <form method="post" action="<?= site_url('logout') ?>">
          <?= csrf_field() ?>
          <button type="submit"
            class="px-5 py-2.5 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition flex items-center">
            <i class="fas fa-sign-out-alt mr-2"></i>
            Keluar
          </button>
        </form>
      </div>
    </div>
  </div>
</aside>