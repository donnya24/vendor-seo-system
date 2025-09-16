<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<style>
  :root { --sidebar-offset: 0px; }
  @media (min-width: 768px){
    #pageWrap{ margin-left: var(--sidebar-offset); transition: margin-left .3s ease; }
  }
  #pageWrap .page-inner{
    max-width: 100% !important;
    width: 100% !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  #pageWrap .table-wrap{ width:100%; overflow-x:auto; }
  #pageWrap table{ width:100%; }
  .animate-slide-up{ animation:slideUp .45s ease-out forwards; opacity:0; transform:translateY(14px); }
  @keyframes slideUp{ to{ opacity:1; transform:translateY(0);} }
</style>
<script>
  (function(){
    function applyOffset(){
      try{
        const L = window.Alpine?.store('layout');
        const open = !!(L && L.sidebarOpen && L.isDesktop);
        document.documentElement.style.setProperty('--sidebar-offset', open ? '15rem' : '0px');
      }catch(e){}
    }
    document.addEventListener('alpine:init', applyOffset);
    document.addEventListener('DOMContentLoaded', applyOffset);
    window.addEventListener('resize', applyOffset);
    window.addEventListener('orientationchange', applyOffset);
    const t=setInterval(applyOffset,150); window.addEventListener('beforeunload',()=>clearInterval(t));
  })();
</script>

<?php
// Data tabel
$rows = [
  [
    'id'       => 4,
    'layanan'  => 'Custom Butik',
    'masuk'    => 13,
    'diproses' => 6,
    'ditolak'  => 1,
    'closing'  => 1,
    'tanggal'  => '2025-08-28',
    'update'   => '2025-08-29 00:26',
  ],
  [
    'id'       => 5,
    'layanan'  => 'Digital Printing',
    'masuk'    => 9,
    'diproses' => 3,
    'ditolak'  => 2,
    'closing'  => 1,
    'tanggal'  => '2025-08-29',
    'update'   => '2025-08-29 08:40',
  ],
];
?>

<div id="pageWrap" class="flex-1 flex flex-col overflow-hidden">
  <!-- Header halaman lebih kecil -->
  <header class="bg-white shadow-md z-20 sticky top-0">
    <div class="page-inner px-4 sm:px-6 py-3 flex items-center justify-between">
      <div>
        <h1 class="text-lg font-bold text-gray-800">Leads Management</h1>
        <p class="text-xs text-gray-500 mt-1">Read-only access to all lead information</p>
      </div>
      <div class="flex gap-3">
        <a href="<?= site_url('admin/leads/export/csv'); ?>" class="px-3 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-xs font-medium flex items-center transition-colors">
          <i class="fa fa-file-csv mr-2"></i> Export CSV
        </a>
        <button class="px-3 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 text-xs font-medium flex items-center transition-colors">
          <i class="fa fa-filter mr-2"></i> Filter
        </button>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1 overflow-y-auto bg-gray-50">
    <div class="page-inner px-4 sm:px-6 py-6">

      <!-- Tabel -->
      <div class="bg-white rounded-2xl shadow-sm overflow-hidden animate-slide-up">
        <div class="table-wrap">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">ID LEADS</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">LAYANAN</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">MASUK</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">DIPROSES</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">DITOLAK</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">CLOSING</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">TANGGAL</th>
                <th class="px-6 py-3 text-left text-xs font-semibold text-white uppercase tracking-wider">UPDATE</th>
                <th class="px-6 py-3 text-left sm:text-right text-xs font-semibold text-white uppercase tracking-wider">AKSI</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
              <?php foreach($rows as $r): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 text-sm text-gray-800 font-semibold"><?= esc($r['id']) ?></td>
                  <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= esc($r['layanan']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?= esc($r['masuk']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?= esc($r['diproses']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?= esc($r['ditolak']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?= esc($r['closing']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?= esc($r['tanggal']) ?></td>
                  <td class="px-6 py-4 text-sm text-gray-800"><?= esc($r['update']) ?></td>
                  <td class="px-6 py-4 text-sm sm:text-right">
                    <a href="<?= site_url('admin/leads/'.$r['id']); ?>"
                       class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs transition-colors">
                      <i class="fa fa-eye"></i><span>Detail</span>
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
