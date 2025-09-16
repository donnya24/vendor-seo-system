<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<!-- Guard ringan agar tidak terpengaruh halaman error/tema lain -->
<style>
  #pageWrap, #pageMain { color:#111827; }
  #pageWrap a:not([class*="text-"]){ color:inherit!important; }

  /* ====== Motion: Fade-Up ====== */
  @media (prefers-reduced-motion:no-preference){
    .fade-up{
      opacity:0; transform:translate3d(0,18px,0);
      animation:fadeUp var(--dur,.55s) cubic-bezier(.22,.9,.24,1) forwards;
      animation-delay:var(--delay,0s);
      will-change:transform,opacity;
    }
    .fade-up-soft{
      opacity:0; transform:translate3d(0,12px,0);
      animation:fadeUp var(--dur,.45s) ease-out forwards;
      animation-delay:var(--delay,0s);
      will-change:transform,opacity;
    }
    @keyframes fadeUp{ to{ opacity:1; transform:none } }
  }
</style>

<script>
  // Pastikan Alpine store untuk modal Announcement tersedia (aman saat Alpine belum loaded)
  document.addEventListener('alpine:init', () => {
    if (!window.Alpine) return;
    if (!Alpine.store('ui')) Alpine.store('ui', {});
    if (typeof Alpine.store('ui').showAnnouncementModal === 'undefined') {
      Alpine.store('ui').showAnnouncementModal = false;
    }
  });

  // Bersihkan tema error yang mungkin tertinggal
  (function(){
    function clean(){ document.documentElement.classList.remove('error','error-theme','with-sidebar-fallback'); }
    document.addEventListener('DOMContentLoaded', clean);
    document.addEventListener('turbo:load', clean);
    document.addEventListener('turbo:before-cache', clean);
  })();
</script>

<!-- ======================== CONTENT WRAPPER ======================== -->
<div
  id="pageWrap"
  class="flex-1 flex flex-col min-h-screen bg-gray-50 pb-16 md:pb-0 transition-[margin] duration-300 ease-in-out"
  :class="sidebarOpen && isDesktop ? 'md:ml-64' : 'md:ml-0'"
