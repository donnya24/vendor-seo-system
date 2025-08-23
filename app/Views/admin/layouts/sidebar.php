<!-- Sidebar -->
<div
  class="sidebar text-white w-64 fixed inset-y-0 left-0 p-4 flex flex-col bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900 transform transition-transform duration-300 ease-in-out md:translate-x-0 no-scrollbar"
  :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
  x-cloak
  @click.outside="if (window.innerWidth < 768) sidebarOpen = false"
>
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold flex items-center">
      <i class="fas fa-chart-line mr-2"></i> 
      <span class="hidden md:inline">Admin Imersa</span>
      <span class="md:hidden">Imersa</span>
    </h1>
    <button class="md:hidden text-white/90 p-2 rounded-full hover:bg-blue-600 z-50" 
            @click="sidebarOpen = false" 
            aria-label="Tutup sidebar">
      <i class="fas fa-times text-lg"></i>
    </button>
  </div>

  <div class="border-b border-white/20 mb-4"></div>

  <nav class="flex-1 overflow-y-auto pb-4 -mr-4 pr-4 no-scrollbar">
    <!-- Scrollable navigation -->
    <div class="overflow-y-auto max-h-full no-scrollbar">
      <p class="text-blue-200 uppercase text-xs font-semibold mb-2 px-3">Main Menu</p>

      <a href="<?= site_url('admin/dashboard'); ?>"
         class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/dashboard*') ? 'active' : 'hover:bg-blue-700/70'; ?>"
         aria-current="<?= url_is('admin/dashboard*') ? 'page' : 'false' ?>">
        <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i> 
        <span>Dashboard</span>
      </a>

      <a href="<?= site_url('admin/users'); ?>"
         class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/users*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
        <i class="fas fa-user-shield mr-3 w-5 text-center"></i> 
        <span>Management Users</span>
      </a>

      <a href="<?= site_url('admin/vendors'); ?>"
         class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/vendors*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
        <i class="fas fa-users mr-3 w-5 text-center"></i> 
        <span>Vendors</span>
      </a>

      <a href="<?= site_url('admin/services'); ?>"
         class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/services*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
        <i class="fas fa-toolbox mr-3 w-5 text-center"></i> 
        <span>Services</span>
      </a>

      <a href="<?= site_url('admin/areas'); ?>"
         class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/areas*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
        <i class="fas fa-map-marker-alt mr-3 w-5 text-center"></i> 
        <span>Areas</span>
      </a>

      <a href="<?= site_url('admin/leads'); ?>"
         class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item <?= url_is('admin/leads*') ? 'active' : 'hover:bg-blue-700/70'; ?>">
        <i class="fas fa-list mr-3 w-5 text-center"></i> 
        <span>Leads</span>
      </a>

      <div class="mt-6">
        <p class="text-blue-200 uppercase text-xs font-semibold mb-2 px-3">Configuration</p>

        <a href="<?= site_url('admin/announcements'); ?>"
           class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item hover:bg-blue-700/70">
          <i class="fas fa-bullhorn mr-3 w-5 text-center"></i> 
          <span>Announcements</span>
        </a>

        <a href="#"
           class="block py-3 px-3 rounded-lg mb-1 flex items-center nav-item hover:bg-blue-700/70">
          <i class="fas fa-history mr-3 w-5 text-center"></i> 
          <span>Audit Logs</span>
        </a>
      </div>
    </div>
  </nav>

  <div class="mt-auto border-t border-blue-700/60 pt-4">
    <button type="button"
            class="block w-full text-left py-3 px-3 rounded-lg flex items-center nav-item hover:bg-blue-700/70"
            @click="showLogoutModal = true">
      <i class="fas fa-sign-out-alt mr-3 w-5 text-center"></i> 
      <span>Logout</span>
    </button>

    <p class="text-[11px] text-blue-200 mt-6 text-center opacity-80">
      &copy; <?= date('Y'); ?> Imersa. All rights reserved.
    </p>
  </div>
</div>