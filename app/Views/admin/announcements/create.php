<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div id="pageWrap"
     class="flex-1 flex flex-col min-h-screen bg-gray-50 transition-[margin] duration-300 ease-in-out"
     :class="sidebarOpen && isDesktop ? 'md:ml-64' : 'md:ml-0'">
  <main id="pageMain" class="flex-1 p-0"></main>
</div>

<input type="hidden" name="is_active" value="0">
<label class="inline-flex items-center gap-2 mb-2">
  <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-blue-600 border-gray-300 rounded">
  <span class="text-sm text-gray-700">Aktifkan announcement</span>
</label>

<!-- ====== STYLES (diletakkan di bawah supaya menang) ====== -->
<style>
  /* Overlay & backdrop */
  #annCreateModal.overlay { position:fixed; inset:0; z-index:70; display:flex; align-items:center; justify-content:center; }
  #annCreateModal .backdrop { position:absolute; inset:0; background:rgba(0,0,0,.55); }

  /* Shell modal: pakai !important agar menang */
  #annCreateModal .shell {
    position:relative; z-index:1;
    width:100%; max-width:880px !important;            /* lebar paksa */
    margin:0 16px; background:#fff; border-radius:14px;
    box-shadow:0 20px 45px rgba(0,0,0,.28);
    display:flex; flex-direction:column;
    max-height:80vh !important; overflow:hidden !important; /* tinggi paksa */
  }
  #annCreateModal .header { background:linear-gradient(90deg,#1e40af,#1d4ed8); color:#fff; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
  #annCreateModal .title { font-weight:700; font-size:20px; }
  #annCreateModal .close { color:#ffffffcc; padding:4px 6px; border-radius:8px; }
  #annCreateModal .close:hover { color:#fff; background:rgba(255,255,255,.15); }

  /* Hanya isi yang scroll */
  #annCreateModal .body {
    padding:16px; overflow-y:auto !important; flex:1 1 auto; min-height:0;
    scrollbar-gutter:stable both-edges;
  }
  #annCreateModal .footer { padding:14px 16px; background:#f8fafc; border-top:1px solid #eef2f7; display:flex; justify-content:flex-end; gap:10px; }

  /* Scrollbar tipis (pakai !important supaya tidak ditimpa) */
  #annCreateModal .body::-webkit-scrollbar{ width:4px !important; }
  #annCreateModal .body::-webkit-scrollbar-track{ background:transparent !important; }
  #annCreateModal .body::-webkit-scrollbar-thumb{ background:rgba(148,163,184,.85) !important; border-radius:9999px !important; }
  #annCreateModal .body::-webkit-scrollbar-thumb:hover{ background:rgba(100,116,139,1) !important; }
  #annCreateModal .body{ scrollbar-width:thin; scrollbar-color:rgba(148,163,184,.85) transparent; }

  @media (max-width:640px){
    #annCreateModal .shell{ max-width:95vw !important; margin:0 8px; border-radius:12px; max-height:85vh !important; }
  }
</style>

<script>
  // Alpine store
  document.addEventListener('alpine:init', () => {
    if (!Alpine.store('ui')) Alpine.store('ui', {});
    if (typeof Alpine.store('ui').showAnnouncementModal === 'undefined') {
      Alpine.store('ui').showAnnouncementModal = false;
    }
  });

  // Kunci scroll halaman saat modal aktif
  function lockPageScroll(lock){
    const c='overflow-hidden';
    document.documentElement.classList.toggle(c, !!lock);
    document.body.classList.toggle(c, !!lock);
  }

  // Buka modal otomatis
  document.addEventListener('DOMContentLoaded', () => {
    try { Alpine.store('ui').showAnnouncementModal = true; } catch(e){}
    lockPageScroll(true);
  });

  // Tutup modal -> kembali ke index
  function closeCreateModal(){
    try { if (window.Alpine?.store('ui')) Alpine.store('ui').showAnnouncementModal = false; } catch(e){}
    lockPageScroll(false);
    window.location.href = "<?= site_url('admin/announcements'); ?>";
  }
</script>

<!-- ====== MODAL CREATE ====== -->
<div id="annCreateModal"
     class="overlay"
     x-cloak
     x-show="$store.ui.showAnnouncementModal"
     x-transition.opacity
     aria-modal="true" role="dialog"
     @keydown.escape.window="closeCreateModal()"
     x-init="$watch(() => $store.ui.showAnnouncementModal, v => lockPageScroll(v))">

  <div class="backdrop" @click="closeCreateModal()"></div>

  <!-- inline style cadangan (belt & suspenders) -->
  <div class="shell"
       style="max-width:880px; max-height:80vh; overflow:hidden;"
       @click.outside="closeCreateModal()">

    <div class="header">
      <div class="title">Tambah Announcement Baru</div>
      <button type="button" class="close" aria-label="Tutup" @click="closeCreateModal()">
        <i class="fa-solid fa-xmark text-xl"></i>
      </button>
    </div>

    <!-- body: hanya bagian ini yang scroll -->
    <div class="body" style="overflow-y:auto;">
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
          <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
          <select name="audience" required
                  class="w-full pl-10 pr-8 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 appearance-none">
            <option value="all" selected>Semua Pengguna</option>
            <option value="vendor">Vendor</option>
            <option value="seoteam">Tim SEO</option>
          </select>
          <span class="absolute right-3 top-3 text-gray-400 pointer-events-none"><i class="fa-solid fa-chevron-down"></i></span>
        </div>

        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Publish <span class="text-red-500">*</span></label>
        <div class="relative mb-4">
          <span class="absolute left-3 top-3 text-gray-400"><i class="fa-solid fa-calendar"></i></span>
          <input type="datetime-local" name="publish_at"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                value="<?= date('Y-m-d\TH:i'); ?>" required>
        </div>

        <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Berakhir (optional)</label>
        <div class="relative mb-4">
          <span class="absolute left-3 top-3 text-gray-400"><i class="fa-solid fa-hourglass-end"></i></span>
          <input type="datetime-local" name="expires_at"
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
      </form>
    </div>

    <div class="footer">
      <button type="button"
              class="px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-100 text-gray-700"
              @click="closeCreateModal()">Batal</button>
      <button type="submit" form="announcementForm"
              class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-semibold">
        Simpan Announcement
      </button>
    </div>
  </div>
</div>

<?= $this->include('admin/layouts/footer'); ?>
