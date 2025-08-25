<!-- ===== Sidebar ===== -->
<div
  id="adminSidebar"
  data-turbo-permanent
  class="sidebar z-40 text-white w-60 fixed inset-y-0 left-0 p-3 flex flex-col
         bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900
         transform transition-transform duration-300 ease-in-out no-scrollbar"
  :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  x-cloak
  role="navigation"
  aria-label="Sidebar utama"
  :aria-hidden="sidebarOpen ? 'false' : 'true'"
  @click.outside="if (!isDesktop) sidebarOpen = false"
  x-data="{
    activeMenu: '<?= url_is('admin/dashboard*') ? 'dashboard' : (url_is('admin/users*') ? 'users' : (url_is('admin/vendors*') ? 'vendors' : (url_is('admin/services*') ? 'services' : (url_is('admin/areas*') ? 'areas' : (url_is('admin/leads*') ? 'leads' : (url_is('admin/announcements*') ? 'announcements' : (url_is('admin/audit-logs*') ? 'audit-logs' : ''))))))) ?>',
    setActiveMenu(menu) {
      this.activeMenu = menu;
      // Simpan status menu aktif di sessionStorage
      sessionStorage.setItem('activeMenu', menu);
    },
    init() {
      // Coba muat status menu aktif dari sessionStorage
      const savedMenu = sessionStorage.getItem('activeMenu');
      if (savedMenu) {
        this.activeMenu = savedMenu;
      }
    }
  }"
>
  <!-- Brand + Close (mobile) -->
  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold">
      <span class="hidden md:inline">Admin Imersa</span>
      <span class="md:hidden">Imersa</span>
    </h1>

    <button
      class="md:hidden text-white/90 p-1 rounded-full hover:bg-blue-600"
      @click="sidebarOpen = false"
      aria-label="Tutup sidebar"
    >
      <i class="fas fa-times text-base"></i>
    </button>
  </div>

  <div class="border-b border-white/20 mb-3"></div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto pb-3 -mr-3 pr-3 no-scrollbar">
    <div class="overflow-y-auto max-h-full no-scrollbar">
      <p class="text-blue-200 uppercase text-xs font-bold mb-1 px-2 tracking-wide">MAIN MENU</p>

      <a href="<?= site_url('admin/dashboard'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'dashboard', 'text-white/90': activeMenu !== 'dashboard'}"
         aria-current="<?= url_is('admin/dashboard*') ? 'page' : 'false' ?>"
         @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('dashboard')">
        <i class="fas fa-tachometer-alt mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Dashboard</span>
      </a>

      <a href="<?= site_url('admin/users'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'users', 'text-white/90': activeMenu !== 'users'}"
         @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('users')">
        <i class="fas fa-user-shield mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Management Users</span>
      </a>

      <a href="<?= site_url('admin/vendors'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'vendors', 'text-white/90': activeMenu !== 'vendors'}"
         @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('vendors')">
        <i class="fas fa-users mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Vendors</span>
      </a>

      <a href="<?= site_url('admin/services'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'services', 'text-white/90': activeMenu !== 'services'}"
         @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('services')">
        <i class="fas fa-toolbox mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Services</span>
      </a>

      <a href="<?= site_url('admin/areas'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'areas', 'text-white/90': activeMenu !== 'areas'}"
         @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('areas')">
        <i class="fas fa-map-marker-alt mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Areas</span>
      </a>

      <a href="<?= site_url('admin/leads'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'leads', 'text-white/90': activeMenu !== 'leads'}"
         @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('leads')">
        <i class="fas fa-list mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Leads</span>
      </a>

      <div class="mt-4">
        <p class="text-blue-200 uppercase text-xs font-bold mb-1 px-2 tracking-wide">CONFIGURATION</p>

        <a href="<?= site_url('admin/announcements'); ?>"
           class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
           :class="{'bg-blue-600 text-white': activeMenu === 'announcements', 'text-white/90': activeMenu !== 'announcements'}"
           @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('announcements')">
          <i class="fas fa-bullhorn mr-2 w-4 text-center text-xs"></i>
          <span class="text-sm">Announcements</span>
        </a>

        <a href="<?= site_url('admin/audit-logs'); ?>"
           class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
           :class="{'bg-blue-600 text-white': activeMenu === 'audit-logs', 'text-white/90': activeMenu !== 'audit-logs'}"
           @click="if (!isDesktop) sidebarOpen = false; setActiveMenu('audit-logs')">
          <i class="fas fa-history mr-2 w-4 text-center text-xs"></i>
          <span class="text-sm">Audit Logs</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Bottom -->
  <div class="mt-auto border-t border-blue-700/60 pt-3">
    <button type="button"
            class="block w-full text-left py-2 px-2 rounded-md flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
            @click="showLogoutModal = true; if (!isDesktop) sidebarOpen = false">
      <i class="fas fa-sign-out-alt mr-2 w-4 text-center text-xs"></i>
      <span class="text-sm">Logout</span>
    </button>

    <p class="text-[10px] text-blue-200 mt-4 text-center opacity-80">
      &copy; <?= date('Y'); ?> Imersa. All rights reserved.
    </p>
  </div>
</div>

<script>
// Fungsi untuk menentukan menu aktif berdasarkan URL saat ini
function setInitialActiveMenu() {
  const currentPath = window.location.pathname;
  let activeMenu = '';
  
  if (currentPath.includes('/admin/dashboard')) activeMenu = 'dashboard';
  else if (currentPath.includes('/admin/users')) activeMenu = 'users';
  else if (currentPath.includes('/admin/vendors')) activeMenu = 'vendors';
  else if (currentPath.includes('/admin/services')) activeMenu = 'services';
  else if (currentPath.includes('/admin/areas')) activeMenu = 'areas';
  else if (currentPath.includes('/admin/leads')) activeMenu = 'leads';
  else if (currentPath.includes('/admin/announcements')) activeMenu = 'announcements';
  else if (currentPath.includes('/admin/audit-logs')) activeMenu = 'audit-logs';
  
  // Simpan di sessionStorage
  if (activeMenu) {
    sessionStorage.setItem('activeMenu', activeMenu);
  }
  
  return activeMenu;
}

// Jalankan saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
  setInitialActiveMenu();
});

// Tangani navigasi browser (tombol back/forward)
window.addEventListener('popstate', function() {
  setInitialActiveMenu();
});
</script>