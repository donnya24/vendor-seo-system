<!-- ===== Utility x-cloak & Guard Sidebar Colors ===== -->
<style>
  [x-cloak]{display:none!important}

  /* Kunci warna elemen di dalam sidebar */
  #adminSidebar a{color:rgba(255,255,255,.9)!important}
  #adminSidebar a:hover{color:#fff!important}
  #adminSidebar a[aria-current="page"]{color:#fff!important}
  #adminSidebar p.text-blue-200{color:rgb(191 219 254 / 1)!important}
  #adminSidebar i{color:inherit!important}

  /* FIX: pastikan brand title selalu putih */
  #adminSidebar h1,
  #adminSidebar h1 span { color:#fff !important; }
</style>

<script>
  // ===== Global Alpine Store untuk Layout (sidebar) & UI (logout modal) =====
  document.addEventListener('alpine:init', () => {
    const mq = () => window.matchMedia('(min-width: 768px)').matches;

    // Store layout: kendali sidebar & media query
    Alpine.store('layout', {
      sidebarOpen: mq(),       // desktop = open, mobile = closed
      isDesktop: mq(),
      open(){ this.sidebarOpen = true },
      close(){ this.sidebarOpen = false },
      toggle(){ this.sidebarOpen = !this.sidebarOpen },
      _updateMQ(){
        const d = mq();
        // hanya ubah isDesktop; buka otomatis saat jadi desktop
        if (d !== this.isDesktop) {
          this.isDesktop = d;
          if (d) this.sidebarOpen = true; // auto open on desktop
        }
      }
    });

    // Store ui: kendali logout modal
    if (!Alpine.store('ui')) Alpine.store('ui', {});
    Alpine.store('ui').showLogoutModal = false;
  });

  // Sinkronkan perubahan viewport
  function _bindSidebarMQ(){
    const onResize = () => { try { Alpine.store('layout')._updateMQ(); } catch(e){} };
    window.addEventListener('resize', onResize);
    window.addEventListener('orientationchange', onResize);
  }
  document.addEventListener('DOMContentLoaded', _bindSidebarMQ);

  // Hook tombol header: cukup beri atribut data-toggle-sidebar pada tombolnya
  function _wireHeaderToggle(){
    const toggle = (e)=>{ e.preventDefault?.(); try { Alpine.store('layout').toggle(); } catch(e){} };
    // cari semua pemicu
    const selectors = ['[data-toggle-sidebar]','#sidebarToggle','#btnSidebarToggle','#headerMenuBtn'];
    const nodes = document.querySelectorAll(selectors.join(','));
    nodes.forEach(el => el.addEventListener('click', toggle));
  }
  document.addEventListener('DOMContentLoaded', _wireHeaderToggle);

  // Reset modal logout (JANGAN set attribute hidden agar x-show bisa bekerja)
  function resetLogoutModal(){
    try{
      if(window.Alpine && Alpine.store('ui')) Alpine.store('ui').showLogoutModal = false;
      document.documentElement.classList.remove('overflow-hidden','error','error-theme','with-sidebar-fallback');
      const m=document.getElementById('logoutModal');
      if(m) m.removeAttribute('hidden'); // pastikan tidak tersangkut 'hidden'
    }catch(e){}
  }
  document.addEventListener('DOMContentLoaded', resetLogoutModal);
  document.addEventListener('turbo:load', resetLogoutModal);
  document.addEventListener('turbo:before-cache', resetLogoutModal);
</script>

<!-- ===== Sidebar ===== -->
<div
  id="adminSidebar"
  data-turbo-permanent
  class="sidebar z-40 text-white w-60 fixed inset-y-0 left-0 p-3 flex flex-col
         bg-gradient-to-b from-blue-800 via-blue-700 to-blue-900
         transform transition-transform duration-300 ease-in-out no-scrollbar"
  :class="$store.layout.sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
  x-cloak
  role="navigation"
  aria-label="Sidebar utama"
  :aria-hidden="$store.layout.sidebarOpen ? 'false' : 'true'"
  @click.outside="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false"
  x-data="{
    activeMenu: '<?= url_is('admin/dashboard*') ? 'dashboard' : (url_is('admin/users*') ? 'users' : (url_is('admin/vendors*') ? 'vendors' : (url_is('admin/services*') ? 'services' : (url_is('admin/areas*') ? 'areas' : (url_is('admin/leads*') ? 'leads' : (url_is('admin/announcements*') ? 'announcements' : (url_is('admin/audit-logs*') ? 'audit-logs' : ''))))))) ?>',
    setActiveMenu(menu){ this.activeMenu = menu; sessionStorage.setItem('activeMenu', menu); },
    init(){ const s=sessionStorage.getItem('activeMenu'); if(s) this.activeMenu=s; }
  }"
