<!-- Sidebar -->
<aside class="text-white w-64 fixed md:static h-full md:h-auto p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900 z-30"
       :class="{'-ml-64': !sidebarOpen, 'ml-0': sidebarOpen}"
       x-cloak>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-xl font-bold flex items-center">
      <i class="fa-solid fa-store mr-2"></i> Vendor Panel
    </h1>
    <button class="md:hidden" @click="sidebarOpen=false"><i class="fa-solid fa-times"></i></button>
  </div>

  <nav class="flex-1">
    <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Menu</p>

    <a href="<?= site_url('vendor/dashboard'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/dashboard*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-gauge mr-3"></i> Dashboard
    </a>

    <a href="<?= site_url('vendor/profile'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/profile*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-id-card mr-3"></i> Profil
    </a>

    <a href="<?= site_url('vendor/products'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/products*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-box mr-3"></i> Produk
    </a>

    <a href="<?= site_url('vendor/services'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/services*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-toolbox mr-3"></i> Layanan
    </a>

    <a href="<?= site_url('vendor/areas'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/areas*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-map-location-dot mr-3"></i> Area
    </a>

    <a href="<?= site_url('vendor/leads'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/leads*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-list-check mr-3"></i> Leads
    </a>

    <a href="<?= site_url('vendor/commissions'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('vendor/commissions*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-money-bill-wave mr-3"></i> Komisi
    </a>

    <a href="<?= site_url('vendor/notifications'); ?>"
       class="block py-2 px-3 rounded-lg mb-6 flex items-center nav-item <?= url_is('vendor/notifications*')?'active':'hover:bg-blue-700/70' ?>">
      <i class="fa-solid fa-bell mr-3"></i> Notifikasi
    </a>
  </nav>

  <div class="mt-auto border-t border-blue-700/60 pt-4">
    <button type="button" class="w-full text-left py-2 px-3 rounded-lg flex items-center nav-item hover:bg-blue-700/70"
            @click="openLogout()">
      <i class="fa-solid fa-right-from-bracket mr-3"></i> Logout
    </button>
    <p class="text-[11px] text-blue-200 mt-6 text-center opacity-80">
      &copy; <?= date('Y'); ?> Imersa. All rights reserved.
    </p>
  </div>
</aside>
