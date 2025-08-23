<!DOCTYPE html>
<html lang="id" x-data="{ atTop: true, menuOpen: false }" x-init="
  window.addEventListener('scroll', () => atTop = window.scrollY < 10)
">
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js" defer></script>

  <style>
    :root{
      --blue:#2563eb; /* tailwind blue-600 */
      --blue-700:#1d4ed8;
    }
    html { scroll-behavior: smooth; }
    body {
      font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, "Helvetica Neue", Arial, sans-serif;
      background-color: #ffffff;
      color: #111827; /* gray-900 */
    }
    .section { scroll-margin-top: 96px; }
    /* reveal default state */
    [data-reveal] { opacity: 0; transform: translateY(18px); }
    /* reduce motion accessibility */
    @media (prefers-reduced-motion: reduce) {
      [data-reveal] { opacity: 1 !important; transform: none !important; }
      .transition, .transform { transition: none !important; }
    }
  </style>
</head>
<body class="bg-white text-gray-900 antialiased">

  <!-- NAV -->
  <header class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
           :class="atTop ? 'bg-white/80 backdrop-blur-lg' : 'bg-white shadow-sm'">
    <div class="max-w-7xl mx-auto px-6">
      <div class="h-16 flex items-center justify-between">
        <a href="#" class="flex items-center space-x-3">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="h-9 w-9 rounded-full">
          <span class="font-extrabold text-lg text-blue-700">Vendor SEO Platform</span>
        </a>

        <nav class="hidden md:flex items-center space-x-8">
          <a href="#features" class="text-gray-700 hover:text-blue-600 transition">Fitur</a>
          <a href="#how" class="text-gray-700 hover:text-blue-600 transition">Cara Kerja</a>
          <a href="#testimoni" class="text-gray-700 hover:text-blue-600 transition">Testimoni</a>
          <a href="#faq" class="text-gray-700 hover:text-blue-600 transition">FAQ</a>
        </nav>

        <div class="hidden md:flex items-center space-x-3">
          <a href="<?= site_url('login'); ?>"
             class="py-2 px-4 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 transition">
            Masuk
          </a>
          <a href="<?= site_url('register'); ?>"
             class="py-2 px-4 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition transform hover:scale-105">
            Daftar
          </a>
        </div>

        <!-- Mobile -->
        <button class="md:hidden inline-flex items-center justify-center p-2 rounded-lg border border-gray-300"
                @click="menuOpen = !menuOpen" aria-label="Toggle menu">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path x-show="!menuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
            <path x-show="menuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <!-- Mobile menu -->
      <div class="md:hidden pt-2 pb-4 space-y-2" x-show="menuOpen" x-cloak>
        <a href="#features" @click="menuOpen=false" class="block py-2 text-gray-700 hover:text-blue-600">Fitur</a>
        <a href="#how" @click="menuOpen=false" class="block py-2 text-gray-700 hover:text-blue-600">Cara Kerja</a>
        <a href="#testimoni" @click="menuOpen=false" class="block py-2 text-gray-700 hover:text-blue-600">Testimoni</a>
        <a href="#faq" @click="menuOpen=false" class="block py-2 text-gray-700 hover:text-blue-600">FAQ</a>
        <div class="pt-2 flex items-center gap-3">
          <a href="<?= site_url('login'); ?>" class="flex-1 text-center py-2 rounded-lg border border-gray-300 text-gray-700">Masuk</a>
          <a href="<?= site_url('register'); ?>" class="flex-1 text-center py-2 rounded-lg bg-blue-600 text-white">Daftar</a>
        </div>
      </div>
    </div>
  </header>

  <!-- HERO -->
  <section class="relative min-h-screen flex items-center bg-cover bg-center"
           style="background-image:url('/assets/img/logo/bg2.jpg');">
    <div class="absolute inset-0 bg-gradient-to-r from-white via-white/85 to-white"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-6 w-full">
      <div class="grid md:grid-cols-2 gap-10 items-center">
        <div data-reveal data-reveal-delay="0">
          <h1 class="text-4xl md:text-5xl font-extrabold leading-tight text-gray-900">
            Kerja Sama Vendor <span class="text-blue-600">Transparan</span> & Efektif
          </h1>
          <p class="mt-4 text-lg text-gray-700">
            Kelola profil, layanan, leads, dan laporan komisi dalam satu platform yang mudah digunakan.
            Tingkatkan kolaborasi dan performa SEO bisnis Anda sekarang.
          </p>

          <div class="mt-8 flex flex-wrap gap-3">
            <a href="<?= site_url('register'); ?>"
               class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition transform hover:scale-105">
              Mulai Gratis
            </a>
            <a href="#features"
               class="px-6 py-3 rounded-lg font-semibold border border-blue-600 text-blue-600 hover:bg-blue-50 transition">
              Lihat Fitur
            </a>
          </div>

          <div class="mt-8 grid grid-cols-3 gap-6 max-w-md">
            <div class="rounded-xl p-4 text-center bg-blue-50 hover:bg-blue-100 transition transform hover:scale-105" data-reveal data-reveal-delay="100">
              <div class="text-2xl font-extrabold text-blue-700">98%</div>
              <div class="text-sm text-gray-600">Vendor aktif bulanan</div>
            </div>
            <div class="rounded-xl p-4 text-center bg-blue-50 hover:bg-blue-100 transition transform hover:scale-105" data-reveal data-reveal-delay="200">
              <div class="text-2xl font-extrabold text-blue-700">+12K</div>
              <div class="text-sm text-gray-600">Leads terproses</div>
            </div>
            <div class="rounded-xl p-4 text-center bg-blue-50 hover:bg-blue-100 transition transform hover:scale-105" data-reveal data-reveal-delay="300">
              <div class="text-2xl font-extrabold text-blue-700">5 Menit</div>
              <div class="text-sm text-gray-600">Rata-rata aktivasi</div>
            </div>
          </div>
        </div>

        <div class="hidden md:flex justify-end" data-reveal data-reveal-delay="150">
          <img src="/assets/img/logo/elemen.png" alt="Dashboard Mockup"
               class="w-full max-w-xl rounded-xl border border-gray-200 transition transform hover:scale-105">
        </div>
      </div>
    </div>
  </section>

  <!-- LOGO STRIP -->
  <section class="py-12 bg-white section">
    <div class="max-w-7xl mx-auto px-6">
      <p class="text-center text-gray-500 text-sm mb-6" data-reveal>Dipercaya oleh vendor dari berbagai industri</p>
      <div class="grid grid-cols-2 md:grid-cols-6 gap-6 opacity-90">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition" alt="" data-reveal data-reveal-delay="50">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition" alt="" data-reveal data-reveal-delay="100">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition" alt="" data-reveal data-reveal-delay="150">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition" alt="" data-reveal data-reveal-delay="200">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition" alt="" data-reveal data-reveal-delay="250">
        <img src="/assets/img/logo/icon.png" class="h-8 mx-auto grayscale hover:grayscale-0 transition" alt="" data-reveal data-reveal-delay="300">
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section id="features" class="section py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-extrabold" data-reveal>Semua yang Dibutuhkan Vendor</h2>
        <p class="text-gray-600 mt-2" data-reveal data-reveal-delay="100">Fokus di penjualan, sisanya biar sistem yang bantu.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border transform transition hover:-translate-y-1 hover:shadow-md"
             data-reveal data-reveal-delay="50">
          <div class="h-12 w-12 rounded-lg bg-blue-100 text-blue-700 grid place-content-center font-extrabold">1</div>
          <h3 class="mt-4 font-bold text-lg">Manajemen Profil & Layanan</h3>
          <p class="text-gray-600 mt-2">Kelola data bisnis, unggah katalog, tetapkan layanan & harga.</p>
        </div>
        <div class="bg-white p-6 rounded-xl border transform transition hover:-translate-y-1 hover:shadow-md"
             data-reveal data-reveal-delay="150">
          <div class="h-12 w-12 rounded-lg bg-blue-100 text-blue-700 grid place-content-center font-extrabold">2</div>
          <h3 class="mt-4 font-bold text-lg">Leads Tracking</h3>
          <p class="text-gray-600 mt-2">Pantau leads masuk, status proses, dan konversi.</p>
        </div>
        <div class="bg-white p-6 rounded-xl border transform transition hover:-translate-y-1 hover:shadow-md"
             data-reveal data-reveal-delay="250">
          <div class="h-12 w-12 rounded-lg bg-blue-100 text-blue-700 grid place-content-center font-extrabold">3</div>
          <h3 class="mt-4 font-bold text-lg">Komisi Transparan</h3>
          <p class="text-gray-600 mt-2">Laporan komisi real-time dan periode pembayaran yang jelas.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section id="how" class="section py-20 bg-white">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-extrabold" data-reveal>Alur Kerja Sederhana</h2>
        <p class="text-gray-600 mt-2" data-reveal data-reveal-delay="100">Langkah singkat untuk mulai kolaborasi.</p>
      </div>

      <div class="grid md:grid-cols-4 gap-6">
        <div class="p-6 rounded-xl border" data-reveal data-reveal-delay="50">
          <div class="text-sm text-gray-500">Langkah 1</div>
          <h3 class="font-bold mt-1">Daftar Akun</h3>
          <p class="text-gray-600 mt-2">Buat akun vendor & lengkapi profil bisnis.</p>
        </div>
        <div class="p-6 rounded-xl border" data-reveal data-reveal-delay="150">
          <div class="text-sm text-gray-500">Langkah 2</div>
          <h3 class="font-bold mt-1">Aktifasi Layanan</h3>
          <p class="text-gray-600 mt-2">Tambahkan layanan/produk yang ditawarkan.</p>
        </div>
        <div class="p-6 rounded-xl border" data-reveal data-reveal-delay="250">
          <div class="text-sm text-gray-500">Langkah 3</div>
          <h3 class="font-bold mt-1">Terima Leads</h3>
          <p class="text-gray-600 mt-2">Leads masuk & kamu proses hingga closing.</p>
        </div>
        <div class="p-6 rounded-xl border" data-reveal data-reveal-delay="350">
          <div class="text-sm text-gray-500">Langkah 4</div>
          <h3 class="font-bold mt-1">Komisi Cair</h3>
          <p class="text-gray-600 mt-2">Komisi dihitung otomatis per periode.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section id="testimoni" class="section py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-extrabold" data-reveal>Apa Kata Vendor</h2>
        <p class="text-gray-600 mt-2" data-reveal data-reveal-delay="100">Testimoni nyata dari partner kami.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-xl border hover:shadow-md transition transform hover:-translate-y-1"
             data-reveal data-reveal-delay="50">
          <p class="text-gray-700 italic">“Leads makin rapi dan transparan. Tim kami jadi lebih fokus closing.”</p>
          <div class="mt-4 flex items-center space-x-3">
            <img src="https://i.pravatar.cc/60?img=1" class="h-10 w-10 rounded-full" alt="">
            <div>
              <div class="font-semibold">Aksara Label</div>
              <div class="text-sm text-gray-500">Konveksi & Label</div>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl border hover:shadow-md transition transform hover:-translate-y-1"
             data-reveal data-reveal-delay="150">
          <p class="text-gray-700 italic">“Laporan komisi jelas. Tidak ada drama, semua tercatat.”</p>
          <div class="mt-4 flex items-center space-x-3">
            <img src="https://i.pravatar.cc/60?img=2" class="h-10 w-10 rounded-full" alt="">
            <div>
              <div class="font-semibold">Bangkalan Print</div>
              <div class="text-sm text-gray-500">Percetakan</div>
            </div>
          </div>
        </div>
        <div class="bg-white p-6 rounded-xl border hover:shadow-md transition transform hover:-translate-y-1"
             data-reveal data-reveal-delay="250">
          <p class="text-gray-700 italic">“Onboarding cepat, dashboardnya gampang dipakai.”</p>
          <div class="mt-4 flex items-center space-x-3">
            <img src="https://i.pravatar.cc/60?img=3" class="h-10 w-10 rounded-full" alt="">
            <div>
              <div class="font-semibold">Lingua Course</div>
              <div class="text-sm text-gray-500">Pendidikan</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="section py-20 bg-white">
    <div class="max-w-5xl mx-auto px-6">
      <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-extrabold" data-reveal>Pertanyaan Umum</h2>
      </div>

      <div class="space-y-4">
        <details class="bg-gray-50 border rounded-lg p-4 group" data-reveal data-reveal-delay="50">
          <summary class="font-semibold cursor-pointer flex items-center justify-between">
            <span>Apakah ada biaya berlangganan?</span>
            <span class="text-blue-600 transform group-open:rotate-180 transition">▾</span>
          </summary>
          <p class="mt-2 text-gray-600">Saat ini tidak ada biaya berlangganan. Skema komisi mengikuti kesepakatan dengan tim Imersa.</p>
        </details>
        <details class="bg-gray-50 border rounded-lg p-4 group" data-reveal data-reveal-delay="150">
          <summary class="font-semibold cursor-pointer flex items-center justify-between">
            <span>Bagaimana integrasi WhatsApp?</span>
            <span class="text-blue-600 transform group-open:rotate-180 transition">▾</span>
          </summary>
          <p class="mt-2 text-gray-600">Tidak menggunakan API WA. Imersa menyediakan nomor khusus; vendor login di HP lalu tim SEO akses via WA Web (by appointment).</p>
        </details>
        <details class="bg-gray-50 border rounded-lg p-4 group" data-reveal data-reveal-delay="250">
          <summary class="font-semibold cursor-pointer flex items-center justify-between">
            <span>Bisakah export data?</span>
            <span class="text-blue-600 transform group-open:rotate-180 transition">▾</span>
          </summary>
          <p class="mt-2 text-gray-600">Ya, leads dan laporan komisi dapat diekspor sesuai kebutuhan.</p>
        </details>
      </div>

      <div class="text-center mt-10" data-reveal data-reveal-delay="150">
        <a href="<?= site_url('register'); ?>" class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition transform hover:scale-105">
          Daftar Sekarang
        </a>
      </div>
    </div>
  </section>

  <!-- CTA STRIP -->
  <section class="py-14 bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white">
    <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-2 gap-6 items-center">
      <div data-reveal>
        <h3 class="text-2xl md:text-3xl font-extrabold">Siap kolaborasi lebih efektif?</h3>
        <p class="text-white/90 mt-2">Aktifasi akun vendor dalam hitungan menit dan mulai terima leads.</p>
      </div>
      <div class="md:text-right" data-reveal data-reveal-delay="100">
        <a href="<?= site_url('register'); ?>" class="inline-block bg-white text-blue-700 px-6 py-3 rounded-lg font-semibold hover:bg-blue-50 transition transform hover:scale-105">
          Buat Akun Vendor
        </a>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-blue-900 text-blue-100">
    <div class="max-w-7xl mx-auto px-6 py-12 grid md:grid-cols-4 gap-8">
      <div data-reveal>
        <div class="flex items-center space-x-3">
          <img src="/assets/img/logo/icon.png" alt="Logo" class="h-8 w-8 rounded-full">
          <span class="font-bold text-white">Vendor SEO Platform</span>
        </div>
        <p class="mt-4 text-sm text-blue-200">Platform kolaborasi vendor & tim SEO untuk proses yang transparan dan terukur.</p>
      </div>
      <div data-reveal data-reveal-delay="100">
        <h4 class="font-semibold text-white">Produk</h4>
        <ul class="mt-3 space-y-2 text-sm">
          <li><a href="#features" class="hover:text-white">Fitur</a></li>
          <li><a href="#how" class="hover:text-white">Cara Kerja</a></li>
          <li><a href="#testimoni" class="hover:text-white">Testimoni</a></li>
        </ul>
      </div>
      <div data-reveal data-reveal-delay="150">
        <h4 class="font-semibold text-white">Dukungan</h4>
        <ul class="mt-3 space-y-2 text-sm">
          <li><a href="#faq" class="hover:text-white">FAQ</a></li>
          <li><a href="<?= site_url('login'); ?>" class="hover:text-white">Masuk</a></li>
          <li><a href="<?= site_url('register'); ?>" class="hover:text-white">Daftar</a></li>
        </ul>
      </div>
      <div data-reveal data-reveal-delay="200">
        <h4 class="font-semibold text-white">Kontak</h4>
        <ul class="mt-3 space-y-2 text-sm">
          <li>Email: support@imersa.id</li>
          <li>WA (By Appointment)</li>
          <li>Alamat: Nganjuk, Jawa Timur</li>
        </ul>
      </div>
    </div>
    <div class="border-t border-white/10">
      <div class="max-w-7xl mx-auto px-6 py-6 text-sm text-center text-blue-200">
        &copy; <?= date('Y'); ?> Vendor Partnership & SEO Performance. Seluruh hak cipta dilindungi.
      </div>
    </div>
  </footer>

  <!-- Back-to-top button -->
  <button id="toTop" title="Kembali ke atas"
          class="fixed bottom-6 right-6 hidden md:inline-flex items-center justify-center h-11 w-11 rounded-full bg-blue-600 text-white shadow-lg hover:bg-blue-700 transition">
    ↑
  </button>

  <!-- Scripts: Scroll reveal + utilities -->
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
                translateY: [18, 0],
                easing: 'easeOutCubic',
                duration: 700,
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
        if (window.scrollY > 600) {
          toTop.style.display = 'inline-flex';
        } else {
          toTop.style.display = 'none';
        }
      };
      window.addEventListener('scroll', toggleToTop);
      toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
      toggleToTop();

      // Active nav link on scroll (simple)
      const sections = ['features','how','testimoni','faq'];
      const links = sections.map(id => [id, document.querySelector(a[href="#${id}"])]);
      const spy = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          const link = links.find(([id]) => id === entry.target.id)?.[1];
          if (link) link.classList.toggle('text-blue-600', entry.isIntersecting);
        });
      }, { threshold: 0.6 });
      sections.forEach(id => {
        const el = document.getElementById(id);
        if (el) spy.observe(el);
      });
    });
  </script>
</body>
</html>