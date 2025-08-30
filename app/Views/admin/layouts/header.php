<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1e40af">
  <title><?= esc($title ?? 'Admin') ?> | Imersa</title>

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- Hotwire Turbo -->
  <script src="https://unpkg.com/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js"></script>
  <script>Turbo.session.drive = true;</script>

  <style>
    :root{
      --sbw: 15rem;              /* fallback width sidebar */
      --sbw-dyn: var(--sbw);     /* diisi hasil ukur JS */
      --primary: #1e40af;
      --primary-dark: #1e3a8a;
    }

    [x-cloak]{display:none!important}
    .sidebar{z-index:30}

    /* Topbar & konten ikut bergeser berdasarkan class pada <html> */
    #topbar{
      background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
      transition: margin-left .3s ease, width .3s ease;
    }
    /* Saat desktop dan sidebar terbuka */
    html.sidebar-open-desktop #topbar{
      margin-left: var(--sbw-dyn, var(--sbw));
      width: calc(100% - var(--sbw-dyn, var(--sbw)));
    }
    /* Saat tertutup / mobile */
    html:not(.sidebar-open-desktop) #topbar{
      margin-left: 0;
      width: 100%;
    }

    /* Konten */
    #pageWrap{ transition: margin-left .3s ease; }
    html.sidebar-open-desktop #pageWrap{ margin-left: var(--sbw-dyn, var(--sbw)); }
    html:not(.sidebar-open-desktop) #pageWrap{ margin-left: 0; }

    .glass-effect{ background:rgba(255,255,255,.15); backdrop-filter:blur(10px); border:1px solid rgba(0,0,0,.1) }
    @keyframes fadeIn{ from{opacity:0;transform:translateY(10px)} to{opacity:1;transform:translateY(0)} }
    .fade-in{ animation:fadeIn .5s ease forwards }

    /* Modal contoh */
    .modal-overlay{position:fixed;inset:0;background-color:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;pointer-events:none;transition:opacity .3s ease;}
    .modal-overlay.active{opacity:1;pointer-events:auto}
    .profile-modal{max-width:500px;width:90%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);transform:scale(.9);transition:transform .3s ease;}
    .modal-overlay.active .profile-modal{transform:scale(1)}
    .modal-header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;background:linear-gradient(90deg,var(--primary) 0%,var(--primary-dark) 100%);color:#fff;}
    .modal-title{font-weight:700;font-size:1.25rem}
    .modal-close{background:rgba(255,255,255,.2);border:none;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;transition:all .2s;}
    .modal-close:hover{background:rgba(255,255,255,.3);transform:rotate(90deg)}
  </style>

  <!-- Alpine v3 (WAJIB defer) -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script>
  /* ===== Util ukuran/layout sidebar ===== */
  const mqDesktop = () => window.matchMedia('(min-width: 768px)').matches;

  function measureSidebar(){
    const sb = document.getElementById('adminSidebar') || document.getElementById('appSidebar') || document.querySelector('.sidebar');
    if (!sb) return;
    const w = Math.round(sb.getBoundingClientRect().width);
    if (w > 0) document.documentElement.style.setProperty('--sbw-dyn', w + 'px');
  }

  function isSidebarVisibleOnDesktop(){
    const sb = document.getElementById('adminSidebar') || document.querySelector('.sidebar');
    if (!sb) return false;
    const hiddenByClass = sb.classList.contains('-translate-x-full');
    const style = getComputedStyle(sb);
    const tx = style.transform;
    const movedOut = tx && tx.includes('matrix') && tx.includes('-1');
    return !(hiddenByClass || movedOut);
  }

  function applyHtmlLayoutClass(){
    const on = mqDesktop() && isSidebarVisibleOnDesktop();
    document.documentElement.classList.toggle('sidebar-open-desktop', on);
  }

  function initSidebarObserver(){
    const sb = document.getElementById('adminSidebar') || document.querySelector('.sidebar');
    if (!sb) return;
    const obs = new MutationObserver(() => { measureSidebar(); applyHtmlLayoutClass(); });
    obs.observe(sb, { attributes: true, attributeFilter: ['class', 'style'] });
  }

  /* ===== Start Alpine dengan aman (idempotent) ===== */
  window.__alpine_started = false;
  function startAlpineOnce(){
    if (window.Alpine && !window.__alpine_started) {
      try { Alpine.flushAndStopDeferringMutations?.(); } catch(e){}
      Alpine.start();
      window.__alpine_started = true;
    }
  }

  /* ===== Lifecycle ===== */
  document.addEventListener('DOMContentLoaded', () => {
    startAlpineOnce();                 // start di initial load
    measureSidebar();
    applyHtmlLayoutClass();
    initSidebarObserver();
  });

  // Re-init setelah navigasi Turbo (penting untuk halaman edit yang modalnya pakai Alpine)
  document.addEventListener('turbo:load', () => {
    window.__alpine_started = false;   // izinkan start ulang
    startAlpineOnce();
    measureSidebar();
    applyHtmlLayoutClass();
    initSidebarObserver();
  });

  // Jika menggunakan HTMX
  document.body?.addEventListener?.('htmx:afterSettle', () => {
    window.__alpine_started = false;
    startAlpineOnce();
  });

  window.addEventListener('resize', () => { measureSidebar(); applyHtmlLayoutClass(); });
  window.addEventListener('orientationchange', () => { measureSidebar(); applyHtmlLayoutClass(); });

  // Helper search (opsional)
  window.handleSearch = (e) => { e.preventDefault(); };
  </script>
