<!-- ===== Sidebar ===== -->
<div
  id="adminSidebar"
  data-turbo-permanent
  class="sidebar z-40 text-white w-60 fixed inset-y-0 left-0 pt-0 pr-3 pb-3 pl-3 flex flex-col
         bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900
         transform transition-transform duration-300 ease-in-out no-scrollbar"
  :class="$store.layout.sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  x-cloak
  role="navigation"
  aria-label="Sidebar utama"
  :aria-hidden="$store.layout.sidebarOpen ? 'false' : 'true'"
  @click.outside="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false"
  x-data="{
    activeMenu: '<?= url_is('admin/dashboard*') ? 'dashboard' : (url_is('admin/users*') ? 'users' : (url_is('admin/vendors*') ? 'vendors' : (url_is('admin/services*') ? 'services' : (url_is('admin/areas*') ? 'areas' : (url_is('admin/leads*') ? 'leads' : (url_is('admin/announcements*') ? 'announcements' : (url_is('admin/activity-logs*') ? 'activity-logs' : ''))))))) ?>',
    userSubmenu: false,
    setActiveMenu(menu){ 
      this.activeMenu = menu; 
      sessionStorage.setItem('activeMenu', menu);
      if(menu !== 'users') this.userSubmenu = false;
    },
    toggleUserSubmenu() {
      this.userSubmenu = !this.userSubmenu;
      if(this.userSubmenu) {
        this.activeMenu = 'users';
        sessionStorage.setItem('activeMenu', 'users');
      }
    },
    init(){ 
      const s=sessionStorage.getItem('activeMenu'); 
      if(s) this.activeMenu=s;
      const path = window.location.pathname;
      if(path.includes('/admin/users') || path.includes('/admin/user')) {
        this.activeMenu = 'users';
        this.userSubmenu = true;
      }
    }
  }"
