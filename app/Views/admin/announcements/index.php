<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<!-- Guard ringan + inisialisasi store layout bila belum ada -->
<script>
  document.addEventListener('alpine:init', () => {
    // Jika store 'layout' belum ada (fallback antar halaman), buat dengan default aman
    if (!Alpine.store('layout')) {
      Alpine.store('layout', {
        sidebarOpen: false,
        isDesktop: window.matchMedia('(min-width: 768px)').matches
      });
    } else {
      // Pastikan properti minimum tersedia
      if (typeof Alpine.store('layout').isDesktop === 'undefined') {
        Alpine.store('layout').isDesktop = window.matchMedia('(min-width: 768px)').matches;
      }
      if (typeof Alpine.store('layout').sidebarOpen === 'undefined') {
        Alpine.store('layout').sidebarOpen = false;
      }
    }
  });

  // Sinkronkan isDesktop saat resize
  (function () {
    const mq = window.matchMedia('(min-width: 768px)');
    function apply() {
      if (window.Alpine && Alpine.store('layout')) {
        Alpine.store('layout').isDesktop = mq.matches;
      }
    }
    mq.addEventListener ? mq.addEventListener('change', apply) : mq.addListener(apply);
    window.addEventListener('turbo:load', apply);
    window.addEventListener('DOMContentLoaded', apply);
  })();
</script>

<!-- Wrapper halaman: otomatis menyesuaikan margin-left saat sidebar open/close -->
<div
  id="pageWrap"
  class="flex-1 flex flex-col min-h-screen bg-transparent transition-[margin] duration-300 ease-in-out"
  :class="Alpine.store('layout').sidebarOpen && Alpine.store('layout').isDesktop ? 'md:ml-64' : 'md:ml-0'"
  x-data
>
  <!-- Header Section -->
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="px-6 py-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold text-gray-800">Announcements Management</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your announcements and notifications</p>
      </div>
      <button onclick="openAnnouncementModal()" class="px-4 py-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium flex items-center transition-colors duration-200">
        <i class="fa fa-plus-circle mr-2"></i> New Announcement
      </button>
    </div>
  </header>

  <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
    <!-- Flash Message -->
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-6 p-4 rounded-xl bg-green-100 text-green-700 text-sm flex items-center animate-fade-in">
        <i class="fa fa-check-circle mr-2"></i>
        <?= esc(session()->getFlashdata('success')) ?>
      </div>
    <?php endif; ?>

    <!-- Announcements Table -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden animate-slide-up">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Judul</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Audience</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase tracking-wider">Aksi</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach(($items ?? []) as $a): ?>
              <tr class="hover:bg-gray-50 transition-colors duration-150">
                <td class="px-6 py-4">
                  <div class="text-sm font-medium text-gray-900"><?= esc($a['title'] ?? '-') ?></div>
                </td>
                <td class="px-6 py-4">
                  <?php
                    $audienceLabels = ['all'=>'Semua Pengguna','vendors'=>'Vendor','admins'=>'Administrator'];
                    $audience = $a['audience'] ?? 'all';
                    $audienceText = $audienceLabels[$audience] ?? ucfirst($audience);
                  ?>
                  <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                    <?= esc($audienceText) ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <?php if (!empty($a['is_active'])): ?>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                      <i class="fa fa-check-circle mr-1"></i> Active
                    </span>
                  <?php else: ?>
                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                      <i class="fa fa-times-circle mr-1"></i> Inactive
                    </span>
                  <?php endif; ?>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right">
                  <a href="<?= site_url('admin/announcements/'.$a['id'].'/edit'); ?>"
                     class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 transition-colors duration-200 mr-2">
                    <i class="fa fa-edit mr-1"></i> Edit
                  </a>
                  <form action="<?= site_url('admin/announcements/'.$a['id'].'/delete'); ?>" method="post" class="inline" onsubmit="return confirm('Hapus pengumuman?')">
                    <?= csrf_field() ?>
                    <button class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 transition-colors duration-200">
                      <i class="fa fa-trash mr-1"></i> Delete
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($items)): ?>
              <tr>
                <td colspan="4" class="px-6 py-12 text-center">
                  <div class="flex flex-col items-center justify-center text-gray-400">
                    <i class="fa fa-bullhorn text-4xl mb-3"></i>
                    <p class="text-lg font-medium">Tidak ada data announcements</p>
                    <p class="text-sm mt-1">Buat announcement baru untuk memulai</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- ========================= MODAL: New Announcement ========================= -->