</head>

<body class="min-h-screen bg-gray-50 overflow-x-hidden">
  <!-- TOPBAR -->
  <header id="topbar" class="fixed top-0 left-0 right-0 z-50" role="banner">
    <div class="h-14 flex items-center justify-between px-3 md:px-4">
      <!-- Hamburger -->
      <button
        type="button"
        class="inline-flex items-center justify-center p-2 rounded-md hover:bg-white/12 text-white/95 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/30"
        aria-label="Toggle sidebar"
        aria-controls="adminSidebar"
        onclick="
          (function(){
            var sb=document.getElementById('adminSidebar')||document.querySelector('.sidebar');
            if(!sb) return;
            if(sb.classList.contains('-translate-x-full')){
              sb.classList.remove('-translate-x-full'); sb.classList.add('translate-x-0');
            }else{
              sb.classList.remove('translate-x-0'); sb.classList.add('-translate-x-full');
            }
            measureSidebar(); applyHtmlLayoutClass();
          })();
        "
      >
        <i class="fa-solid fa-bars"></i>
      </button>

      <!-- Search -->
      <div class="flex-1 flex justify-center">
        <form onsubmit="handleSearch(event)" class="relative w-full max-w-xl mx-4">
          <span class="absolute inset-y-0 left-0 z-10 pl-4 flex items-center pointer-events-none">
            <i class="fa-solid fa-magnifying-glass text-white/90"></i>
          </span>
          <input
            id="searchInput"
            type="search"
            class="glass-effect w-full pl-12 pr-4 py-2 rounded-full outline-none focus:ring-2 focus:ring-white/25 text-[15px] text-white placeholder-white/85"
            placeholder="Search vendors, projects, leads..."
          >
        </form>
      </div>

      <!-- Right -->
      <div class="flex items-center gap-3 md:gap-4 text-white/95">
        <button class="relative p-2 rounded-md hover:bg-white/12 transition-colors duration-200" aria-label="Notifikasi">
          <i class="fa-regular fa-bell"></i>
          <span class="absolute -top-0.5 -right-0.5 bg-amber-500 text-[10px] leading-4 w-4 h-4 rounded-full grid place-items-center font-semibold text-white">3</span>
        </button>
        <button onclick="document.getElementById('profileModal').classList.add('active');document.body.style.overflow='hidden'" class="flex items-center gap-2 hover:opacity-90 transition-opacity duration-200">
          <img
            src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 256 256'><defs><linearGradient id='g' x1='0' y1='0' x2='0' y2='1'><stop offset='0%' stop-color='rgb(96,165,250)'/><stop offset='100%' stop-color='rgb(30,64,175)'/></linearGradient></defs><circle cx='128' cy='128' r='124' fill='url(%23g)'/><circle cx='128' cy='96' r='46' fill='white'/><path d='M36 216c14-42 52-72 92-72s78 30 92 72' fill='white'/></svg>"
            alt="Avatar" class="h-8 w-8 rounded-full ring-2 ring-white/50" loading="lazy">
          <i class="fa-solid fa-user-gear text-white/90 text-base"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- Modal contoh -->
  <div id="profileModal" class="modal-overlay" onclick="if(event.target === this){this.classList.remove('active');document.body.style.overflow=''}">
    <div class="profile-modal">
      <div class="modal-header">
        <h3 class="modal-title">Foto Profil</h3>
        <button class="modal-close" onclick="document.getElementById('profileModal').classList.remove('active');document.body.style.overflow=''"><i class="fa-solid fa-times"></i></button>
      </div>
      <div class="p-6">…</div>
    </div>
  </div>

  <!-- Shell: sidebar + pageWrap di-include oleh layout -->
  <div id="appShell" class="flex flex-1 pt-14">
    <!-- Sidebar (id=adminSidebar) & Konten (id=pageWrap) muncul dari include lain -->
  </div>
</body>
</html>
