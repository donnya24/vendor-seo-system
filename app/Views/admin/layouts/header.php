<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#2563eb">
  <title><?= esc($title ?? 'Admin') ?> | Imersa</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- Hotwire Turbo: navigasi mulus tanpa full reload -->
  <script src="https://unpkg.com/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js"></script>
  <script>Turbo.session.drive = true;</script>

  <style>
    :root { --sbw: 16rem; } /* w-64 = 16rem (lebar sidebar) */

    [x-cloak]{display:none!important}
    .no-scrollbar{-ms-overflow-style:none;scrollbar-width:none}
    .no-scrollbar::-webkit-scrollbar{display:none}
    .sidebar{z-index:30}
    .sidebar-overlay{z-index:29}

    /* Topbar gradient */
    .topbar-gradient{
      background: linear-gradient(180deg,#4f46e5 0%, #3b82f6 45%, #1e40af 100%);
      box-shadow: inset 0 -1px 0 rgba(255,255,255,.18);
    }

    /* Search “glass” */
    .glass-input{
      border:1px solid rgba(255,255,255,.35);
      background: rgba(255,255,255,.16);
      color:#fff; backdrop-filter: blur(6px);
    }
    .glass-input::placeholder{color:rgba(255,255,255,.85)}

    /* Animasi topbar melebar/menciut */
    @media (prefers-reduced-motion:no-preference){
      .hdr-anim{transition: margin .30s ease, width .30s ease;}
    }

    /* Progress bar Turbo (opsional biar lebih halus) */
    .turbo-progress-bar{
      height:3px; background:#3b82f6;
    }
  </style>

  <!-- Alpine v3 -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script>
  // Store global agar header & sidebar berbagi state dan tidak “kedip”
  document.addEventListener('alpine:init', () => {
    Alpine.store('ui', {
      sidebarOpen: false,
      showLogoutModal: false,
      profileDropdownOpen: false,
      searchOpen: false,
      isDesktop: window.innerWidth >= 768,

      init(){
        const saved = localStorage.getItem('sidebarOpen');
        this.sidebarOpen = saved !== null ? (saved === 'true') : this.isDesktop;

        // Persist desktop
        const persist = () => { if (this.isDesktop) localStorage.setItem('sidebarOpen', this.sidebarOpen) };
        // Observer manual (karena store bukan x-data)
        this._persistInterval && clearInterval(this._persistInterval);
        this._persistInterval = setInterval(persist, 500);

        // Resize handler
        window.addEventListener('resize', () => {
          this.isDesktop = window.innerWidth >= 768;
          if (!this.isDesktop) {
            this.sidebarOpen = false;
            this.searchOpen = false;
          } else {
            const s = localStorage.getItem('sidebarOpen');
            this.sidebarOpen = s !== null ? (s === 'true') : true;
          }
        });

        // Tutup dropdown profil saat klik di luar
        document.addEventListener('click', (e) => {
          if (this.profileDropdownOpen && !e.target.closest('.profile-dropdown')) {
            this.profileDropdownOpen = false;
          }
        });
      },

      headerStyle(){
        if (!this.isDesktop) return 'margin-left:0;width:100%';
        return this.sidebarOpen
          ? `margin-left: var(--sbw); width: calc(100% - var(--sbw))`
          : 'margin-left:0;width:100%';
      },

      toggleSidebar(){ this.sidebarOpen = !this.sidebarOpen; },
      toggleSearch(inputEl){
        this.searchOpen = !this.searchOpen;
        if (this.searchOpen && inputEl) setTimeout(() => inputEl.focus(), 0);
      }
    });
  });

  // Fade konten saat navigasi Turbo
  document.addEventListener('turbo:load', () => {
    const main = document.getElementById('pageMain');
    if (main) requestAnimationFrame(() => main.classList.remove('opacity-0'));
  });
  document.addEventListener('turbo:before-visit', () => {
    const main = document.getElementById('pageMain');
    if (main) main.classList.add('opacity-0');
  });
  </script>
</head>

<body class="min-h-screen bg-gray-50 overflow-x-hidden">
  <!-- TOPBAR permanen di kanan sidebar -->
  <header
    id="topbar"
    data-turbo-permanent
    x-data
    x-init="$store.ui.init()"
    class="topbar-gradient sticky top-0 z-50 hdr-anim"
    :style="$store.ui.headerStyle()"
    role="banner"
  >
    <div class="h-14 flex items-center justify-between px-3 md:px-4">

      <!-- Left: Hamburger -->
      <div class="flex items-center">
        <!-- Mobile -->
        <button
          class="md:hidden p-2 rounded-md hover:bg-white/10 text-white"
          @click="$store.ui.toggleSidebar()"
          aria-label="Buka/Tutup sidebar"
          :aria-expanded="$store.ui.sidebarOpen ? 'true':'false'"
          aria-controls="adminSidebar"
        >
          <i class="fa-solid fa-bars"></i>
        </button>
        <!-- Desktop -->
        <button
          class="hidden md:inline-flex p-2 rounded-md hover:bg-white/10 text-white"
          @click="$store.ui.toggleSidebar()"
          aria-label="Toggle sidebar"
          :aria-expanded="$store.ui.sidebarOpen ? 'true':'false'"
          aria-controls="adminSidebar"
        >
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

      <!-- Center: Search -->
      <div class="flex-1 flex justify-center">
        <label class="relative w-full max-w-4xl" aria-label="Pencarian">
          <span class="absolute inset-y-0 left-0 z-10 pl-4 flex items-center pointer-events-none">
            <i class="fa-solid fa-magnifying-glass text-white/95"></i>
          </span>
          <input
            type="search"
            class="glass-input w-full pl-12 pr-4 py-2 rounded-full outline-none focus:ring-0 focus:border-white/60 text-[15px]"
            placeholder="Search vendors, projects, leads..."
          >
        </label>
      </div>

      <!-- Right: Notif + Avatar -->
      <div class="flex items-center gap-3 md:gap-4 text-white">
        <button class="relative p-2 rounded-md hover:bg-white/10" aria-label="Notifikasi">
          <i class="fa-regular fa-bell"></i>
          <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-[10px] leading-4 w-4 h-4 rounded-full grid place-items-center font-semibold">3</span>
        </button>

        <div class="relative profile-dropdown">
          <button @click="$store.ui.profileDropdownOpen = !$store.ui.profileDropdownOpen" class="flex items-center gap-2">
            <img src="https://i.pravatar.cc/80" alt="Admin" class="h-8 w-8 rounded-full ring-2 ring-white/60" loading="lazy">
            <i class="fa-solid fa-chevron-down text-white/90 text-sm"></i>
          </button>

          <div
            x-show="$store.ui.profileDropdownOpen" x-cloak @click.outside="$store.ui.profileDropdownOpen=false"
            class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 border border-gray-100 text-gray-700"
          >
            <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Profil Saya</a>
            <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Pengaturan</a>
            <button
              @click="$store.ui.showLogoutModal=true; $store.ui.profileDropdownOpen=false"
              class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
            >Logout</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile search dropdown -->
    <div class="md:hidden px-3 md:px-4 pb-3" x-show="$store.ui.searchOpen" x-transition x-cloak>
      <label class="relative w-full" aria-label="Pencarian">
        <span class="absolute inset-y-0 left-0 z-10 pl-3 flex items-center pointer-events-none">
          <i class="fa-solid fa-magnifying-glass text-white/90"></i>
        </span>
        <input type="search" x-ref="searchInput"
               class="glass-input w-full pl-10 pr-10 py-2 rounded-full outline-none focus:ring-0 focus:border-white/60"
               placeholder="Search...">
        <button @click="$store.ui.toggleSearch($refs.searchInput)"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/80"
                aria-label="Tutup pencarian">
          <i class="fa-solid fa-xmark"></i>
        </button>
      </label>
    </div>
  </header>

  <!-- Shell pembungkus sidebar + konten (DITUTUP di footer.php) -->
  <div id="appShell" class="flex flex-1">
