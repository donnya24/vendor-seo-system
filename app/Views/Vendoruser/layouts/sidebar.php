<!-- Sidebar -->
<aside class="sidebar text-white w-64 fixed h-full p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900"
       :class="{'-ml-64': !$store.ui.sidebar}">
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold flex items-center">
      <i class="fas fa-store mr-2"></i> Vendor Area
    </h1>
    <button class="md:hidden" @click="$store.ui.sidebar=false" aria-label="Tutup sidebar">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="border-b border-white/20 my-3"></div>

  <nav class="flex-1 text-sm">
    <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Main Menu</p>

    <a href="<?= site_url('vendoruser/dashboard') ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= current_url() == site_url('vendoruser/dashboard') ? 'active' : '' ?>">
      <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
    </a>

    <a href="<?= site_url('vendoruser/leads') ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= strpos(current_url(), site_url('vendoruser/leads')) !== false ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40' ?>">
      <i class="fas fa-bullseye mr-3"></i> Leads Saya
    </a>

    <a href="<?= site_url('vendoruser/services') ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= strpos(current_url(), site_url('vendoruser/services')) !== false ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40' ?>">
      <i class="fas fa-toolbox mr-3"></i> Layanan
    </a>

    <a href="<?= site_url('vendoruser/areas') ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= strpos(current_url(), site_url('vendoruser/areas')) !== false ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40' ?>">
      <i class="fas fa-map-marker-alt mr-3"></i> Area
    </a>

    <a href="<?= site_url('vendoruser/products') ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= strpos(current_url(), site_url('vendoruser/products')) !== false ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40' ?>">
      <i class="fas fa-boxes mr-3"></i> Produk
    </a>

    <a href="<?= site_url('vendoruser/commissions') ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= strpos(current_url(), site_url('vendoruser/commissions')) !== false ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40' ?>">
      <i class="fas fa-coins mr-3"></i> Komisi
    </a>

    <a href="<?= site_url('vendoruser/activity_logs') ?>"
      class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= strpos(current_url(), site_url('vendoruser/activity')) !== false ? 'active' : 'hover:bg-blue-700/70 focus:ring-2 focus:ring-blue-300/40' ?>">
      <i class="fas fa-clock-rotate-left mr-3"></i> Histori Aktivitas
    </a>
  </nav>

  <!-- Logout -->
  <div class="mt-auto">
    <button class="block w-full text-left py-2 px-3 rounded-lg flex items-center nav-item hover:bg-white/10"
            @click.prevent="$store.ui.modal='logout'">
      <i class="fas fa-sign-out-alt mr-3"></i> Logout
    </button>
    
    <!-- Copyright Imersa -->
    <div class="text-center text-xs text-blue-200/70 mt-4">
      &copy; <?= date('Y') ?> Imersa. All rights reserved.
    </div>
  </div>

  <!-- Modal Logout -->
  <div x-show="$store.ui.modal==='logout'" x-transition.opacity class="fixed inset-0 z-50">
    <div class="min-h-screen flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-black/40" @click="$store.ui.modal=null"></div>
      <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
        <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
          <i class="fa-solid fa-right-from-bracket text-2xl"></i>
        </div>
        <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
        <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>
        <div class="mt-6 flex items-center justify-center gap-3">
          <button class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                  @click="$store.ui.modal=null">Batal</button>
          <form action="<?= site_url('logout') ?>" method="post">
            <?= csrf_field() ?>
            <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Ya, Keluar</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</aside>

<!-- Alpine Stores (inject data dari server) -->
<script>
Alpine.store('app', {
  stats: <?= isset($stats) ? json_encode($stats) : '{}' ?>,
  recentLeads: <?= isset($recentLeads) ? json_encode($recentLeads) : '[]' ?>,
  topKeywords: <?= isset($topKeywords) ? json_encode($topKeywords) : '[]' ?>,
  unread: <?= isset($stats['unread']) ? (int)$stats['unread'] : 0 ?>,
  init() { 
    console.log('Vendor Dashboard initialized');
  }
});
</script>
</body>
</html>