>
  <!-- Brand (sejajar header) + Close (mobile) -->
  <div class="h-14 flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold text-white">
      <span class="hidden md:inline">Admin Imersa</span>
      <span class="md:hidden">Imersa</span>
    </h1>
    <button
      class="md:hidden text-white/90 p-1 rounded-full hover:bg-blue-600"
      @click="$store.layout.close()"
      aria-label="Tutup sidebar"
      type="button"
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
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'dashboard', 'text-white/90': activeMenu !== 'dashboard'}"
         aria-current="<?= url_is('admin/dashboard*') ? 'page' : 'false' ?>"
         @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('dashboard')">
        <i class="fas fa-tachometer-alt mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Dashboard</span>
      </a>

      <!-- Management Users with Dropdown -->
      <div class="mb-1">
        <button 
          @click="toggleUserSubmenu()"
          class="w-full py-2 px-2 rounded-md flex items-center justify-between transition-colors duration-200 hover:bg-blue-700/70
                text-white/90"
          :class="{'bg-blue-600 text-white': activeMenu === 'users', 'text-white/90': activeMenu !== 'users'}"
          aria-expanded="userSubmenu"
          aria-controls="user-submenu"
        >
          <div class="flex items-center">
            <i class="fas fa-user-shield mr-2 w-4 text-center text-xs"></i>
            <span class="text-sm">Management Users</span>
          </div>
          <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
             :class="{'rotate-180': userSubmenu}"></i>
        </button>
        
        <div 
          id="user-submenu" 
          x-show="userSubmenu" 
          x-collapse
          class="pl-6 mt-1 space-y-1"
        >
          <a href="<?= site_url('admin/users?tab=seo'); ?>"
             class="block py-1.5 px-2 rounded-md flex items-center transition-colors duration-200 hover:bg-blue-700/70
                    text-white/90 visited:text-white/90 text-xs"
             :class="{'bg-blue-500/50 text-white': window.location.search.includes('tab=seo') || (window.location.pathname.includes('/admin/users') && !window.location.search.includes('tab=vendor'))}"
             @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false;">
            <i class="fas fa-users mr-2 w-3 text-center"></i>
            <span>User Tim SEO</span>
          </a>
          
          <a href="<?= site_url('admin/users?tab=vendor'); ?>"
             class="block py-1.5 px-2 rounded-md flex items-center transition-colors duration-200 hover:bg-blue-700/70
                    text-white/90 visited:text-white/90 text-xs"
             :class="{'bg-blue-500/50 text-white': window.location.search.includes('tab=vendor')}"
             @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false;">
            <i class="fas fa-store mr-2 w-3 text-center"></i>
            <span>User Vendor</span>
          </a>
        </div>
      </div>

      <a href="<?= site_url('admin/vendors'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'vendors', 'text-white/90': activeMenu !== 'vendors'}"
         @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('vendors')">
        <i class="fas fa-users mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Vendors</span>
      </a>

      <a href="<?= site_url('admin/services'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'services', 'text-white/90': activeMenu !== 'services'}"
         @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('services')">
        <i class="fas fa-toolbox mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Services</span>
      </a>

      <a href="<?= site_url('admin/areas'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'areas', 'text-white/90': activeMenu !== 'areas'}"
         @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('areas')">
        <i class="fas fa-map-marker-alt mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Areas</span>
      </a>

      <a href="<?= site_url('admin/leads'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'leads', 'text-white/90': activeMenu !== 'leads'}"
         @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('leads')">
        <i class="fas fa-list mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Leads</span>
      </a>

      <div class="mt-4">
        <p class="text-blue-200 uppercase text-xs font-bold mb-1 px-2 tracking-wide">CONFIGURATION</p>

        <a href="<?= site_url('admin/announcements'); ?>"
           class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                  text-white/90 visited:text-white/90"
           :class="{'bg-blue-600 text-white': activeMenu === 'announcements', 'text-white/90': activeMenu !== 'announcements'}"
           @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('announcements')">
          <i class="fas fa-bullhorn mr-2 w-4 text-center text-xs"></i>
          <span class="text-sm">Announcements</span>
        </a>

        <a href="<?= site_url('admin/activity-logs'); ?>"
           class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                  text-white/90 visited:text-white/90"
           :class="{'bg-blue-600 text-white': activeMenu === 'activity-logs', 'text-white/90': activeMenu !== 'activity-logs'}"
           @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('activity-logs')">
          <i class="fas fa-history mr-2 w-4 text-center text-xs"></i>
          <span class="text-sm">Activity Logs</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Bottom -->
  <div class="mt-auto border-t border-blue-700/60 pt-3">
    <!-- ======= HANYA BAGIAN LOGOUT YANG DIUBAH (teleport modal) ======= -->
    <div x-data="{ showConfirm: false }">
      <!-- tombol utama memunculkan popup -->
      <button
        type="button"
        class="block w-full text-left py-2 px-2 rounded-md flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
        @click="showConfirm = true; if (!$store.layout?.isDesktop) $store.layout.sidebarOpen = false"
        aria-haspopup="dialog"
        aria-controls="logoutModal"
      >
        <i class="fas fa-sign-out-alt mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Log out</span>
      </button>

      <!-- Popup konfirmasi dipindah ke <body> -->
      <template x-teleport="body">
        <div
          id="logoutModal"
          x-show="showConfirm"
          x-transition.opacity
          @keydown.escape.window="showConfirm = false"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50"
          role="dialog"
          aria-modal="true"
        >
          <!-- klik overlay menutup -->
          <div
            class="absolute inset-0"
            @click="showConfirm = false"
          ></div>

          <div class="relative bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-gray-800"
               x-transition.scale.origin.center>
            <h2 class="text-lg font-semibold mb-3">Konfirmasi</h2>
            <p class="mb-5">Apakah anda yakin ingin log out?</p>
            <div class="flex justify-end gap-3">
              <button
                type="button"
                class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300"
                @click="showConfirm = false"
              >Batal</button>

              <form method="post" action="<?= site_url('logout'); ?>">
                <?= csrf_field() ?>
                <button
                  type="submit"
                  class="px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700"
                >Iya</button>
              </form>
            </div>
          </div>
        </div>
      </template>
    </div>
    <!-- ======= END BAGIAN LOGOUT ======= -->

    <p class="text-[10px] text-blue-200 mt-4 text-center opacity-80">
      &copy; <?= date('Y'); ?> Imersa. All rights reserved.
    </p>
  </div>
</div>
