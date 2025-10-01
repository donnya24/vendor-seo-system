<!-- ===== Sidebar ===== -->
<div
  id="adminSidebar"
  data-turbo-permanent
  class="sidebar z-40 text-white w-60 fixed inset-y-0 left-0 pt-0 pr-3 pb-3 pl-3 flex flex-col
         bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900
         transform transition-transform duration-300 ease-in-out no-scrollbar shadow-xl"
  :class="$store.ui.sidebar ? 'translate-x-0' : '-translate-x-full'"
  x-cloak
  role="navigation"
  aria-label="Sidebar utama"
  :aria-hidden="$store.ui.sidebar ? 'false' : 'true'"
  @click.outside="if (!$store.layout.isDesktop) $store.ui.sidebar = false"
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
  <!-- Admin Area Header -->
  <div class="px-4 py-3 border-b border-blue-600/30">
    <div class="flex items-center justify-between">
      <div class="flex items-center space-x-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
          <i class="fas fa-shield-alt text-white text-sm"></i>
        </div>
        <div>
          <h2 class="text-white font-bold text-sm">Admin Area</h2>
          <p class="text-blue-200 text-xs">Control Panel</p>
        </div>
      </div>
      <button
        class="md:hidden text-white/90 p-1.5 rounded-lg hover:bg-blue-600/50 transition-colors"
        @click="$store.ui.close()"
        aria-label="Tutup sidebar"
        type="button"
      >
        <i class="fas fa-times text-base"></i>
      </button>
    </div>
  </div>

<!-- User Profile -->
<?php 
 $ap = (new \App\Models\AdminProfileModel())->getAdminProfile();
 $profileImage = $ap['profile_image'] ?? '';
 $profileOnDisk = $profileImage ? (FCPATH . 'uploads/admin_profiles/' . $profileImage) : '';
 $profileImagePath = ($profileImage && is_file($profileOnDisk))
  ? base_url('uploads/admin_profiles/' . $profileImage)
  : base_url('assets/img/default-avatar.png');
?>
<div class="px-4 pb-3 border-b border-blue-600/30">
  <div class="flex items-center space-x-3 p-2 bg-blue-700/30 rounded-lg">
    <div class="relative">
      <img src="<?= $profileImagePath ?>" alt="Admin" class="w-10 h-10 rounded-full border-2 border-white/30">
      <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-blue-900"></span>
    </div>
    <div class="flex-1">
      <p class="text-white font-medium text-sm"><?= esc($ap['name'] ?? 'Administrator') ?></p>
      <p class="text-blue-200 text-xs"><?= esc($ap['email'] ?? 'admin@imersa.com') ?></p>
    </div>
  </div>