>
  <!-- ======================== MAIN CONTENT ======================== -->
  <main
    id="pageMain"
    class="flex-1 overflow-y-auto p-3 md:p-4 no-scrollbar fade-up"
    style="--dur:.60s; --delay:.02s"
  >
    <!-- STATS CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-6">
      <!-- Total Vendors -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-2.5 rounded-lg border border-blue-200 shadow-xs hover:shadow-sm transition-shadow fade-up"
           style="--delay:.08s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-blue-800 uppercase tracking-wider mb-0.5">TOTAL VENDORS</p>
            <p class="text-lg font-bold text-blue-900"><?= esc($stats['totalVendors'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-md text-white ml-2">
            <i class="fas fa-store text-xs"></i>
          </div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-blue-200/50">
          <div class="flex items-center text-blue-700 text-[10px] font-medium">
            <i class="fas fa-arrow-up mr-0.5"></i>
            <span class="font-semibold">2 dari bulan lalu</span>
          </div>
        </div>
      </div>

      <!-- Monthly Deals (total closing bulan ini) -->
      <div class="bg-gradient-to-br from-green-50 to-green-100 p-2.5 rounded-lg border border-green-200 shadow-xs hover:shadow-sm transition-shadow fade-up"
           style="--delay:.14s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-green-800 uppercase tracking-wider mb-0.5">MONTHLY DEALS</p>
            <p class="text-lg font-bold text-green-900"><?= esc($stats['monthlyDeals'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-green-600 rounded-md text-white ml-2">
            <i class="fas fa-handshake text-xs"></i>
          </div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-green-200/50">
          <div class="flex items-center text-green-700 text-[10px] font-medium">
            <i class="fas fa-chart-pie mr-0.5"></i>
            <span class="font-semibold">40% dari target</span>
          </div>
        </div>
      </div>

      <!-- Top Keywords -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-2.5 rounded-lg border border-purple-200 shadow-xs hover:shadow-sm transition-shadow fade-up"
           style="--delay:.20s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-purple-800 uppercase tracking-wider mb-0.5">TOP KEYWORDS</p>
            <p class="text-lg font-bold text-purple-900"><?= esc($stats['topKeywords'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-purple-600 rounded-md text-white ml-2">
            <i class="fas fa-key text-xs"></i>
          </div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-purple-200/50">
          <div class="flex items-center text-purple-700 text-[10px] font-medium">
            <i class="fas fa-arrow-up mr-0.5"></i>
            <span class="font-semibold">5 baru bulan ini</span>
          </div>
        </div>
      </div>
    </div>

    <!-- ROW 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
      <!-- Active Projects -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden fade-up" style="--delay:.26s">
        <div class="px-3 py-2 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-sm font-semibold text-gray-800">Active Projects</h3>
          <a href="<?= site_url('admin/projects'); ?>" class="text-xs text-blue-600 hover:text-blue-800 font-medium visited:text-blue-600">
            <span class="font-semibold">Create new</span>
          </a>
        </div>
        <div class="p-3">
          <div class="space-y-2">
            <div class="flex items-start justify-between p-2 bg-gray-50 rounded-md fade-up-soft" style="--delay:.30s">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Label Baja Stratilaya</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Surabaya, Malang</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-08-01</p>
              </div>
              <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-[11px] font-semibold rounded-full">Active</span>
            </div>

            <div class="flex items-start justify-between p-2 hover:bg-gray-50 rounded-md fade-up-soft" style="--delay:.34s">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Cetak Yasin Bangkalan</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Bangkalan</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-08-05</p>
              </div>
              <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-[11px] font-semibold rounded-full">Active</span>
            </div>

            <div class="flex items-start justify-between p-2 hover:bg-gray-50 rounded-md fade-up-soft" style="--delay:.38s">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Kursus Bahasa Inggris</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Semarang</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-07-28</p>
              </div>
              <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 text-[11px] font-semibold rounded-full">Planning</span>
            </div>

            <div class="flex items-start justify-between p-2 hover:bg-gray-50 rounded-md fade-up-soft" style="--delay:.42s">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Seven Villa Kalkuang</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Yogyakarta</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-08-10</p>
              </div>
              <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 text-[11px] font-semibold rounded-full">Planning</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="lg:col-span-1 bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden fade-up" style="--delay:.32s">
        <div class="px-3 py-2 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3>
        </div>
        <div class="p-3">
          <div class="space-y-2">
            <!-- Add New Tim SEO -->
            <button onclick="openSeoTeamModal()" class="flex items-center p-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition text-xs w-full fade-up-soft" style="--delay:.36s">
              <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white mr-2">
                <i class="fas fa-users text-sm"></i>
              </div>
              <div class="text-left">
                <span class="font-semibold block">Add New Tim SEO</span>
                <span class="text-[11px] text-blue-600">Tambah anggota tim SEO</span>
              </div>
            </button>

            <!-- Post Announcement -> OPEN MODAL -->
            <button type="button"
                    class="flex items-center p-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition text-xs w-full fade-up-soft"
                    style="--delay:.40s"
                    @click="$store.ui.showAnnouncementModal = true; document.documentElement.classList.add('overflow-hidden'); document.body.classList.add('overflow-hidden');">
              <div class="flex items-center justify-center h-8 w-8 rounded-full bg-green-600 text-white mr-2">
                <i class="fas fa-bullhorn text-sm"></i>
              </div>
              <div class="text-left">
                <span class="font-semibold block">Post Announcement</span>
                <span class="text-[11px] text-green-600">Broadcast messages</span>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- LEADS TERBARU -->
    <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.44s">
      <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <h3 class="text-sm font-semibold text-gray-800 flex items-center">
          <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
          Leads Terbaru
        </h3>
        <a href="<?= site_url('admin/leads'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold inline-flex items-center gap-1 visited:text-white">
          <i class="fas fa-eye text-[10px]"></i> Lihat Semua
        </a>
      </div>

      <div class="p-0">
        <div class="overflow-x-auto">
          <table class="w-full table-auto divide-y divide-gray-100">
            <thead class="bg-blue-600">
              <tr>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">ID</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">MASUK</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">CLOSING</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">TANGGAL</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">UPDATE</th>
                <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">AKSI</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <?php
                // Gunakan data dari controller jika ada; fallback ke placeholder agar UI tetap rapi
                $rows = $recentLeads ?? [
                  [
                    'id_leads'   => '4',
                    'masuk'      => 13,
                    'closing'    => 1,
                    'tanggal'    => '2025-08-28',
                    'updated_at' => '2025-08-29 00:26',
                    'detail_url' => site_url('admin/leads/4')
                  ],
                  [
                    'id_leads'   => '5',
                    'masuk'      => 9,
                    'closing'    => 1,
                    'tanggal'    => '2025-08-29',
                    'updated_at' => '2025-08-29 08:40',
                    'detail_url' => site_url('admin/leads/5')
                  ],
                ];
                $__idx = 0;
              ?>
              <?php foreach($rows as $lead): $__idx++; $delay = number_format(0.48 + 0.04*($__idx), 2, '.', ''); ?>
                <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= $delay ?>s;">
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <div class="text-sm font-bold text-gray-900"><?= esc($lead['id_leads'] ?? '-') ?></div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <div class="text-sm text-gray-900"><?= esc($lead['masuk'] ?? 0) ?></div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <div class="text-sm text-gray-900"><?= esc($lead['closing'] ?? 0) ?></div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <div class="text-sm text-gray-900"><?= esc($lead['tanggal'] ?? '-') ?></div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <div class="text-sm text-gray-900"><?= esc($lead['updated_at'] ?? '-') ?></div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <a href="<?= esc($lead['detail_url'] ?? '#') ?>" class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm visited:text-white">
                      <i class="fa-regular fa-eye text-[11px]"></i> Detail
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if(empty($rows)): ?>
                <tr class="fade-up-soft" style="--delay:.52s"><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">Belum ada data leads.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <!-- Waktu real-time (independen) -->
    <script>
      (function(){
        const fmtDate = new Intl.DateTimeFormat('id-ID',{day:'2-digit',month:'short',year:'numeric'});
        const fmtTime = new Intl.DateTimeFormat('id-ID',{hour:'2-digit',minute:'2-digit'});
        function render(){
          document.querySelectorAll('.js-date').forEach(el=>{const d=new Date(el.dataset.ts);el.textContent=isNaN(d)?'—':fmtDate.format(d);});
          document.querySelectorAll('.js-time').forEach(el=>{const d=new Date(el.dataset.ts);el.textContent=isNaN(d)?'—':fmtTime.format(d);});
        }
        render();
        setInterval(render, 30000);
      })();
    </script>
  </main>
</div>

<!-- ======================== MODAL: Tambah Announcement ======================== -->
<style>
  /* Scoped ke modal announcement saja */
  .modal-overlay-ann{ position:fixed; inset:0; z-index:60; display:flex; align-items:center; justify-content:center; }
  .backdrop-ann{ position:absolute; inset:0; background:rgba(0,0,0,.55); }
  .modal-shell{ position:relative; z-index:1; width:100%; max-width:720px; margin:0 12px; background:#fff; border-radius:14px; box-shadow:0 20px 45px rgba(0,0,0,.28); display:flex; flex-direction:column; max-height:calc(100vh - 64px); overflow:hidden; }
  .modal-header-ann{ background:#1e40af; color:#fff; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
  .modal-title-ann{ font-weight:700; font-size:20px; }
  .modal-close-ann{ color:#ffffffcc; padding:4px 6px; border-radius:8px; }
  .modal-close-ann:hover{ color:#fff; background:rgba(255,255,255,.15); }
  .modal-body-ann{ padding:16px; overflow:auto; flex:1 1 auto; min-height:0; scrollbar-gutter:stable both-edges; }
  .modal-footer-ann{ padding:14px 16px; background:#f8fafc; border-top:1px solid #eef2f7; display:flex; justify-content:flex-end; gap:10px; }
  .scroll-thin{ scrollbar-width:thin; scrollbar-color:rgba(148,163,184,.9) transparent; }
  .scroll-thin::-webkit-scrollbar{ width:6px; height:6px; }
  .scroll-thin::-webkit-scrollbar-track{ background:transparent; }
  .scroll-thin::-webkit-scrollbar-thumb{ background:rgba(148,163,184,.9); border-radius:9999px; }
  .scroll-thin::-webkit-scrollbar-thumb:hover{ background:rgba(100,116,139,1); }
  @media (max-width:640px){ .modal-shell{ max-width:96vw; margin:0 8px; border-radius:12px; } }
</style>

<script>
  // Kunci buka/tutup scroll halaman saat modal aktif
  function lockPageScroll(lock){
    const cls='overflow-hidden';
    document.documentElement.classList.toggle(cls, !!lock);
    document.body.classList.toggle(cls, !!lock);
  }
  // Reset saat navigasi
  (function(){
    function reset(){
      try{ if (window.Alpine?.store('ui')) Alpine.store('ui').showAnnouncementModal = false; }catch(e){}
      lockPageScroll(false);
    }
    document.addEventListener('DOMContentLoaded', reset);
    document.addEventListener('turbo:load', reset);
    document.addEventListener('turbo:before-cache', reset);
  })();
</script>

<div class="modal-overlay-ann"
     x-cloak
     x-show="$store.ui.showAnnouncementModal"
     x-transition.opacity
     aria-modal="true" role="dialog"
     @keydown.escape.window="$store.ui.showAnnouncementModal=false; lockPageScroll(false);"
     x-init="$watch(() => $store.ui.showAnnouncementModal, v => lockPageScroll(v))"
>
  <div class="backdrop-ann" @click="$store.ui.showAnnouncementModal=false; lockPageScroll(false);"></div>

  <div class="modal-shell" @click.outside="$store.ui.showAnnouncementModal=false; lockPageScroll(false);">
    <div class="modal-header-ann">
      <div class="modal-title-ann">Tambah Announcement Baru</div>
      <button type="button" class="modal-close-ann"
              @click="$store.ui.showAnnouncementModal=false; lockPageScroll(false);"
              aria-label="Tutup">
        <i class="fa-solid fa-xmark text-xl"></i>
      </button>
    </div>

    <div class="modal-body-ann scroll-thin">
      <form id="announcementForm" method="post" action="<?= site_url('admin/announcements/store'); ?>">
        <?= csrf_field() ?>

        <label class="block text-sm font-semibold text-gray-700 mb-1">Judul Announcement <span class="text-red-500">*</span></label>
        <div class="relative mb-4">
          <span class="absolute left-3 top-3 text-gray-400"><i class="fa-solid fa-heading"></i></span>
          <input type="text" name="title" required
                 class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                 placeholder="Masukkan judul announcement">
        </div>

        <label class="block text-sm font-semibold text-gray-700 mb-1">Konten <span class="text-red-500">*</span></label>
        <div class="relative mb-4">
          <span class="absolute left-3 top-3 text-gray-400"><i class="fa-solid fa-align-left"></i></span>
          <textarea name="content" required rows="6"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Masukkan konten announcement"></textarea>
        </div>

        <label class="block text-sm font-semibold text-gray-700 mb-1">Target Audience <span class="text-red-500">*</span></label>
        <div class="relative mb-4">
          <span class="absolute left-3 top-3 text-gray-400"><i class="fa-solid fa-users"></i></span>
          <select name="audience" required
                  class="w-full pl-10 pr-8 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none">
            <option value="all" selected>Semua Pengguna</option>
            <option value="vendor">Tim SEO</option>
            <option value="user">Vendor</option>
          </select>
          <span class="absolute right-3 top-3 text-gray-400 pointer-events-none">
            <i class="fa-solid fa-chevron-down"></i>
          </span>
        </div>

        <label class="inline-flex items-center gap-2 mb-2">
          <input type="checkbox" name="is_active" value="1" checked
                 class="w-4 h-4 text-blue-600 border-gray-300 rounded">
          <span class="text-sm text-gray-700">Aktifkan announcement</span>
        </label>
      </form>
    </div>

    <div class="modal-footer-ann">
      <button type="button"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700"
              @click="$store.ui.showAnnouncementModal=false; lockPageScroll(false);">
        Batal
      </button>
      <button type="submit" form="announcementForm"
              class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold">
        Simpan Announcement
      </button>
    </div>
  </div>
</div>

<!-- ======================== MODAL FORM TIM SEO ======================== -->
<style>
  .seo-modal-overlay{ position:fixed; inset:0; z-index:60; display:none; align-items:center; justify-content:center; }
  .seo-modal-overlay.active{ display:flex; }
  .seo-modal-backdrop{ position:absolute; inset:0; background:rgba(0,0,0,.55); }
  .seo-modal-shell{
    position:relative; z-index:1; width:100%; max-width:600px; margin:0 12px;
    background:#fff; border-radius:14px; box-shadow:0 24px 60px rgba(0,0,0,.28);
    display:flex; flex-direction:column; max-height:520px; overflow:hidden;
  }
  .seo-modal-header{ background:#1e40af; color:#fff; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
  .seo-modal-title{ font-weight:800; font-size:18px; }
  .seo-modal-x{ display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:9999px; color:#dbeafe; }
  .seo-modal-x:hover{ background:rgba(255,255,255,.15); color:#fff; }
  .seo-modal-body{ padding:16px; overflow:auto; flex:1 1 auto; }
  .seo-modal-footer{ padding:12px 16px; background:#f8fafc; border-top:1px solid #eef2f7; display:flex; align-items:center; justify-content:space-between; }
  .seo-input:focus{ outline:0; box-shadow:0 0 0 2px rgba(37,99,235,.4); border-color:#3b82f6; }
</style>

<div id="seoTeamModal" class="seo-modal-overlay" onclick="if(event.target===this) closeSeoTeamModal()">
  <div class="seo-modal-backdrop"></div>

  <div class="seo-modal-shell" role="dialog" aria-modal="true" aria-labelledby="seoModalTitle">
    <!-- Header -->
    <div class="seo-modal-header">
      <h3 id="seoModalTitle" class="seo-modal-title">Tambah Tim SEO Baru</h3>
      <button class="seo-modal-x" type="button" aria-label="Tutup" onclick="closeSeoTeamModal()">
        <i class="fa-solid fa-xmark text-lg"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="seo-modal-body">
      <form id="seoTeamForm" class="space-y-3" autocomplete="off">
        <!-- Nama -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Nama Anggota <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-user"></i></span>
            <input type="text" name="nama" class="seo-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg transition" placeholder="Masukkan nama lengkap" required>
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-envelope"></i></span>
            <input type="email" name="email" class="seo-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg transition" placeholder="email@contoh.com" required>
          </div>
        </div>

        <!-- Telepon -->
        <div>
          <label class="block text-sm font-medium text-gray-700">No Telepon <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-phone"></i></span>
            <input type="tel" name="telepon" class="seo-input w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg transition" placeholder="0812-3456-7890" required>
          </div>
        </div>

        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700">Password <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-lock"></i></span>
            <input type="password" name="password" id="password" class="seo-input w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg transition" placeholder="Minimal 8 karakter" minlength="8" required>
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
              <i class="fas fa-eye" id="passwordIcon"></i>
            </button>
          </div>
          <p class="text-xs text-gray-500 mt-1">Gunakan kombinasi huruf, angka, dan simbol</p>
        </div>
      </form>
    </div>

    <!-- Footer -->
    <div class="seo-modal-footer">
      <button type="button" class="px-3 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700 text-sm" onclick="closeSeoTeamModal()">Batal</button>
      <button type="button" class="btn-success px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold" onclick="document.getElementById('seoTeamForm').requestSubmit()">Simpan Anggota</button>
    </div>
  </div>
</div>

<script>
function togglePassword(){
  const i=document.getElementById('password'); const ic=document.getElementById('passwordIcon');
  if(i.type==='password'){ i.type='text'; ic.classList.replace('fa-eye','fa-eye-slash'); }
  else { i.type='password'; ic.classList.replace('fa-eye-slash','fa-eye'); }
}
function openSeoTeamModal(){
  document.getElementById('seoTeamModal').classList.add('active');
  document.body.style.overflow='hidden';
}
function closeSeoTeamModal(){
  const m=document.getElementById('seoTeamModal');
  m.classList.remove('active');
  document.body.style.overflow='';
  const f=document.getElementById('seoTeamForm'); if(f){ f.reset(); }
}
document.getElementById('seoTeamForm').addEventListener('submit',function(e){
  e.preventDefault();
  const btn=document.querySelector('#seoTeamModal .btn-success'); const t=btn.innerHTML;
  btn.innerHTML='<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...'; btn.disabled=true;
  setTimeout(()=>{ alert('Data tim SEO berhasil disimpan!'); btn.innerHTML=t; btn.disabled=false; closeSeoTeamModal(); },1200);
});
document.querySelectorAll('#seoTeamForm input').forEach(el=>{
  el.addEventListener('blur',function(){
    if(!this.value && this.hasAttribute('required')) this.classList.add('border-red-500');
    else this.classList.remove('border-red-500');
  });
});
document.addEventListener('keydown',e=>{ if(e.key==='Escape') closeSeoTeamModal(); });
</script>

<?= $this->include('admin/layouts/footer'); ?>
