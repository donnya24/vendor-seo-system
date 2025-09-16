<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#1e40af">
  <title><?= esc($title ?? 'Admin') ?> | Imersa</title>

  <?php
    // Versi sederhana cache-buster (opsional)
    $favVer = time();
  ?>
  <!-- ================== FAVICONS (pakai file milikmu) ================== -->
  <link rel="icon" href="<?= base_url('favicon.ico') ?>?v=<?= $favVer ?>" type="image/x-icon">
  <link rel="shortcut icon" href="<?= base_url('favicon.ico') ?>?v=<?= $favVer ?>" type="image/x-icon">
  <!-- (Opsional) Jika kamu punya varian lain, buka komentar di bawah dan sesuaikan pathnya:
  <link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/img/favicon-32x32.png') ?>?v=<?= $favVer ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/img/favicon-16x16.png') ?>?v=<?= $favVer ?>">
  <link rel="apple-touch-icon" href="<?= base_url('assets/img/apple-touch-icon.png') ?>?v=<?= $favVer ?>">
  <link rel="mask-icon" href="<?= base_url('assets/img/safari-pinned-tab.svg') ?>?v=<?= $favVer ?>" color="#1e40af">
  -->

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <!-- Hotwire Turbo -->
  <script src="https://unpkg.com/@hotwired/turbo@8.0.4/dist/turbo.es2017-umd.js" defer></script>
  <script>
    // Init Turbo secepat mungkin
    (function initTurbo(){
      function apply(){
        if (!window.Turbo) return;
        try{
          Turbo.session.drive = true;
          if (Turbo.setProgressBarDelay) Turbo.setProgressBarDelay(0);
        }catch(e){}
      }
      if (document.readyState === 'complete') apply();
      else window.addEventListener('load', apply, { once:true });
    })();
  </script>

  <style>
    :root{
      --sbw: 15rem; --sbw-dyn: var(--sbw); --sbw-pad: 0px;
      --primary: #1e40af; --primary-dark: #1e3a8a; --header-h: 3.5rem;
    }
    [x-cloak]{display:none!important}
    .sidebar{z-index:40}

    /* HEADER menyesuaikan lebar sidebar */
    #topbar{
      background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
      box-shadow: 0 4px 12px rgba(0,0,0,.1);
      margin-left: var(--sbw-pad);
      width: calc(100% - var(--sbw-pad));
      transition: margin-left .3s ease, width .3s ease;
    }
    html::before{
      content:""; position:fixed; top:0; left:0; height:var(--header-h); width:0; pointer-events:none; z-index:10;
      background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%); transition: width .3s ease;
    }
    @media (min-width:768px){ html::before{ width: var(--sbw-pad); } }
    #pageWrap{ transition: margin-left .3s ease; }
    @media (min-width:768px){ #pageWrap{ margin-left: var(--sbw-pad); } }

    .glass-effect{ background:rgba(255,255,255,.15); backdrop-filter:blur(10px); border:1px solid rgba(0,0,0,.1) }

    /* Overlay modal */
    .overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;pointer-events:none;transition:opacity .3s}
    .overlay.active{opacity:1;pointer-events:auto}

    /* Progress bar Turbo */
    turbo-progress-bar {
      background: linear-gradient(to right, var(--primary), var(--primary-dark));
      height: 3px;
      opacity: 0;
      transition: opacity 0.3s;
    }
    turbo-progress-bar.loading { opacity: 1; }
  </style>

  <!-- Alpine -->
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <script>
  /* ===== Layout sidebar & header offset ===== */
  const mqDesktop = () => window.matchMedia('(min-width: 768px)').matches;
  const getSidebar = () => document.getElementById('adminSidebar') || document.getElementById('appSidebar') || document.querySelector('.sidebar');

  function measureSidebar(){
    const sb=getSidebar(); if(!sb) return;
    const w=Math.round(sb.getBoundingClientRect().width);
    if(w>0) document.documentElement.style.setProperty('--sbw-dyn', w+'px');
  }
  function isSidebarOpenOnDesktop(){
    const sb=getSidebar(); if(!sb) return false;
    return mqDesktop() && !sb.classList.contains('-translate-x-full');
  }
  function applyHeaderOffset(){
    const sb=getSidebar();
    const w=(sb && isSidebarOpenOnDesktop())?Math.round(sb.getBoundingClientRect().width):0;
    document.documentElement.style.setProperty('--sbw-pad', w+'px');
  }
  function initSidebarObserver(){
    const sb=getSidebar(); if(!sb) return;
    const obs=new MutationObserver(()=>{measureSidebar();applyHeaderOffset();});
    obs.observe(sb,{attributes:true,attributeFilter:['class','style']});
    // Simpan untuk dibersihkan sebelum Turbo cache
    window.__sbObs = obs;
  }

  // Debounce
  function debounce(func, wait) {
    let timeout;
    return function(...args) {
      clearTimeout(timeout);
      timeout = setTimeout(()=>func(...args), wait);
    };
  }
  const debouncedApplyHeaderOffset = debounce(applyHeaderOffset, 100);
  const debouncedMeasureSidebar = debounce(measureSidebar, 100);

  function initLayoutBindings(){
    measureSidebar(); applyHeaderOffset(); initSidebarObserver();
    window.addEventListener('resize', debouncedApplyHeaderOffset);
    window.addEventListener('orientationchange', debouncedMeasureSidebar);
  }
  function cleanupLayoutBindings(){
    window.removeEventListener('resize', debouncedApplyHeaderOffset);
    window.removeEventListener('orientationchange', debouncedMeasureSidebar);
    if (window.__sbObs){ try{ window.__sbObs.disconnect(); }catch(e){} delete window.__sbObs; }
  }

  document.addEventListener('DOMContentLoaded', initLayoutBindings);
  document.addEventListener('turbo:load', initLayoutBindings);
  document.addEventListener('turbo:before-cache', cleanupLayoutBindings);

  // (Tidak dipakai; dibiarkan agar tidak mengubah skrip lain)
  window.handleSearch = (e) => e.preventDefault();

  // ====== Unggah dari Galeri (validasi + preview + fallback ikon) ======
  (function(){
    let currentBlobURL = null;

    function bytesToMB(b){ return (b/1024/1024).toFixed(2); }

    function bindProfileUpload(){
      const inputGallery = document.getElementById('pickFromGallery');
      const previewImg   = document.getElementById('profilePreview');
      const fallbackIcon = document.getElementById('profileIconFallback');
      const saveBtn      = document.getElementById('savePhotoBtn');
      const fileField    = document.getElementById('fileField');
      const notice       = document.getElementById('noticeText');
      const openGalleryBtn = document.getElementById('openGalleryBtn');

      if(!previewImg || !saveBtn) return;

      const MAX_MB = 5;
      const ACCEPT = ['image/jpeg','image/png','image/webp'];

      function setError(msg){
        if(notice){ notice.textContent = msg; notice.classList.remove('text-green-700'); notice.classList.add('text-red-700'); }
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50','cursor-not-allowed');
      }
      function setOk(msg){
        if(notice){ notice.textContent = msg; notice.classList.remove('text-red-700'); notice.classList.add('text-green-700'); }
      }

      function revokeBlob(){
        if(currentBlobURL){ URL.revokeObjectURL(currentBlobURL); currentBlobURL = null; }
      }
      function showFallback(){
        revokeBlob();
        previewImg.classList.add("hidden");
        previewImg.removeAttribute('src');       // ⟵ TIDAK pakai src=""
        fallbackIcon?.classList.remove("hidden");
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50','cursor-not-allowed');
      }

      function showImage(url){
        if(!url){ showFallback(); return; }
        revokeBlob();
        currentBlobURL = url;
        previewImg.setAttribute('src', url);
        previewImg.classList.remove("hidden");
        fallbackIcon?.classList.add("hidden");
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50','cursor-not-allowed');
      }

      function handleFile(file){
        if(!file){ showFallback(); return; }
        if(!ACCEPT.includes(file.type)){ setError('Format tidak didukung. Gunakan JPG/PNG/WebP.'); return; }
        if(file.size > MAX_MB*1024*1024){ setError(`Ukuran terlalu besar (${bytesToMB(file.size)} MB). Maksimal ${MAX_MB} MB.`); return; }

        const url = URL.createObjectURL(file);
        showImage(url);

        if(fileField){
          const dt = new DataTransfer();
          dt.items.add(file);
          fileField.files = dt.files;
        }
        setOk('Siap diunggah.');
      }

      // Set awal dari data-initial-src (kalau ada), TANPA src kosong
      const initial = previewImg?.getAttribute('data-initial-src') || "";
      if(initial.trim().length > 0){
        previewImg.setAttribute('src', initial);
        previewImg.classList.remove("hidden");
        fallbackIcon?.classList.add("hidden");
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50','cursor-not-allowed');
      }else{
        showFallback();
      }

      inputGallery?.addEventListener('change', e => handleFile(e.target.files?.[0]));
      previewImg?.addEventListener('error', showFallback);
      document.getElementById('profilePickerArea')?.addEventListener('click', ()=> inputGallery?.click());
      openGalleryBtn?.addEventListener('click', ()=> inputGallery?.click());

      // Bersihkan blob URL saat akan dicache/tinggalkan halaman
      document.addEventListener('turbo:before-cache', revokeBlob, { once:true });
      window.addEventListener('beforeunload', revokeBlob, { once:true });
    }

    document.addEventListener('DOMContentLoaded', bindProfileUpload);
    document.addEventListener('turbo:load', bindProfileUpload);
  })();
  </script>
