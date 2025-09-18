<?php
// File: app/Views/seo/layouts/sidebar.php
?>
<!-- SIDEBAR -->
<aside 
    x-data="{ confirmLogout: false }"
    class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex-col z-40 hidden md:flex"
>
  <!-- Logo / Judul -->
  <div class="p-4 text-xl font-bold flex items-center gap-2">
    <i class="fas fa-chart-line"></i> SEO Panel
  </div>

  <!-- Navigasi Sidebar -->
  <nav class="flex-1 space-y-1 text-sm px-2">
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
        $active = ($activeMenu ?? '') === $item['key'] ? 'bg-blue-700' : '';
    ?>
    <a href="<?= site_url($item['url']) ?>" 
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= $active ?>">
       <i class="<?= $item['icon'] ?> mr-2"></i> <?= $item['label'] ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <!-- Tombol Logout -->
  <div class="p-4">
    <button @click.stop="confirmLogout = true"
      class="w-full px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-left">
      <i class="fas fa-sign-out-alt mr-2"></i> Logout
    </button>
  </div>

  <!-- Modal Konfirmasi Logout -->
  <div x-show="confirmLogout" x-transition
       x-cloak
       @click.outside="confirmLogout = false"
       class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white text-gray-800 rounded-lg shadow-lg w-80 p-6"
         @click.stop>
      <h2 class="text-lg font-semibold mb-4">Konfirmasi Logout</h2>
      <p class="mb-6">Apakah Anda yakin ingin keluar?</p>
      <div class="flex justify-end gap-3">
        <button @click="confirmLogout = false"
          class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400">
          Batal
        </button>
        <form method="post" action="<?= site_url('logout') ?>">
          <?= csrf_field() ?>
          <button type="submit"
            class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white">
            Keluar
          </button>
        </form>
      </div>
    </div>
  </div>
</aside>
