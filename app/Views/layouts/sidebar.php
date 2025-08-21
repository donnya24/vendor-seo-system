<!-- Sidebar -->
<div class="sidebar text-white w-64 fixed h-full p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900"
     :class="{ '-ml-64': !sidebarOpen }"
     x-show="sidebarOpen"
     @click.outside="sidebarOpen = false">

  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-bold flex items-center">
      <i class="fas fa-chart-line mr-2"></i> Imersa
    </h1>
    <button @click="sidebarOpen = false" class="md:hidden">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="border-b border-white/20 my-3"></div>

  <nav class="flex-1">
    <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Main Menu</p>

    <!-- Dashboard -->
    <a href="<?= site_url('admin/dashboard'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/dashboard*') ? 'active' : 'hover:bg-blue-700/70'; ?>"
       aria-current="<?= url_is('admin/dashboard*') ? 'page' : 'false' ?>">
      <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
    </a>

    <!-- Vendors -->
    <a href="<?= site_url('admin/vendors'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/vendors*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
      <i class="fas fa-users mr-3"></i> Vendors
    </a>

    <!-- Services -->
    <a href="<?= site_url('admin/services'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/services*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
      <i class="fas fa-toolbox mr-3"></i> Services
    </a>

    <!-- Areas -->
    <a href="<?= site_url('admin/areas'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/areas*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
      <i class="fas fa-map-marker-alt mr-3"></i> Areas
    </a>

    <!-- Leads -->
    <a href="<?= site_url('admin/leads'); ?>"
       class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/leads*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
      <i class="fas fa-list mr-3"></i> Leads
    </a>

    <div class="mt-6">
      <p class="text-blue-200 uppercase text-xs font-semibold mb-2">Configuration</p>

      <a href="#"
         class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item hover:bg-blue-700/70">
        <i class="fas fa-bullhorn mr-3"></i> Announcements
      </a>

      <a href="#"
         class="block py-2 px-3 rounded-lg mb-1 flex items-center nav-item hover:bg-blue-700/70">
        <i class="fas fa-history mr-3"></i> Audit Logs
      </a>
    </div>
  </nav>

  <div class="pt-4 border-t border-blue-700/60">
    <a href="#" @click.prevent="openLogoutModal()"
       class="block py-2 px-3 rounded-lg flex items-center nav-item hover:bg-blue-700/70">
      <i class="fas fa-sign-out-alt mr-3"></i> Logout
    </a>
  </div>
</div>
