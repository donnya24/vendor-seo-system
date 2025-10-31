<!DOCTYPE html>
<html lang="id" x-data="{ 
  atTop: true, 
  menuOpen: false,
  currentPath: window.location.hash.substring(1) || '',
  init() {
    window.closeMobileMenu = () => { this.menuOpen = false };
    window.addEventListener('scroll', () => this.atTop = window.scrollY < 10);
    
    // Handle initial path from URL hash
    this.handleInitialPath();
    
    // Listen for hashchange (browser back/forward)
    window.addEventListener('hashchange', () => this.handleHashChange());
  },
  handleInitialPath() {
    const hash = window.location.hash.substring(1);
    if (hash) {
      this.navigateTo(hash);
    }
  },
  handleHashChange() {
    const hash = window.location.hash.substring(1);
    this.currentPath = hash;
    
    if (hash) {
      this.scrollToSection(hash);
    }
    this.updateActiveNav(hash);
  },
  scrollToSection(section) {
    const targetElement = document.getElementById(section);
    if (targetElement) {
      setTimeout(() => {
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }, 100);
    }
  },
  updateActiveNav(section) {
    document.querySelectorAll('.nav-link').forEach(link => {
      const linkSection = link.getAttribute('data-section');
      if (linkSection === section) {
        link.classList.add('text-blue-600', 'bg-blue-50');
        link.classList.remove('text-gray-700');
      } else {
        link.classList.remove('text-blue-600', 'bg-blue-50');
        link.classList.add('text-gray-700');
      }
    });
  },
  navigateTo(section) {
    // Update URL hash without reloading page
    const newHash = section ? `#${section}` : '#';
    window.location.hash = newHash;
    
    this.currentPath = section;
    this.scrollToSection(section);
    this.updateActiveNav(section);
    this.menuOpen = false; // Tutup mobile menu setelah navigasi
  }
}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Vendor Partnership & SEO Performance</title>

  <!-- Meta SEO -->
  <meta name="description" content="Platform kolaborasi vendor & tim SEO: kelola profil, layanan, leads, dan komisi dengan transparan." />
  <meta property="og:title" content="Vendor Partnership & SEO Performance" />
  <meta property="og:description" content="Kolaborasi vendor & SEO yang transparan dan efisien." />
  <meta property="og:type" content="website" />
  <meta property="og:image" content="/assets/img/logo/icon.png" />
  <link rel="icon" href="/assets/img/logo/icon.png" type="image/png" />

  <!-- Fonts & CSS -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js" defer></script>

  <style>
    :root{
      --blue:#3b82f6;
      --blue-400:#60a5fa;
      --blue-500:#3b82f6;
      --blue-600:#2563eb;
      --blue-700:#1d4ed8;
      --blue-900:#1e3a8a;
    }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: linear-gradient(to bottom, #ffffff 0%, #f0f9ff 100%);
      color: #0f172a;
    }
    .section { scroll-margin-top: 96px; }
    
    /* Premium Liquid Glass Effect */
    .liquid-glass {
      background: rgba(255, 255, 255, 0.75);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 
        0 8px 32px 0 rgba(0, 0, 0, 0.1),
        inset 0 1px 0 0 rgba(255, 255, 255, 0.2);
    }
    
    .liquid-glass-scrolled {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(25px) saturate(200%);
      -webkit-backdrop-filter: blur(25px) saturate(200%);
      border-bottom: 1px solid rgba(255, 255, 255, 0.4);
      box-shadow: 
        0 8px 32px 0 rgba(0, 0, 0, 0.15),
        inset 0 1px 0 0 rgba(255, 255, 255, 0.3);
    }
    
    /* Glassmorphism effect */
    .glass {
      background: rgba(255, 255, 255, 0.7);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    /* Gradient text */
    .gradient-text {
      background: linear-gradient(135deg, #3b82f6 0%, #60a5fa 50%, #93c5fd 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    /* Animated gradient text */
    .animated-gradient-text {
      background-size: 200% 200%;
      animation: animatedText 3s ease infinite;
    }
    
    @keyframes animatedText {
      0% {
        background-position: 0% 50%;
      }
      50% {
        background-position: 100% 50%;
      }
      100% {
        background-position: 0% 50%;
      }
    }
    
    /* Animated gradient background */
    .gradient-bg {
      background: linear-gradient(-45deg, #3b82f6, #60a5fa, #93c5fd, #bfdbfe);
      background-size: 400% 400%;
      animation: gradientShift 15s ease infinite;
    }
    
    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    
    /* Floating animation */
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
    }
    
    .float { animation: float 6s ease-in-out infinite; }
    
    /* Card hover effect */
    .card-hover {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .card-hover:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
    }
    
    /* Reveal animation */
    [data-reveal] { 
      opacity: 0; 
      transform: translateY(30px);
    }
    
    /* Shine effect */
    .shine {
      position: relative;
      overflow: hidden;
    }
    
    .shine::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }
    
    .shine:hover::before {
      left: 100%;
    }
    
    /* Pulse effect */
    @keyframes pulse-ring {
      0% { transform: scale(0.9); opacity: 1; }
      100% { transform: scale(1.3); opacity: 0; }
    }
    
    .pulse-ring {
      animation: pulse-ring 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    /* Premium button styles */
    .btn-glass {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      transition: all 0.3s ease;
    }
    
    .btn-glass:hover {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
    }
    
    /* PERBAIKAN: Hamburger menu animation yang lebih baik */
    .hamburger-top,
    .hamburger-middle,
    .hamburger-bottom {
      position: absolute;
      width: 20px;
      height: 2px;
      background-color: #374151;
      left: 50%;
      transform: translateX(-50%);
      transition: all 0.3s ease;
    }
    
    .hamburger-top {
      top: 30%;
    }
    
    .hamburger-middle {
      top: 50%;
    }
    
    .hamburger-bottom {
      top: 70%;
    }
    
    /* State ketika menu terbuka */
    .menu-open .hamburger-top {
      transform: translateX(-50%) rotate(45deg) translateY(7px);
    }
    
    .menu-open .hamburger-middle {
      opacity: 0;
    }
    
    .menu-open .hamburger-bottom {
      transform: translateX(-50%) rotate(-45deg) translateY(-7px);
    }

    /* PERBAIKAN: Google Maps Embed Styling untuk Mobile */
    .map-container {
      position: relative;
      overflow: hidden;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      width: 100%;
    }

    .map-container iframe {
      border: 0;
      filter: grayscale(20%) contrast(90%);
      transition: filter 0.3s ease;
      width: 100%;
      display: block;
    }

    .map-container:hover iframe {
      filter: grayscale(0%) contrast(100%);
    }

    /* Responsive height untuk maps */
    .map-responsive {
      height: 200px;
    }

    @media (min-width: 640px) {
      .map-responsive {
        height: 250px;
      }
    }

    @media (min-width: 768px) {
      .map-responsive {
        height: 200px;
      }
    }

    @media (min-width: 1024px) {
      .map-responsive {
        height: 180px;
      }
    }

    /* NEW: Footer Texture Styles */
    .footer-texture {
      position: relative;
      overflow: hidden;
    }

    .footer-texture::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-image: 
        radial-gradient(circle at 25% 25%, rgba(59, 130, 246, 0.1) 0%, transparent 50%),
        radial-gradient(circle at 75% 75%, rgba(96, 165, 250, 0.08) 0%, transparent 50%),
        linear-gradient(45deg, transparent 48%, rgba(255, 255, 255, 0.03) 50%, transparent 52%);
      background-size: 
        400px 400px,
        300px 300px,
        60px 60px;
      opacity: 0.6;
      pointer-events: none;
    }

    .footer-texture::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: 
        linear-gradient(135deg, transparent 0%, rgba(0, 0, 0, 0.1) 100%);
      pointer-events: none;
    }

    .footer-content {
      position: relative;
      z-index: 1;
    }

    /* Subtle grid pattern overlay */
    .grid-pattern {
      background-image: 
        linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
      background-size: 20px 20px;
    }

    /* Diamond pattern alternative */
    .diamond-pattern {
      background-image: 
        linear-gradient(45deg, rgba(59, 130, 246, 0.05) 25%, transparent 25%),
        linear-gradient(-45deg, rgba(59, 130, 246, 0.05) 25%, transparent 25%),
        linear-gradient(45deg, transparent 75%, rgba(59, 130, 246, 0.05) 75%),
        linear-gradient(-45deg, transparent 75%, rgba(59, 130, 246, 0.05) 75%);
      background-size: 40px 40px;
      background-position: 0 0, 0 20px, 20px -20px, -20px 0px;
    }

    /* Wave texture */
    .wave-texture {
      background-image: 
        radial-gradient(circle at 100% 50%, transparent 20%, rgba(0, 0, 0, 0.1) 21%, rgba(0, 0, 0, 0.1) 34%, transparent 35%, transparent),
        radial-gradient(circle at 0% 50%, transparent 20%, rgba(0, 0, 0, 0.1) 21%, rgba(0, 0, 0, 0.1) 34%, transparent 35%, transparent);
      background-size: 60px 60px;
      background-position: 0 0, 30px 30px;
    }

    @media (prefers-reduced-motion: reduce) {
      [data-reveal] { opacity: 1 !important; transform: none !important; }
      .transition, .transform, .gradient-bg, .float { transition: none !important; animation: none !important; }
      .animated-gradient-text { animation: none !important; }
    }
    
    /* Hide elements with x-cloak */
    [x-cloak] { display: none !important; }
  </style>
</head>
<body class="antialiased overflow-x-hidden">

  <!-- NAV -->
  <header class="fixed top-0 inset-x-0 z-50 transition-all duration-500"
           :class="atTop ? 'liquid-glass' : 'liquid-glass-scrolled'">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <div class="h-16 flex items-center justify-between">
        <!-- Logo dengan teks yang muncul di mobile juga -->
        <a href="/" class="flex items-center space-x-3 group" @click.prevent="navigateTo('')">
          <div class="relative">
            <img src="/assets/img/logo/icon.png" alt="Logo" class="h-9 w-9 rounded-xl shadow-md transform group-hover:scale-110 transition-transform duration-300">
            <div class="absolute inset-0 rounded-xl bg-blue-500 opacity-0 group-hover:opacity-20 transition-opacity"></div>
          </div>
          <!-- Menghapus 'hidden sm:block' agar teks muncul di mobile -->
          <span class="font-black text-lg gradient-text">Vendor SEO</span>
        </a>

        <nav class="hidden md:flex items-center space-x-1">
          <a href="#features" @click.prevent="navigateTo('features')" data-section="features" class="nav-link px-4 py-2 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-all duration-300 text-sm font-medium">Fitur</a>
          <a href="#how" @click.prevent="navigateTo('how')" data-section="how" class="nav-link px-4 py-2 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-all duration-300 text-sm font-medium">Cara Kerja</a>
          <a href="#testimoni" @click.prevent="navigateTo('testimoni')" data-section="testimoni" class="nav-link px-4 py-2 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-all duration-300 text-sm font-medium">Testimoni</a>
          <a href="#faq" @click.prevent="navigateTo('faq')" data-section="faq" class="nav-link px-4 py-2 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-all duration-300 text-sm font-medium">FAQ</a>
        </nav>

        <div class="hidden md:flex items-center space-x-3">
          <a href="<?= site_url('login'); ?>" class="px-5 py-2.5 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-all duration-300 font-medium text-sm">
            Masuk
          </a>
          <a href="<?= site_url('register'); ?>" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-blue-400 text-white hover:from-blue-700 hover:to-blue-500 transition-all duration-300 transform hover:scale-105 font-medium text-sm shadow-lg shine">
            Daftar Gratis
          </a>
        </div>

        <!-- PERBAIKAN: Mobile menu button dengan animasi hamburger ke silang yang benar-benar berfungsi -->
        <button class="md:hidden p-2.5 rounded-xl btn-glass hover:bg-white/20 transition-colors relative w-10 h-10"
                @click="menuOpen = !menuOpen" 
                :class="menuOpen ? 'menu-open' : ''"
                aria-label="Toggle menu">
          <!-- Icon hamburger 3 garis yang berubah menjadi silang -->
          <div class="relative w-full h-full">
            <span class="hamburger-top"></span>
            <span class="hamburger-middle"></span>
            <span class="hamburger-bottom"></span>
          </div>
        </button>
      </div>

      <!-- Mobile menu -->
      <div class="md:hidden pb-4 space-y-2" x-show="menuOpen" x-cloak
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="opacity-0 -translate-y-4"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 -translate-y-4">
        <a href="#features" @click.prevent="navigateTo('features'); menuOpen=false" data-section="features" class="nav-link block py-3 px-4 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-colors text-sm font-medium">Fitur</a>
        <a href="#how" @click.prevent="navigateTo('how'); menuOpen=false" data-section="how" class="nav-link block py-3 px-4 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-colors text-sm font-medium">Cara Kerja</a>
        <a href="#testimoni" @click.prevent="navigateTo('testimoni'); menuOpen=false" data-section="testimoni" class="nav-link block py-3 px-4 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-colors text-sm font-medium">Testimoni</a>
        <a href="#faq" @click.prevent="navigateTo('faq'); menuOpen=false" data-section="faq" class="nav-link block py-3 px-4 rounded-xl btn-glass text-gray-700 hover:text-blue-600 transition-colors text-sm font-medium">FAQ</a>
        <div class="pt-3 grid grid-cols-2 gap-3">
          <a href="<?= site_url('login'); ?>" class="text-center py-3 rounded-xl btn-glass text-gray-700 font-medium text-sm">Masuk</a>
          <a href="<?= site_url('register'); ?>" class="text-center py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-400 text-white font-medium text-sm">Daftar</a>
        </div>
      </div>
    </div>
  </header>

  <!-- HERO -->
  <section class="relative min-h-screen flex items-center overflow-hidden pt-16">
    <!-- Animated background -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-blue-100 to-blue-50"></div>
    <div class="absolute inset-0 opacity-30">
      <div class="absolute top-20 left-10 w-72 h-72 bg-blue-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
      <div class="absolute top-40 right-10 w-72 h-72 bg-blue-300 rounded-full mix-blend-multiply filter blur-3xl" style="animation-delay: 2s;"></div>
      <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-3xl" style="animation-delay: 4s;"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 w-full py-16">
      <div class="grid lg:grid-cols-2 gap-10 items-center">
        <div data-reveal>
          <!-- Badge -->
          <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full glass mb-5 border border-blue-200">
            <span class="relative flex h-3 w-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
            </span>
            <span class="text-xs font-semibold text-gray-700">Platform #1 untuk Vendor</span>
          </div>

          <h1 class="text-4xl md:text-5xl lg:text-6xl font-black leading-tight text-gray-900 mb-5">
            Kerja Sama <br/>
            <span class="gradient-text animated-gradient-text">Transparan</span> & <br/>
            <span class="gradient-text animated-gradient-text">Efektif</span>
          </h1>
          
          <p class="text-lg text-gray-600 mb-6 leading-relaxed">
            Kelola profil, layanan, leads, dan laporan komisi dalam satu platform yang mudah. 
            <strong class="text-gray-900">Tingkatkan kolaborasi</strong> dan performa SEO bisnis Anda sekarang.
          </p>

          <div class="flex flex-wrap gap-3 mb-10">
            <a href="<?= site_url('register'); ?>" class="group relative px-6 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-blue-400 text-white font-bold hover:from-blue-700 hover:to-blue-500 transition-all duration-300 transform hover:scale-105 shadow-2xl shine text-sm">
              <span class="relative z-10">Mulai Gratis Sekarang</span>
            </a>
            <a href="#features" @click.prevent="navigateTo('features')" class="px-6 py-3 rounded-xl font-bold border-2 border-gray-300 text-gray-700 hover:border-blue-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300 flex items-center space-x-2 text-sm">
              <span>Lihat Fitur</span>
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
              </svg>
            </a>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-3 gap-4">
            <div class="glass rounded-2xl p-4 text-center transform hover:scale-105 transition-transform duration-300" data-reveal data-reveal-delay="100">
              <div class="text-2xl font-black gradient-text mb-1">98%</div>
              <div class="text-xs text-gray-600 font-medium">Vendor Aktif</div>
            </div>
            <div class="glass rounded-2xl p-4 text-center transform hover:scale-105 transition-transform duration-300" data-reveal data-reveal-delay="200">
              <div class="text-2xl font-black gradient-text mb-1">+12K</div>
              <div class="text-xs text-gray-600 font-medium">Leads Proses</div>
            </div>
            <div class="glass rounded-2xl p-4 text-center transform hover:scale-105 transition-transform duration-300" data-reveal data-reveal-delay="300">
              <div class="text-2xl font-black gradient-text mb-1">5 Min</div>
              <div class="text-xs text-gray-600 font-medium">Aktivasi</div>
            </div>
          </div>
        </div>

        <!-- Hero Image -->
        <div class="hidden lg:flex justify-center items-center" data-reveal data-reveal-delay="200">
          <div class="relative">
            <div class="absolute -inset-4 bg-gradient-to-r from-blue-600 to-blue-400 rounded-3xl blur-2xl opacity-20 animate-pulse"></div>
            <img src="/assets/img/logo/elemen.png" alt="Dashboard" class="relative w-full max-w-xl rounded-3xl shadow-2xl float border-4 border-white">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- LOGO STRIP -->
  <section class="py-12 bg-white section">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <p class="text-center text-gray-500 font-semibold mb-6 uppercase tracking-wide text-xs" data-reveal>Dipercaya oleh vendor dari berbagai industri</p>
      <div class="grid grid-cols-3 md:grid-cols-6 gap-6 items-center">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition-all duration-300 hover:scale-110" alt="" data-reveal data-reveal-delay="50">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition-all duration-300 hover:scale-110" alt="" data-reveal data-reveal-delay="100">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition-all duration-300 hover:scale-110" alt="" data-reveal data-reveal-delay="150">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition-all duration-300 hover:scale-110" alt="" data-reveal data-reveal-delay="200">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition-all duration-300 hover:scale-110" alt="" data-reveal data-reveal-delay="250">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition-all duration-300 hover:scale-110" alt="" data-reveal data-reveal-delay="300">
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="section py-20 bg-gradient-to-b from-blue-50 to-white relative overflow-hidden">
    <div class="absolute inset-0 opacity-5">
      <div class="absolute top-0 left-1/4 w-96 h-96 bg-blue-500 rounded-full filter blur-3xl"></div>
      <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-blue-400 rounded-full filter blur-3xl"></div>
    </div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6">
      <div class="text-center mb-14">
        <div class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-600 font-bold text-xs mb-3" data-reveal>✨ FITUR UNGGULAN</div>
        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-3" data-reveal>Semua yang Dibutuhkan Vendor</h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto" data-reveal data-reveal-delay="100">Fokus di penjualan, sisanya biar sistem yang bantu.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="glass p-6 rounded-3xl card-hover group" data-reveal data-reveal-delay="100">
          <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white grid place-content-center font-black text-2xl mb-5 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
            1
          </div>
          <h3 class="font-black text-xl mb-2 text-gray-900">Manajemen Profil & Layanan</h3>
          <p class="text-gray-600 leading-relaxed text-sm">Kelola data bisnis, unggah katalog, tetapkan layanan & harga dalam satu dashboard yang intuitif.</p>
        </div>

        <div class="glass p-6 rounded-3xl card-hover group" data-reveal data-reveal-delay="200">
          <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-500 text-white grid place-content-center font-black text-2xl mb-5 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
            2
          </div>
          <h3 class="font-black text-xl mb-2 text-gray-900">Leads Tracking</h3>
          <p class="text-gray-600 leading-relaxed text-sm">Laporkan leads tiap minggu atau tiap bulan, dan lihat tingkat konversi dengan visualisasi data yang jelas.</p>
        </div>

        <div class="glass p-6 rounded-3xl card-hover group" data-reveal data-reveal-delay="300">
          <div class="h-14 w-14 rounded-2xl bg-gradient-to-br from-blue-300 to-blue-400 text-white grid place-content-center font-black text-2xl mb-5 transform group-hover:scale-110 group-hover:rotate-3 transition-all duration-300">
            3
          </div>
          <h3 class="font-black text-xl mb-2 text-gray-900">Komisi Transparan</h3>
          <p class="text-gray-600 leading-relaxed text-sm">Laporkan komisi real-time dengan periode pembayaran yang jelas dan terukur. No drama, semua tercatat!</p>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section id="how" class="section py-20 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <div class="text-center mb-14">
        <div class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-600 font-bold text-xs mb-3" data-reveal>🚀 MULAI DENGAN MUDAH</div>
        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-3" data-reveal>Alur Kerja Sederhana</h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto" data-reveal data-reveal-delay="100">Hanya 4 langkah untuk mulai kolaborasi.</p>
      </div>

      <div class="grid md:grid-cols-4 gap-5">
        <div class="relative p-6 rounded-3xl border-2 border-gray-200 hover:border-blue-500 transition-all duration-300 card-hover" data-reveal data-reveal-delay="100">
          <div class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-xs">Langkah 1</div>
          <div class="mt-3">
            <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-600 font-black text-xl grid place-content-center mb-3">📝</div>
            <h3 class="font-black text-lg mb-2 text-gray-900">Daftar Akun</h3>
            <p class="text-gray-600 text-sm">Buat akun vendor & lengkapi profil bisnis Anda dengan mudah.</p>
          </div>
        </div>

        <div class="relative p-6 rounded-3xl border-2 border-gray-200 hover:border-blue-400 transition-all duration-300 card-hover" data-reveal data-reveal-delay="200">
          <div class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 text-white font-bold text-xs">Langkah 2</div>
          <div class="mt-3">
            <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-500 font-black text-xl grid place-content-center mb-3">⚡</div>
            <h3 class="font-black text-lg mb-2 text-gray-900">Aktifasi Layanan</h3>
            <p class="text-gray-600 text-sm">Tambahkan layanan/produk yang ditawarkan kepada klien.</p>
          </div>
        </div>

        <div class="relative p-6 rounded-3xl border-2 border-gray-200 hover:border-blue-300 transition-all duration-300 card-hover" data-reveal data-reveal-delay="300">
          <div class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-gradient-to-r from-blue-400 to-blue-500 text-white font-bold text-xs">Langkah 3</div>
          <div class="mt-3">
            <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-400 font-black text-xl grid place-content-center mb-3">📊</div>
            <h3 class="font-black text-lg mb-2 text-gray-900">Terima Leads</h3>
            <p class="text-gray-600 text-sm">Leads masuk dari hasil SEO & kamu proses hingga closing.</p>
          </div>
        </div>

        <div class="relative p-6 rounded-3xl border-2 border-gray-200 hover:border-blue-200 transition-all duration-300 card-hover" data-reveal data-reveal-delay="400">
          <div class="absolute -top-3 left-6 px-3 py-0.5 rounded-full bg-gradient-to-r from-blue-300 to-blue-400 text-white font-bold text-xs">Langkah 4</div>
          <div class="mt-3">
            <div class="h-12 w-12 rounded-xl bg-blue-100 text-blue-300 font-black text-xl grid place-content-center mb-3">💰</div>
            <h3 class="font-black text-lg mb-2 text-gray-900">Komisi Cair</h3>
            <p class="text-gray-600 text-sm">Kirim laporan komisi sesuai kesepakatan awal dan dibayar per periode.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section id="testimoni" class="section py-20 bg-gradient-to-b from-blue-50 to-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
      <div class="text-center mb-14">
        <div class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-600 font-bold text-xs mb-3" data-reveal>💬 TESTIMONI</div>
        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-3" data-reveal>Apa Kata Vendor</h2>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto" data-reveal data-reveal-delay="100">Testimoni nyata dari partner kami yang sukses.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="glass p-6 rounded-3xl card-hover" data-reveal data-reveal-delay="100">
          <div class="flex mb-3">
            <span class="text-yellow-400 text-xl">⭐⭐⭐⭐⭐</span>
          </div>
          <p class="text-gray-700 text-base mb-5 italic leading-relaxed">"Leads makin rapi dan transparan. Tim kami jadi lebih fokus closing dan revenue meningkat drastis!"</p>
          <div class="flex items-center space-x-3">
            <img src="https://i.pravatar.cc/60?img=1" class="h-12 w-12 rounded-full ring-4 ring-blue-100" alt="">
            <div>
              <div class="font-black text-gray-900">Aksara Label</div>
              <div class="text-sm text-gray-500">Konveksi & Label</div>
            </div>
          </div>
        </div>

        <div class="glass p-6 rounded-3xl card-hover" data-reveal data-reveal-delay="200">
          <div class="flex mb-3">
            <span class="text-yellow-400 text-xl">⭐⭐⭐⭐⭐</span>
          </div>
          <p class="text-gray-700 text-base mb-5 italic leading-relaxed">"Laporan komisi jelas dan real-time. Tidak ada drama, semua tercatat dengan detail!"</p>
          <div class="flex items-center space-x-3">
            <img src="https://i.pravatar.cc/60?img=2" class="h-12 w-12 rounded-full ring-4 ring-blue-100" alt="">
            <div>
              <div class="font-black text-gray-900">Bangkalan Print</div>
              <div class="text-sm text-gray-500">Percetakan</div>
            </div>
          </div>
        </div>

        <div class="glass p-6 rounded-3xl card-hover" data-reveal data-reveal-delay="300">
          <div class="flex mb-3">
            <span class="text-yellow-400 text-xl">⭐⭐⭐⭐⭐</span>
          </div>
          <p class="text-gray-700 text-base mb-5 italic leading-relaxed">"Onboarding cepat, dashboardnya gampang dipakai. Bahkan untuk yang gaptek sekalipun!"</p>
          <div class="flex items-center space-x-3">
            <img src="https://i.pravatar.cc/60?img=3" class="h-12 w-12 rounded-full ring-4 ring-blue-100" alt="">
            <div>
              <div class="font-black text-gray-900">Lingua Course</div>
              <div class="text-sm text-gray-500">Pendidikan</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="section py-20 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6">
      <div class="text-center mb-14">
        <div class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-600 font-bold text-xs mb-3" data-reveal>❓ BANTUAN</div>
        <h2 class="text-3xl md:text-4xl font-black text-gray-900 mb-3" data-reveal>Pertanyaan Umum</h2>
        <p class="text-lg text-gray-600" data-reveal data-reveal-delay="100">Temukan jawaban untuk pertanyaan yang sering diajukan.</p>
      </div>

      <div class="space-y-3" x-data="{ open: null }">
        <div class="glass rounded-2xl overflow-hidden card-hover" data-reveal data-reveal-delay="100">
          <button @click="open = open === 1 ? null : 1" class="w-full p-5 text-left flex items-center justify-between hover:bg-blue-50 transition-colors">
            <span class="font-bold text-base text-gray-900 pr-4">Apakah ada biaya berlangganan?</span>
            <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="open === 1" x-collapse class="px-5 pb-5">
            <p class="text-gray-600 leading-relaxed text-sm">Saat ini tidak ada biaya berlangganan. Skema komisi mengikuti kesepakatan dengan tim Imersa yang fair dan transparan.</p>
          </div>
        </div>

        <div class="glass rounded-2xl overflow-hidden card-hover" data-reveal data-reveal-delay="150">
          <button @click="open = open === 2 ? null : 2" class="w-full p-5 text-left flex items-center justify-between hover:bg-blue-50 transition-colors">
            <span class="font-bold text-base text-gray-900 pr-4">Bagaimana integrasi WhatsApp?</span>
            <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="open === 2" x-collapse class="px-5 pb-5">
            <p class="text-gray-600 leading-relaxed text-sm">Tidak menggunakan API WA. Imersa menyediakan nomor khusus untuk vendor login di HP lalu tim SEO akses via WA Web (by appointment) untuk koordinasi yang lebih efisien.</p>
          </div>
        </div>

        <div class="glass rounded-2xl overflow-hidden card-hover" data-reveal data-reveal-delay="200">
          <button @click="open = open === 3 ? null : 3" class="w-full p-5 text-left flex items-center justify-between hover:bg-blue-50 transition-colors">
            <span class="font-bold text-base text-gray-900 pr-4">Bisakah export data?</span>
            <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="open === 3" x-collapse class="px-5 pb-5">
            <p class="text-gray-600 leading-relaxed text-sm">Ya, leads dan laporan komisi dapat diekspor dalam format Excel atau CSV sesuai kebutuhan analisis dan pelaporan Anda.</p>
          </div>
        </div>

        <div class="glass rounded-2xl overflow-hidden card-hover" data-reveal data-reveal-delay="250">
          <button @click="open = open === 4 ? null : 4" class="w-full p-5 text-left flex items-center justify-between hover:bg-blue-50 transition-colors">
            <span class="font-bold text-base text-gray-900 pr-4">Berapa lama proses setup awal?</span>
            <svg class="w-5 h-5 text-blue-600 transform transition-transform duration-300" :class="open === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
          </button>
          <div x-show="open === 4" x-collapse class="px-5 pb-5">
            <p class="text-gray-600 leading-relaxed text-sm">Rata-rata hanya 5 menit untuk aktivasi lengkap. Anda bisa langsung mulai menerima dan mengelola leads setelah profil disetup.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA STRIP -->
  <section class="py-16 relative overflow-hidden">
    <div class="absolute inset-0 gradient-bg"></div>
    <div class="absolute inset-0 bg-black opacity-10"></div>
    
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6">
      <div class="text-center">
        <h3 class="text-2xl md:text-4xl font-black text-white mb-5 animated-gradient-text" data-reveal>
          Siap Kolaborasi Lebih Efektif?
        </h3>
        <p class="text-lg text-white/90 mb-8 max-w-2xl mx-auto animated-gradient-text" data-reveal data-reveal-delay="100">
          Aktifasi akun vendor dalam hitungan menit dan mulai terima leads berkualitas hari ini juga.
        </p>
        <div class="flex flex-wrap justify-center gap-3" data-reveal data-reveal-delay="200">
          <a href="<?= site_url('register'); ?>" class="px-8 py-3 rounded-2xl bg-white text-blue-700 font-black text-base hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-2xl shine">
            Buat Akun Vendor Gratis
          </a>
          <a href="#contact" @click.prevent="navigateTo('contact')" class="px-8 py-3 rounded-2xl border-3 border-white text-white font-black text-base hover:bg-white hover:text-blue-700 transition-all duration-300 transform hover:scale-105">
            Hubungi Kami
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER dengan Texture -->
<footer id="contact" class="bg-gray-900 text-gray-300 footer-texture grid-pattern">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 footer-content">
    <div class="grid md:grid-cols-4 gap-8">
      <div data-reveal class="md:col-span-1">
        <div class="flex items-center space-x-3 mb-5">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="h-9 w-9 rounded-xl">
          <span class="font-black text-lg text-white">Vendor SEO</span>
        </div>
        <p class="text-gray-400 leading-relaxed mb-5 text-sm">Platform kolaborasi vendor & tim SEO untuk proses yang transparan, terukur, dan menguntungkan.</p>
        <div class="flex space-x-3">
          <a href="#" class="h-9 w-9 rounded-xl bg-gray-800 hover:bg-blue-600 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
          </a>
          <a href="#" class="h-9 w-9 rounded-xl bg-gray-800 hover:bg-blue-400 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>
          </a>
          <a href="#" class="h-9 w-9 rounded-xl bg-gray-800 hover:bg-blue-300 flex items-center justify-center transition-colors">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>
          </a>
        </div>
      </div>

      <div data-reveal data-reveal-delay="100" class="md:col-span-1">
        <h4 class="font-black text-white mb-3 text-base">Produk</h4>
        <ul class="space-y-2">
          <li><a href="#features" @click.prevent="navigateTo('features')" data-section="features" class="nav-link hover:text-white transition-colors text-sm">Fitur Platform</a></li>
          <li><a href="#how" @click.prevent="navigateTo('how')" data-section="how" class="nav-link hover:text-white transition-colors text-sm">Cara Kerja</a></li>
          <li><a href="#testimoni" @click.prevent="navigateTo('testimoni')" data-section="testimoni" class="nav-link hover:text-white transition-colors text-sm">Testimoni Vendor</a></li>
        </ul>
      </div>

      <div data-reveal data-reveal-delay="150" class="md:col-span-1">
        <h4 class="font-black text-white mb-3 text-base">Dukungan</h4>
        <ul class="space-y-2">
          <li><a href="#faq" @click.prevent="navigateTo('faq')" data-section="faq" class="nav-link hover:text-white transition-colors text-sm">FAQ</a></li>
          <li><a href="#" class="hover:text-white transition-colors text-sm">Dokumentasi</a></li>
          <li><a href="#" class="hover:text-white transition-colors text-sm">Pusat Bantuan</a></li>
        </ul>
      </div>

      <!-- PERBAIKAN: Bagian kontak dan maps dengan layout yang lebih baik untuk mobile -->
      <div data-reveal data-reveal-delay="200" class="md:col-span-1">
        <h4 class="font-black text-white mb-3 text-base">Kontak & Lokasi</h4>
        <ul class="space-y-3 text-sm">
          <!-- Email dengan ikon dan warna merah -->
          <li class="flex items-start space-x-2">
            <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="break-words">mail@imersa.co.id</span>
          </li>
          
          <!-- WhatsApp dengan ikon dan warna hijau -->
          <li class="flex items-start space-x-2">
            <svg class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
            </svg>
            <span>+62 857-5589-6233</span>
          </li>
          
          <!-- Lokasi dengan ikon pin berwarna merah -->
          <li class="flex items-start space-x-2">
            <svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Jl. Puntodewo No. 2 Dsn. Baron Timur, Ds. Baron, Kec. Baron, Nganjuk 64394</span>
          </li>
        </ul>
        
        <!-- Maps dengan responsive height -->
        <div class="mt-4">
          <div class="map-container map-responsive">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3954.6601164590224!2d112.05888057455128!3d-7.611913475246488!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e784707ce38f817%3A0x6b4e5aaf62859ec5!2sImersa%20Academy!5e0!3m2!1sid!2sid!4v1761645689669!5m2!1sid!2sid"
              width="100%" 
              height="100%" 
              style="border:0;" 
              allowfullscreen="" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade"
              title="Lokasi Imersa Academy">
            </iframe>
          </div>
        </div>
      </div>
    </div>

    <div class="border-t border-gray-800 pt-6 mt-8">
      <div class="flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
        <p class="text-xs text-gray-400 text-center md:text-left">
          &copy; 2025 Vendor Partnership & SEO Performance. All rights reserved.
        </p>
      </div>
    </div>
  </div>
</footer>

  <!-- Back-to-top button -->
  <button id="toTop" title="Kembali ke atas"
          class="fixed bottom-6 right-6 h-12 w-12 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-2xl hover:from-blue-700 hover:to-blue-500 transition-all duration-300 transform hover:scale-110 z-40 hidden">
    <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
    </svg>
  </button>

  <!-- Scripts -->
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      // Intersection Observer for reveal
      if (!prefersReduced && window.anime) {
        const io = new IntersectionObserver((entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              const delay = parseInt(entry.target.getAttribute('data-reveal-delay') || '0', 10);
              anime({
                targets: entry.target,
                opacity: [0, 1],
                translateY: [30, 0],
                easing: 'easeOutCubic',
                duration: 800,
                delay
              });
              io.unobserve(entry.target);
            }
          });
        }, { threshold: 0.1 });

        document.querySelectorAll('[data-reveal]').forEach(el => io.observe(el));
      } else {
        document.querySelectorAll('[data-reveal]').forEach(el => {
          el.style.opacity = 1;
          el.style.transform = 'none';
        });
      }

      // Back-to-top
      const toTop = document.getElementById('toTop');
      const toggleToTop = () => {
        if (window.scrollY > 400) {
          toTop.classList.remove('hidden');
          toTop.classList.add('flex', 'items-center', 'justify-center');
        } else {
          toTop.classList.add('hidden');
          toTop.classList.remove('flex', 'items-center', 'justify-center');
        }
      };
      window.addEventListener('scroll', toggleToTop);
      toTop.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        // Clear hash when going to top
        if (window.location.hash) {
          window.history.pushState({}, '', window.location.pathname);
        }
      });
      toggleToTop();

      // Handle contact link
      document.querySelectorAll('a[href="#contact"]').forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          document.getElementById('contact').scrollIntoView({ behavior: 'smooth' });
        });
      });
    });
  </script>
</body>
</html>