</head>

<body class="min-h-screen bg-gray-50 overflow-x-hidden">
  <!-- TOPBAR -->
  <header id="topbar" class="fixed top-0 left-0 right-0 z-50" role="banner">
    <div class="h-14 flex items-center justify-between px-3 md:px-4">
      <!-- Left: Hamburger -->
      <div class="flex items-center">
        <button id="hamburgerBtn" type="button"
          class="inline-flex items-center justify-center p-2 rounded-md hover:bg-white/12 text-white/95 transition-colors duration-200"
          aria-label="Toggle sidebar">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

      <!-- Right: Notifikasi + Avatar -->
      <div class="flex items-center gap-3 md:gap-4 text-white/95">
        <!-- (DIPERTAHANKAN) Tombol lonceng aslinya diganti fungsinya via Alpine, markup tetap setara -->
        <div x-data="notifDropdown()" class="relative">
          <button @click="toggle()" type="button" class="relative p-2 rounded-md hover:bg-white/12 transition-colors duration-200" aria-label="Notifikasi">
            <i class="fa-regular fa-bell"></i>
            <template x-if="count > 0">
              <span class="absolute -top-0.5 -right-0.5 bg-amber-500 text-[10px] leading-4 w-4 h-4 rounded-full grid place-items-center font-semibold text-white" x-text="count"></span>
            </template>
          </button>
          <!-- Dropdown notifikasi -->
          <div x-show="open" @click.away="open=false" x-transition
               class="absolute right-0 mt-2 w-72 bg-white rounded-lg shadow-lg overflow-hidden z-50">
            <div class="flex items-center justify-between px-4 py-2 bg-gray-100 border-b">
              <span class="font-semibold text-gray-700">Notifikasi</span>
              <button @click="clearAll()" class="text-gray-500 hover:text-red-600" title="Bersihkan">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
            <div class="max-h-60 overflow-y-auto divide-y divide-gray-100">
              <template x-if="items.length > 0">
                <ul>
                  <template x-for="(n, i) in items" :key="i">
                    <li class="px-4 py-3 hover:bg-gray-50 text-sm text-gray-700" x-text="n"></li>
                  </template>
                </ul>
              </template>
              <template x-if="items.length === 0">
                <div class="px-4 py-6 text-center text-gray-500 text-sm">
                  Belum ada notifikasi
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Avatar header -->
        <button type="button"
                id="headerAvatarButton"
                data-avatar-src="<?= esc($profilePhoto ?? '', 'attr') ?>"
                class="relative flex items-center justify-center hover:opacity-90 transition-opacity duration-200 w-8 h-8">
          <div id="headerAvatarFallback" class="absolute inset-0 rounded-full ring-2 ring-white/50 bg-white/20 grid place-items-center text-white/90">
            <i class="fa-solid fa-user text-xs"></i>
          </div>
          <span id="headerAvatarMount"></span>
        </button>
      </div>
    </div>
  </header>

  <!-- Modal Profil -->
  <div id="profileModal" class="overlay">
    <div class="bg-white rounded-2xl shadow-2xl w-[92%] max-w-[540px] overflow-hidden transform transition">
      <div class="flex items-center justify-between px-6 py-4" style="background:linear-gradient(90deg,var(--primary) 0%,var(--primary-dark) 100%);color:#fff">
        <h3 class="font-semibold text-[18px]">Foto Profil</h3>
        <button id="profileCloseBtn" type="button" class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
          <i class="fa-solid fa-times"></i>
        </button>
      </div>

      <div class="p-6">
        <!-- Preview CENTER & LINGKARAN -->
        <div class="w-full flex justify-center items-center mb-5">
          <div id="profilePickerArea" class="w-48 h-48 rounded-full overflow-hidden shadow-lg border-4 border-white cursor-pointer relative bg-gray-100" title="Klik untuk memilih gambar">
            <div id="profileIconFallback" class="absolute inset-0 flex items-center justify-center text-gray-400 text-7xl pointer-events-none">
              <i class="fa-solid fa-user"></i>
            </div>
            <!-- Penting: TANPA src="" -->
            <img id="profilePreview"
                 data-initial-src="<?= esc($profilePhoto ?? '', 'attr') ?>"
                 alt="Foto Profil"
                 class="w-full h-full object-cover hidden"
                 draggable="false">
          </div>
        </div>

        <form method="post" action="<?= site_url('profile/upload'); ?>" enctype="multipart/form-data" class="space-y-4">
          <?= csrf_field() ?>
          <input id="fileField" name="photo" type="file" accept="image/*" class="hidden">
          <input id="pickFromGallery" type="file" accept="image/*" class="hidden">

          <div class="flex items-center gap-3">
            <button id="openGalleryBtn" type="button"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-gray-300 text-gray-800 hover:bg-gray-50 transition">
              <i class="fa-solid fa-image"></i><span>Pilih dari Galeri</span>
            </button>
            <span id="noticeText" class="text-sm text-gray-600">Format: JPG/PNG/WebP · Maks 5 MB</span>
          </div>

          <!-- Hanya tombol Simpan -->
          <div class="flex items-center justify-end pt-2">
            <button id="savePhotoBtn" type="submit" disabled
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition opacity-50 cursor-not-allowed">
              <i class="fa-solid fa-cloud-arrow-up"></i>
              <span>Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Shell -->
  <div id="appShell" class="flex flex-1 pt-14"></div>

  <script>
    // Modal profil + guard Turbo cache
    (function(){
      const m = document.getElementById('profileModal');
      if(!m) return;
      const obs = new MutationObserver(() => {
        m.classList.contains('active') ? (m.style.opacity='1', m.style.pointerEvents='auto') : (m.style.opacity='0', m.style.pointerEvents='none');
      });
      obs.observe(m, { attributes:true, attributeFilter:['class'] });
      document.addEventListener('turbo:before-cache', () => {
        m.classList.remove('active');
        document.body.style.overflow = '';
      });
    })();

    // Event handlers
    (function(){
      const profileModal = document.getElementById('profileModal');
      const openAvatarBtn = document.getElementById('headerAvatarButton');
      const closeBtn1 = document.getElementById('profileCloseBtn');
      const hamburgerBtn = document.getElementById('hamburgerBtn');

      function openModal(){ profileModal.classList.add('active'); document.body.style.overflow='hidden'; }
      function closeModal(){ profileModal.classList.remove('active'); document.body.style.overflow=''; }

      openAvatarBtn?.addEventListener('click', openModal);
      closeBtn1?.addEventListener('click', closeModal);
      profileModal?.addEventListener('click', (e)=>{ if(e.target===profileModal) closeModal(); });

      hamburgerBtn?.addEventListener('click', ()=>{
        const sb=document.getElementById('adminSidebar')||document.querySelector('.sidebar');
        if(!sb) return;
        if(sb.classList.contains('-translate-x-full')){
          sb.classList.remove('-translate-x-full'); sb.classList.add('translate-x-0');
        }else{
          sb.classList.remove('translate-x-0'); sb.classList.add('-translate-x-full');
        }
        measureSidebar(); applyHeaderOffset();
      });

      // Avatar header loader (tanpa <img> inline)
      const mount = document.getElementById('headerAvatarMount');
      const fallback = document.getElementById('headerAvatarFallback');
      const src = (openAvatarBtn?.getAttribute('data-avatar-src')||'').trim();
      if(src && mount && fallback){
        const img = new Image();
        img.alt='Avatar'; img.draggable=false; img.loading='lazy';
        img.className='h-8 w-8 rounded-full ring-2 ring-white/50 object-cover';
        img.onload = ()=>{ mount.innerHTML=''; mount.appendChild(img); fallback.classList.add('hidden'); };
        img.onerror = ()=>{ mount.innerHTML=''; fallback.classList.remove('hidden'); };
        img.src = src;
      }
    })();

    // Progress bar Turbo (show/hide)
    (function(){
      document.addEventListener('turbo:before-visit', () => {
        const bar = document.querySelector('turbo-progress-bar');
        if (bar) bar.classList.add('loading');
      });
      document.addEventListener('turbo:load', () => {
        const bar = document.querySelector('turbo-progress-bar');
        if (bar) bar.classList.remove('loading');
      });
    })();

    // Dropdown notifikasi (Alpine)
    function notifDropdown(){
      return {
        open:false,
        items: <?= json_encode($notifications ?? []) ?>, // backend bisa kirim array string
        get count(){ return this.items.length },
        toggle(){ this.open = !this.open },
        clearAll(){ this.items = []; this.open=false }
      }
    }
  </script>
</body>
</html>