</div>

  <!-- Nav -->
  <nav class="flex-1 overflow-y-auto pb-3 px-3 -mr-3 pr-3 no-scrollbar">
    <div class="overflow-y-auto max-h-full no-scrollbar">
      <p class="text-blue-200 uppercase text-xs font-bold mb-3 px-2 tracking-wider">MAIN MENU</p>

      <a href="<?= site_url('admin/dashboard'); ?>"
         class="group flex items-center py-3 px-3 rounded-lg mb-1 transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600/30 border-l-4 border-blue-400 text-white shadow-lg': activeMenu === 'dashboard', 'text-white/90': activeMenu !== 'dashboard'}"
         aria-current="<?= url_is('admin/dashboard*') ? 'page' : 'false' ?>"
         @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('dashboard')">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-600/50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-colors">
            <i class="fas fa-tachometer-alt text-white text-sm"></i>
          </div>
          <div>
            <span class="text-sm font-medium">Dashboard</span>
            <p class="text-xs text-blue-200">Overview & Statistics</p>
          </div>
        </div>
      </a>

      <!-- Management Users with Dropdown -->
      <div class="mb-1">
        <button 
          @click="toggleUserSubmenu()"
          class="group w-full flex items-center justify-between py-3 px-3 rounded-lg transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                text-white/90"
          :class="{'bg-blue-600/30 border-l-4 border-blue-400 text-white shadow-lg': activeMenu === 'users', 'text-white/90': activeMenu !== 'users'}"
          aria-expanded="userSubmenu"
          aria-controls="user-submenu"
        >
          <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-600/50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-colors">
              <i class="fas fa-user-shield text-white text-sm"></i>
            </div>
            <div class="text-left">
              <span class="text-sm font-medium">Management Users</span>
              <p class="text-xs text-blue-200">Control user access</p>
            </div>
          </div>
          <i class="fas fa-chevron-down text-xs transition-transform duration-200" 
             :class="{'rotate-180': userSubmenu}"></i>
        </button>
        
        <div 
          id="user-submenu" 
          x-show="userSubmenu" 
          x-collapse
          class="pl-11 mt-1 space-y-1"
        >
          <a href="<?= site_url('admin/users?tab=seo'); ?>"
             class="group flex items-center py-2 px-3 rounded-lg transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                    text-white/90 visited:text-white/90 text-sm"
             :class="{'bg-blue-500/30 border-l-4 border-blue-400 text-white': window.location.search.includes('tab=seo') || (window.location.pathname.includes('/admin/users') && !window.location.search.includes('tab=vendor'))}"
             @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false;">
            <div class="w-6 h-6 bg-blue-600/50 rounded-lg flex items-center justify-center mr-2 group-hover:bg-blue-600 transition-colors">
              <i class="fas fa-users text-white text-xs"></i>
            </div>
            <span class="text-xs font-medium">User Tim SEO</span>
          </a>
          
          <a href="<?= site_url('admin/users?tab=vendor'); ?>"
             class="group flex items-center py-2 px-3 rounded-lg transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                    text-white/90 visited:text-white/90 text-sm"
             :class="{'bg-blue-500/30 border-l-4 border-blue-400 text-white': window.location.search.includes('tab=vendor')}"
             @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false;">
            <div class="w-6 h-6 bg-blue-600/50 rounded-lg flex items-center justify-center mr-2 group-hover:bg-blue-600 transition-colors">
              <i class="fas fa-store text-white text-xs"></i>
            </div>
            <span class="text-xs font-medium">User Vendor</span>
          </a>
        </div>
      </div>

      <a href="<?= site_url('admin/leads'); ?>"
         class="group flex items-center py-3 px-3 rounded-lg mb-1 transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600/30 border-l-4 border-blue-400 text-white shadow-lg': activeMenu === 'leads', 'text-white/90': activeMenu !== 'leads'}"
         @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('leads')">
        <div class="flex items-center">
          <div class="w-8 h-8 bg-blue-600/50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-colors">
            <i class="fas fa-list text-white text-sm"></i>
          </div>
          <div>
            <span class="text-sm font-medium">Leads</span>
            <p class="text-xs text-blue-200">Manage leads data</p>
          </div>
        </div>
      </a>

      <div class="mt-6">
        <p class="text-blue-200 uppercase text-xs font-bold mb-3 px-2 tracking-wider">CONFIGURATION</p>

        <a href="<?= site_url('admin/announcements'); ?>"
           class="group flex items-center py-3 px-3 rounded-lg mb-1 transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                  text-white/90 visited:text-white/90"
           :class="{'bg-blue-600/30 border-l-4 border-blue-400 text-white shadow-lg': activeMenu === 'announcements', 'text-white/90': activeMenu !== 'announcements'}"
           @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('announcements')">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-600/50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-colors">
              <i class="fas fa-bullhorn text-white text-sm"></i>
            </div>
            <div>
              <span class="text-sm font-medium">Announcements</span>
              <p class="text-xs text-blue-200">Send notifications</p>
            </div>
          </div>
        </a>

        <a href="<?= site_url('admin/activity-logs'); ?>"
           class="group flex items-center py-3 px-3 rounded-lg mb-1 transition-all duration-200 hover:bg-blue-700/50 hover:translate-x-1
                  text-white/90 visited:text-white/90"
           :class="{'bg-blue-600/30 border-l-4 border-blue-400 text-white shadow-lg': activeMenu === 'activity-logs', 'text-white/90': activeMenu !== 'activity-logs'}"
           @click="if (!$store.layout.isDesktop) $store.ui.sidebar = false; setActiveMenu('activity-logs')">
          <div class="flex items-center">
            <div class="w-8 h-8 bg-blue-600/50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-blue-600 transition-colors">
              <i class="fas fa-history text-white text-sm"></i>
            </div>
            <div>
              <span class="text-sm font-medium">Activity Logs</span>
              <p class="text-xs text-blue-200">System activities</p>
            </div>
          </div>
        </a>
      </div>
    </div>
  </nav>

  <!-- Bottom -->
  <div class="mt-auto border-t border-blue-700/60 pt-3 px-3">
    <!-- ======= HANYA BAGIAN LOGOUT YANG DIUBAH (teleport modal) ======= -->
    <div x-data="{ showConfirm: false }">
      <!-- tombol utama memunculkan popup -->
      <button
        type="button"
        class="group w-full flex items-center justify-between py-3 px-3 rounded-lg transition-all duration-200 hover:bg-red-600/30 hover:translate-x-1
              text-white/90"
        @click="showConfirm = true; if (!$store.layout?.isDesktop) $store.ui.sidebar = false"
        aria-haspopup="dialog"
        aria-controls="logoutModal"
      >
        <div class="flex items-center">
          <div class="w-8 h-8 bg-red-600/50 rounded-lg flex items-center justify-center mr-3 group-hover:bg-red-600 transition-colors">
            <i class="fas fa-sign-out-alt text-white text-sm"></i>
          </div>
          <div class="text-left">
            <span class="text-sm font-medium">Log out</span>
            <p class="text-xs text-blue-200">Exit admin panel</p>
          </div>
        </div>
        <i class="fas fa-arrow-right text-xs transition-transform group-hover:translate-x-1"></i>
      </button>

      <!-- Popup konfirmasi dipindah ke <body> -->
      <template x-teleport="body">
        <div
          id="logoutModal"
          x-show="showConfirm"
          x-transition.opacity
          @keydown.escape.window="showConfirm = false"
          class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm"
          role="dialog"
          aria-modal="true"
        >
          <!-- klik overlay menutup -->
          <div
            class="absolute inset-0"
            @click="showConfirm = false"
          ></div>

          <div class="relative bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm text-gray-800 transform transition-all"
               x-transition.scale.origin.center>
            <div class="flex items-center justify-center w-12 h-12 bg-red-100 rounded-full mx-auto mb-4">
              <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-center mb-2">Konfirmasi Logout</h2>
            <p class="text-center text-gray-600 mb-6">Apakah anda yakin ingin keluar dari panel admin?</p>
            <div class="flex justify-center gap-3">
              <button
                type="button"
                class="px-6 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium transition-colors"
                @click="showConfirm = false"
              >Batal</button>

              <form method="post" action="<?= site_url('logout'); ?>">
                <?= csrf_field() ?>
                <button
                  type="submit"
                  class="px-6 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors"
                >Keluar</button>
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