>
  <!-- Brand + Close (mobile) -->
  <div class="flex items-center justify-between mb-4">
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

      <a href="<?= site_url('admin/users'); ?>"
         class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                text-white/90 visited:text-white/90"
         :class="{'bg-blue-600 text-white': activeMenu === 'users', 'text-white/90': activeMenu !== 'users'}"
         @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('users')">
        <i class="fas fa-user-shield mr-2 w-4 text-center text-xs"></i>
        <span class="text-sm">Management Users</span>
      </a>

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

        <a href="<?= site_url('admin/audit-logs'); ?>"
           class="block py-2 px-2 rounded-md mb-1 flex items-center transition-colors duration-200 hover:bg-blue-700/70
                  text-white/90 visited:text-white/90"
           :class="{'bg-blue-600 text-white': activeMenu === 'audit-logs', 'text-white/90': activeMenu !== 'audit-logs'}"
           @click="if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; setActiveMenu('audit-logs')">
          <i class="fas fa-history mr-2 w-4 text-center text-xs"></i>
          <span class="text-sm">Audit Logs</span>
        </a>
      </div>
    </div>
  </nav>

  <!-- Bottom -->
  <div class="mt-auto border-t border-blue-700/60 pt-3">
    <button
      type="button"
      class="block w-full text-left py-2 px-2 rounded-md flex items-center transition-colors duration-200 hover:bg-blue-700/70 text-white/90"
      @click="$store.ui.showLogoutModal = true; if (!$store.layout.isDesktop) $store.layout.sidebarOpen = false; document.documentElement.classList.add('overflow-hidden');"
      aria-haspopup="dialog"
      aria-controls="logoutModal"
    >
      <i class="fas fa-sign-out-alt mr-2 w-4 text-center text-xs"></i>
      <span class="text-sm">Logout</span>
    </button>

    <p class="text-[10px] text-blue-200 mt-4 text-center opacity-80">
      &copy; <?= date('Y'); ?> Imersa. All rights reserved.
    </p>
  </div>
</div>

<!-- ===== Logout Modal ===== -->
<div
  id="logoutModal"
  x-show="$store.ui.showLogoutModal"
  x-transition.opacity
  class="fixed inset-0 z-50 flex items-center justify-center"
  aria-modal="true"
  role="dialog"
  @keydown.escape.window="$store.ui.showLogoutModal = false; document.documentElement.classList.remove('overflow-hidden');"
  x-cloak
  x-init="$watch(()=>$store.ui.showLogoutModal, v => { if(v) $el.removeAttribute('hidden'); document.documentElement.classList.toggle('overflow-hidden', v) })"
>
  <!-- Backdrop -->
  <div class="absolute inset-0 bg-black/50"
       @click="$store.ui.showLogoutModal = false; document.documentElement.classList.remove('overflow-hidden');"></div>

  <!-- Dialog -->
  <div
    class="relative bg-white text-gray-800 w-full max-w-sm mx-4 rounded-lg shadow-xl p-5"
    @click.outside="$store.ui.showLogoutModal = false; document.documentElement.classList.remove('overflow-hidden');"
  >
    <div class="flex items-start gap-3">
      <div class="shrink-0 mt-0.5">
        <i class="fas fa-sign-out-alt text-red-600"></i>
      </div>
      <div class="flex-1">
        <h3 class="text-base font-semibold mb-1">Konfirmasi Logout</h3>
        <p class="text-sm text-gray-600">Anda yakin ingin keluar dari sesi saat ini?</p>
      </div>
    </div>

    <div class="mt-5 flex justify-end gap-2">
      <button
        type="button"
        class="px-3 py-2 text-sm rounded-md border border-gray-300 hover:bg-gray-100"
        @click="$store.ui.showLogoutModal = false; document.documentElement.classList.remove('overflow-hidden');"
      >Batal</button>

      <form method="post" action="<?= site_url('logout'); ?>" data-turbo="false"
            @submit="$store.ui.showLogoutModal = false; document.documentElement.classList.remove('overflow-hidden');">
        <?= csrf_field() ?>
        <button
          type="submit"
          class="px-3 py-2 text-sm rounded-md bg-red-600 text-white hover:bg-red-700"
        >Keluar</button>
      </form>
    </div>

    <button
      type="button"
      class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
      @click="$store.ui.showLogoutModal = false; document.documentElement.classList.remove('overflow-hidden');"
      aria-label="Tutup"
    >
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>

<script>
  // Inisialisasi menu aktif dari URL
  function setInitialActiveMenu(){
    const p=window.location.pathname; let m='';
    if(p.includes('/admin/dashboard')) m='dashboard';
    else if(p.includes('/admin/users')) m='users';
    else if(p.includes('/admin/vendors')) m='vendors';
    else if(p.includes('/admin/services')) m='services';
    else if(p.includes('/admin/areas')) m='areas';
    else if(p.includes('/admin/leads')) m='leads';
    else if(p.includes('/admin/announcements')) m='announcements';
    else if(p.includes('/admin/audit-logs')) m='audit-logs';
    if(m) sessionStorage.setItem('activeMenu', m);
    return m;
  }
  document.addEventListener('DOMContentLoaded', setInitialActiveMenu);
  window.addEventListener('popstate', setInitialActiveMenu);
</script>
