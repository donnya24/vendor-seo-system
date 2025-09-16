<?php
// File: app/Views/seo/layouts/sidebar.php
?>
<!-- SIDEBAR -->
<aside class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-blue-800 to-blue-900 text-white flex-col z-40 hidden md:flex"
       :class="{'!flex': sidebarOpen}" x-data="{ confirmLogout: false }" x-transition>
  <div class="p-4 text-xl font-bold flex items-center gap-2">
    <i class="fas fa-chart-line"></i> SEO Panel
  </div>
  <nav class="flex-1 space-y-1 text-sm px-2">
    <a href="<?= site_url('seo/dashboard') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='dashboard'?'bg-blue-700':'' ?>">
      <i class="fas fa-gauge-high mr-2"></i> Dashboard
    </a>
    <a href="<?= site_url('seo/targets') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='targets'?'bg-blue-700':'' ?>">
      <i class="fas fa-bullseye mr-2"></i> Targets
    </a>
    <a href="<?= site_url('seo/reports') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='reports'?'bg-blue-700':'' ?>">
      <i class="fas fa-file-lines mr-2"></i> Reports
    </a>
    <a href="<?= site_url('seo/leads') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='leads'?'bg-blue-700':'' ?>">
      <i class="fas fa-users mr-2"></i> Leads
    </a>
    <a href="<?= site_url('seo/commissions') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='commissions'?'bg-blue-700':'' ?>">
      <i class="fas fa-money-bill-wave mr-2"></i> Komisi
    </a>
    <a href="<?= site_url('seo/vendor') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='vendor'?'bg-blue-700':'' ?>">
      <i class="fas fa-building mr-2"></i> Vendors
    </a>
    <a href="<?= site_url('seo/logs') ?>" @click="sidebarOpen=false"
       class="block px-3 py-2 rounded-lg hover:bg-blue-700 <?= ($activeMenu ?? '')==='logs'?'bg-blue-700':'' ?>">
      <i class="fas fa-history mr-2"></i> Log Aktivitas
    </a>
  </nav>

  <!-- Tombol Logout -->
  <div class="p-4">
    <button @click="confirmLogout = true"
      class="w-full px-3 py-2 rounded bg-white/10 hover:bg-white/20 text-left">
      <i class="fas fa-sign-out-alt mr-2"></i> Logout
    </button>
  </div>

  <!-- Modal Konfirmasi Logout -->
  <div x-show="confirmLogout" x-transition
       class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white text-gray-800 rounded-lg shadow-lg w-80 p-6">
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