<div id="announcementModal" class="modal-overlay" onclick="if(event.target === this) closeAnnouncementModal()">
  <div class="profile-modal">
    <div class="modal-header">
      <h3 class="modal-title">Tambah Announcement Baru</h3>
      <button class="modal-close" onclick="closeAnnouncementModal()">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>

    <!-- HANYA BODY YANG SCROLL -->
    <div class="modal-body">
      <form id="announcementForm" action="<?= site_url('admin/announcements/store'); ?>" method="post" class="space-y-4">
        <?= csrf_field() ?>

        <!-- Judul -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Judul Announcement <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-heading"></i></span>
            <input type="text" name="title"
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                   placeholder="Masukkan judul announcement" required>
          </div>
        </div>

        <!-- Konten -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Konten <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-align-left"></i></span>
            <textarea name="content" rows="6"
                      class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                      placeholder="Masukkan konten announcement" required></textarea>
          </div>
        </div>

        <!-- Audience -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Target Audience <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400"><i class="fas fa-users"></i></span>
            <select name="audience"
                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition appearance-none"
                    required>
              <option value="all">Semua Pengguna</option>
              <option value="vendors">Tim SEO</option>
              <option value="admins">Vendor</option>
            </select>
            <span class="absolute right-3 top-3 text-gray-400 pointer-events-none"><i class="fas fa-chevron-down"></i></span>
          </div>
        </div>

        <!-- Status -->
        <div class="flex items-center pt-2">
          <label class="flex items-center">
            <input type="checkbox" name="is_active"
                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                   checked>
            <span class="ml-2 text-sm text-gray-700">Aktifkan announcement</span>
          </label>
        </div>
      </form>
    </div>

    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeAnnouncementModal()">Batal</button>
      <button class="btn-success" onclick="document.getElementById('announcementForm').submit()">Simpan Announcement</button>
    </div>
  </div>
</div>

<!-- ========================= Styles & Animations ========================= -->
<style>
  .animate-slide-up{ animation:slideUp .5s ease-out forwards; opacity:0; transform:translateY(20px); }
  @keyframes slideUp{ to{ opacity:1; transform:translateY(0); } }
  .animate-fade-in{ animation:fadeIn .5s ease-out forwards; }
  @keyframes fadeIn{ from{opacity:0} to{opacity:1} }

  .modal-overlay{
    position:fixed; inset:0; background:rgba(0,0,0,.55);
    display:flex; align-items:center; justify-content:center;
    z-index:70; opacity:0; visibility:hidden; transition:all .3s ease;
  }
  .modal-overlay.active{ opacity:1; visibility:visible; }

  #announcementModal .profile-modal{
    background:#fff; border-radius:14px;
    width:100%; max-width:720px;
    max-height:calc(100vh - 64px);
    margin:16px; box-shadow:0 20px 45px rgba(0,0,0,.28);
    display:flex; flex-direction:column; overflow:hidden;
    transform:scale(.96); transition:transform .25s ease;
  }
  .modal-overlay.active .profile-modal{ transform:scale(1); }

  .modal-header{
    background:linear-gradient(90deg,#1e40af,#1d4ed8);
    color:#fff; padding:14px 18px;
    border-top-left-radius:14px; border-top-right-radius:14px;
    display:flex; align-items:center; justify-content:space-between;
  }
  .modal-title{ font-size:1.25rem; font-weight:700; }
  .modal-close{ background:none; border:none; color:white; cursor:pointer; font-size:1.25rem; display:flex; align-items:center; justify-content:center; }
  .modal-footer{
    padding:14px 16px; border-top:1px solid #e5e7eb;
    display:flex; justify-content:flex-end; gap:.75rem;
  }

  #announcementModal .modal-body{
    padding:16px; flex:1 1 auto; min-height:0;
    overflow-y:auto; scrollbar-gutter:stable both-edges;
  }

  #announcementModal .modal-body::-webkit-scrollbar{ width:6px; }
  #announcementModal .modal-body::-webkit-scrollbar-track{ background:transparent; }
  #announcementModal .modal-body::-webkit-scrollbar-thumb{ background:rgba(148,163,184,.85); border-radius:9999px; }
  #announcementModal .modal-body::-webkit-scrollbar-thumb:hover{ background:rgba(100,116,139,1); }
  #announcementModal .modal-body{ scrollbar-width:thin; scrollbar-color:rgba(148,163,184,.85) transparent; }

  .btn-secondary{ padding:.5rem 1rem; border:1px solid #d1d5db; border-radius:.5rem; color:#374151; background:#fff; font-weight:500; transition:.2s; }
  .btn-secondary:hover{ background:#f9fafb; }
  .btn-success{ padding:.5rem 1rem; border:none; border-radius:.5rem; color:#fff; background:#16a34a; font-weight:500; transition:background-color .2s; }
  .btn-success:hover{ background:#15803d; }

  @media (max-width:640px){
    #announcementModal .profile-modal{
      max-width:95vw;
      max-height:calc(100vh - 48px);
      margin:8px; border-radius:12px;
    }
  }
</style>

<!-- ========================= JS: Open / Close ========================= -->
<script>
  function openAnnouncementModal(){
    const modal = document.getElementById('announcementModal');
    modal.classList.add('active');
    document.documentElement.classList.add('overflow-hidden');
    document.body.classList.add('overflow-hidden');
  }
  function closeAnnouncementModal(){
    const modal = document.getElementById('announcementModal');
    modal.classList.remove('active');
    document.documentElement.classList.remove('overflow-hidden');
    document.body.classList.remove('overflow-hidden');
    document.getElementById('announcementForm').reset();
  }

  // Tutup dengan ESC
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAnnouncementModal(); });

  // Validasi ringan
  document.querySelectorAll('#announcementForm input, #announcementForm select, #announcementForm textarea').forEach(el=>{
    el.addEventListener('blur',function(){
      if (!this.value && this.hasAttribute('required')) this.classList.add('border-red-500');
      else this.classList.remove('border-red-500');
    });
  });
</script>

<?= $this->include('admin/layouts/footer'); ?>
