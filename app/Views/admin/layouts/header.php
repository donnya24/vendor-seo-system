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
    :root { 
      --sbw: 15rem;              /* estimasi lebar sidebar */
      --sbw-dyn: var(--sbw);     /* akan di-overwrite hasil ukur JS */
      --primary: #1e40af;
      --primary-dark: #1e3a8a;
      --primary-light: #3b82f6;
      --accent: #6366f1;
      --sidebar-bg: #1e293b;
    }

    [x-cloak] { display: none !important; }
    .sidebar { z-index: 30; }
    .sidebar-overlay { z-index: 29; }

    .topbar-gradient {
      background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      /* transition DIHAPUS agar header tidak transisi */
      /* transition: margin-left 300ms ease, width 300ms ease; */
    }

    .glass-effect {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(0, 0, 0, 0.1);
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .fade-in { animation: fadeIn 0.5s ease forwards; }

    /* Modal (tidak diubah) */
    .modal-overlay{position:fixed;inset:0;background-color:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;pointer-events:none;transition:opacity .3s ease;}
    .modal-overlay.active{opacity:1;pointer-events:auto}
    .profile-modal{max-width:500px;width:90%;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);transform:scale(.9);transition:transform .3s ease;}
    .modal-overlay.active .profile-modal{transform:scale(1)}
    .modal-header{display:flex;justify-content:space-between;align-items:center;padding:20px 24px;background:linear-gradient(90deg,var(--primary) 0%,var(--primary-dark) 100%);color:#fff;}
    .modal-title{font-weight:600;font-size:1.25rem}
    .modal-close{background:rgba(255,255,255,.2);border:none;width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#fff;transition:all .2s;}
    .modal-close:hover{background:rgba(255,255,255,.3);transform:rotate(90deg)}
    .profile-image-container{padding:40px;display:flex;flex-direction:column;align-items:center;justify-content:center;background:#f8fafc;position:relative;}
    .profile-image{width:100%;max-width:280px;height:auto;object-fit:cover;border-radius:12px;box-shadow:0 10px 15px -3px rgba(0,0,0,.1);margin-bottom:20px;}
    .action-buttons{display:flex;flex-direction:column;gap:12px;width:100%;max-width:280px}
    .btn{padding:12px 16px;border-radius:8px;font-weight:500;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .2s;cursor:pointer;width:100%;font-size:.95rem}
    .btn-primary{background:var(--primary);color:#fff;border:none}
    .btn-primary:hover{background:var(--primary-dark)}
    .btn-outline{background:transparent;color:var(--primary);border:1px solid var(--primary)}
    .btn-outline:hover{background:#eff6ff}
    .modal-footer{padding:20px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;display:flex;justify-content:flex-end;gap:12px}
    .btn-secondary{padding:10px 20px;border-radius:8px;font-weight:500;background:transparent;color:#64748b;border:1px solid #cbd5e1;transition:all .2s;cursor:pointer}
    .btn-secondary:hover{background:#f1f5f9;color:#475569}
    .btn-success{padding:10px 20px;border-radius:8px;font-weight:500;background:var(--primary);color:#fff;border:none;transition:all .2s;cursor:pointer}
    .btn-success:hover{background:var(--primary-dark)}
  </style>

  <!-- Alpine v3 -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script>
  // ====== Ukur lebar sidebar aktual, set ke --sbw-dyn ======
  function measureSidebar() {
    // id yang umum dipakai di kode kamu: #appSidebar (fallback ke .sidebar)
    const sb = document.getElementById('appSidebar') || document.querySelector('.sidebar');
    if (!sb) return;
    // Gunakan width aktual (termasuk padding/border)
    const w = Math.round(sb.getBoundingClientRect().width);
    if (w > 0) document.documentElement.style.setProperty('--sbw-dyn', w + 'px');
  }

  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
      if (typeof Alpine === 'undefined') {
        console.warn('Alpine.js not loaded - applying fallback styles');
        document.documentElement.classList.add('no-alpine');
      } else { Alpine.start(); }
    }, 100);

    // Modal helpers
    window.showProfileModal = function(){
      const m=document.getElementById('profileModal'); m.classList.add('active'); document.body.style.overflow='hidden';
    };
    window.hideProfileModal = function(){
      const m=document.getElementById('profileModal'); m.classList.remove('active'); document.body.style.overflow='';
    };
    document.addEventListener('keydown', e => { if(e.key==='Escape') hideProfileModal(); });

    // Search handler
    window.handleSearch = function(event) {
      event.preventDefault();
      const searchTerm = document.getElementById('searchInput').value.trim();
      if (searchTerm) { console.log('Melakukan pencarian untuk:', searchTerm); alert('Mencari: ' + searchTerm); }
    };

    // Inisialisasi pengukuran
    measureSidebar();

    // Amati perubahan ukuran sidebar (misal class w-60 diganti)
    const sb = document.getElementById('appSidebar') || document.querySelector('.sidebar');
    if (sb && 'ResizeObserver' in window) {
      const ro = new ResizeObserver(() => measureSidebar());
      ro.observe(sb);
    }
  });

  // Sinkron dengan Turbo + resize + toggle
  document.addEventListener('turbo:load', measureSidebar);
  window.addEventListener('resize', measureSidebar);
  window.addEventListener('toggle-sidebar', () => setTimeout(measureSidebar, 0));

  // Smooth reveal on Turbo
  document.addEventListener('turbo:load', () => {
    const main = document.getElementById('pageMain');
    if (main) { main.classList.remove('opacity-0'); main.classList.add('fade-in'); }
  });
  </script>
</head>

<body class="min-h-screen bg-gray-50 overflow-x-hidden" 
      x-data="{ sidebarOpen: window.innerWidth >= 768, searchOpen: false, isDesktop: window.innerWidth >= 768 }"
      x-init="
        // ukur saat mount
        measureSidebar();
        window.addEventListener('resize', () => {
          isDesktop = window.innerWidth >= 768;
          if (!isDesktop && sidebarOpen) { sidebarOpen = false; }
          measureSidebar();
        });
        $watch('sidebarOpen', () => { $nextTick(() => measureSidebar()); });
        $watch('isDesktop', () => { $nextTick(() => measureSidebar()); });
      "
      x-on:toggle-sidebar.window="sidebarOpen = !sidebarOpen; $nextTick(() => measureSidebar())"
>
  
  <!-- TOPBAR -->
  <header
    id="topbar"
    data-turbo-permanent
    class="topbar-gradient fixed top-0 left-0 right-0 z-50"
    :style="isDesktop && sidebarOpen
      ? 'margin-left: var(--sbw-dyn, var(--sbw)); width: calc(100% - var(--sbw-dyn, var(--sbw)))'
      : 'margin-left: 0; width: 100%'"
    role="banner"
  >
    <div class="h-14 flex items-center justify-between px-3 md:px-4">

      <!-- Left: Hamburger -->
      <div class="flex items-center">
        <button
          type="button"
          class="inline-flex items-center justify-center p-2 rounded-md hover:bg-white/12 text-white/95 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/30"
          aria-label="Toggle sidebar"
          aria-controls="appSidebar"
          :aria-expanded="sidebarOpen.toString()"
          @click.stop.prevent="sidebarOpen = !sidebarOpen; $nextTick(() => measureSidebar())"
          @keyup.enter.prevent="sidebarOpen = !sidebarOpen; $nextTick(() => measureSidebar())"
          @keyup.space.prevent="sidebarOpen = !sidebarOpen; $nextTick(() => measureSidebar())"
        >
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

      <!-- Center: Search -->
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

      <!-- Right: Notif + Avatar -->
      <div class="flex items-center gap-3 md:gap-4 text-white/95">
        <button class="relative p-2 rounded-md hover:bg-white/12 transition-colors duration-200" aria-label="Notifikasi">
          <i class="fa-regular fa-bell"></i>
          <span class="absolute -top-0.5 -right-0.5 bg-amber-500 text-[10px] leading-4 w-4 h-4 rounded-full grid place-items-center font-semibold text-white">3</span>
        </button>

        <!-- Profile & Full Photo -->
        <div class="relative">
          <button onclick="showProfileModal()" class="flex items-center gap-2 hover:opacity-90 transition-opacity duration-200">
            <img
              src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 256 256'><defs><linearGradient id='g' x1='0' y1='0' x2='0' y2='1'><stop offset='0%' stop-color='rgb(96,165,250)'/><stop offset='100%' stop-color='rgb(30,64,175)'/></linearGradient></defs><circle cx='128' cy='128' r='124' fill='url(%23g)'/><circle cx='128' cy='96' r='46' fill='white'/><path d='M36 216c14-42 52-72 92-72s78 30 92 72' fill='white'/></svg>"
              alt="Avatar"
              class="h-8 w-8 rounded-full ring-2 ring-white/50"
              loading="lazy">
            <i class="fa-solid fa-user-gear text-white/90 text-base"></i>
          </button>
        </div>
      </div>
    </div>
  </header>

  <!-- Modal untuk foto profil -->
  <div id="profileModal" class="modal-overlay" onclick="if(event.target === this) hideProfileModal()">
    <div class="profile-modal">
      <div class="modal-header">
        <h3 class="modal-title">Foto Profil</h3>
        <button class="modal-close" onclick="hideProfileModal()"><i class="fa-solid fa-times"></i></button>
      </div>
      
      <div class="profile-image-container">
        <img
          src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><defs><linearGradient id='g' x1='0' y1='0' x2='0' y2='1'><stop offset='0%' stop-color='rgb(96,165,250)'/><stop offset='100%' stop-color='rgb(30,64,175)'/></linearGradient></defs><circle cx='256' cy='256' r='248' fill='url(%23g)'/><circle cx='256' cy='190' r='90' fill='white'/><path d='M80 416c28-86 103-144 176-144s148 58 176 144' fill='white'/></svg>"
          alt="Foto Profil Pengguna"
          class="profile-image">
        
        <div class="action-buttons">
          <button class="btn btn-primary">
            <i class="fa-solid fa-pen-to-square"></i>
            Edit Profil
          </button>
        </div>
      </div>
      
      <div class="modal-footer">
        <button class="btn-secondary" onclick="hideProfileModal()">Batal</button>
        <button class="btn-success">Simpan Perubahan</button>
      </div>
    </div>
  </div>

  <!-- Shell -->
  <div id="appShell" class="flex flex-1 pt-14">
    <!-- Konten halaman & sidebar -->
  </div>
</body>
</html>

