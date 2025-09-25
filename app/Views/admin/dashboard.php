<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<!-- Guard ringan agar tidak terpengaruh halaman error/tema lain -->
<style>
  #pageWrap, #pageMain { color:#111827; }
  #pageWrap a:not([class*="text-"]){ color:inherit!important; }

  /* ====== Motion: Fade-Up ====== */
  @media (prefers-reduced-motion:no-preference){
    .fade-up{ opacity:0; transform:translate3d(0,18px,0); animation:fadeUp var(--dur,.55s) cubic-bezier(.22,.9,.24,1) forwards; animation-delay:var(--delay,.0s); will-change:transform,opacity; }
    .fade-up-soft{ opacity:0; transform:translate3d(0,12px,0); animation:fadeUp var(--dur,.45s) ease-out forwards; animation-delay:var(--delay,.0s); will-change:transform,opacity; }
    @keyframes fadeUp{ to{ opacity:1; transform:none } }
  }

  /* ====== Cards (konsisten) ====== */
  .card{ background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 1px 2px rgba(0,0,0,.05); }
  .card-head{ padding:12px 14px; border-bottom:1px solid #eef2f7; display:flex; align-items:center; justify-content:space-between; }
  .card-title{ font-size:14px; font-weight:600; color:#0f172a; display:flex; align-items:center; gap:8px; }
  .btn-ghost{ font-size:12px; padding:7px 12px; border-radius:10px; border:1px solid #cfe3ff; color:#0b61d6; background:#eef6ff; }
  .btn-ghost:hover{ background:#e3f0ff; }

  /* ====== Table look & feel seragam ====== */
  .table-modern{ width:100%; font-size:12px; border-collapse:separate; border-spacing:0; }
  .table-modern thead th{
    background:#1D4ED8; /* blue background */
    color:#fff; 
    padding:10px 14px; 
    text-align:left;
    font-size:12px; 
    font-weight:600; 
    letter-spacing:.04em; 
    text-transform:uppercase;
  }
  .table-modern thead th:first-child{ border-top-left-radius:10px; }
  .table-modern thead th:last-child{ border-top-right-radius:10px; }
  .table-modern tbody td{ padding:12px; border-top:1px solid #f1f5f9; vertical-align:top; }
  .table-modern tbody tr:hover{ background:#f8fafc; }

  /* ====== Badges & Buttons konsisten tema ====== */
  .badge{ display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:9999px; font-size:12px; font-weight:600; border:1px solid transparent; }
  .badge-blue{ color:#1e40af; background:#eff6ff; border-color:#bfdbfe; }
  .badge-green{ color:#166534; background:#ecfdf5; border-color:#86efac; }
  .badge-yellow{ color:#854d0e; background:#fffbeb; border-color:#fde68a; }
  .btn-pill{ padding:8px 12px; border-radius:9999px; font-size:12px; font-weight:600; border:1px solid; transition:.15s; }
  .btn-pill.green{ color:#14532d; background:#ecfdf5; border-color:#86efac; }
  .btn-pill.green:hover{ background:#dcfce7; }
  .btn-pill.red{ color:#7f1d1d; background:#fef2f2; border-color:#fecaca; }
  .btn-pill.red:hover{ background:#fee2e2; }
  .btn-pill.muted{ color:#475569; background:#f1f5f9; border-color:#cbd5e1; cursor:not-allowed; }

  /* Kontak */
  .contact{ display:flex; flex-direction:column; gap:6px; }
  .contact .line{ display:flex; align-items:center; gap:8px; color:#334155; }
  .contact .line i{ width:16px; text-align:center; color:#94a3b8; }
  .contact .wa a{ color:#047857; }
</style>

<script>
  // Bersihkan tema error yang mungkin tertinggi
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
    <!-- 6 STATS CARDS (sesuai kebutuhan) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mb-6">
      <!-- 1. Total vendor -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-2.5 rounded-lg border border-blue-200 shadow-xs hover:shadow-sm transition-shadow fade-up" style="--delay:.08s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-blue-800 uppercase tracking-wider mb-0.5">TOTAL VENDOR</p>
            <p class="text-lg font-bold text-blue-900"><?= esc($stats['totalVendors'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-blue-600 rounded-md text-white ml-2"><i class="fas fa-store text-xs"></i></div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-blue-200/50"><div class="flex items-center text-blue-700 text-[10px] font-medium"><i class="fas fa-arrow-up mr-0.5"></i><span class="font-semibold">Terdaftar aktif</span></div></div>
      </div>

      <!-- 2. Total komisi bulan ini (yang status ws paid) -->
      <div class="bg-gradient-to-br from-green-50 to-green-100 p-2.5 rounded-lg border border-green-200 shadow-xs hover:shadow-sm transition-shadow fade-up" style="--delay:.14s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-green-800 uppercase tracking-wider mb-0.5">TOTAL KOMISI</p>
            <p class="text-lg font-bold text-green-900">Rp <?= number_format($stats['monthlyCommissionPaid'] ?? 0, 0, ',', '.'); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-green-600 rounded-md text-white ml-2"><i class="fas fa-money-bill-wave text-xs"></i></div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-green-200/50"><div class="flex items-center text-green-700 text-[10px] font-medium"><i class="fas fa-check-circle mr-0.5"></i><span class="font-semibold">Status paid</span></div></div>
      </div>

      <!-- 3. Top keyword -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-2.5 rounded-lg border border-purple-200 shadow-xs hover:shadow-sm transition-shadow fade-up" style="--delay:.20s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-purple-800 uppercase tracking-wider mb-0.5">TOP KEYWORD</p>
            <p class="text-lg font-bold text-purple-900"><?= esc($stats['topKeyword'] ?? '-'); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-purple-600 rounded-md text-white ml-2"><i class="fas fa-key text-xs"></i></div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-purple-200/50"><div class="flex items-center text-purple-700 text-[10px] font-medium"><i class="fas fa-fire mr-0.5"></i><span class="font-semibold">Paling dicari</span></div></div>
      </div>

      <!-- 4. Total leads masuk realtime (hari ini) -->
      <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-2.5 rounded-lg border border-orange-200 shadow-xs hover:shadow-sm transition-shadow fade-up" style="--delay:.26s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-orange-800 uppercase tracking-wider mb-0.5">TOTAL LEADS HARI INI</p>
            <p class="text-lg font-bold text-orange-900"><?= esc($stats['leadsToday'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-orange-600 rounded-md text-white ml-2"><i class="fas fa-clock text-xs"></i></div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-orange-200/50"><div class="flex items-center text-orange-700 text-[10px] font-medium"><i class="fas fa-calendar-day mr-0.5"></i><span class="font-semibold">Realtime</span></div></div>
      </div>

      <!-- 5. Total lead masuk (keseluruhan) -->
      <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-2.5 rounded-lg border border-indigo-200 shadow-xs hover:shadow-sm transition-shadow fade-up" style="--delay:.32s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-indigo-800 uppercase tracking-wider mb-0.5">TOTAL LEADS MASUK</p>
            <p class="text-lg font-bold text-indigo-900"><?= esc($stats['totalLeadsIn'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-indigo-600 rounded-md text-white ml-2"><i class="fas fa-inbox text-xs"></i></div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-indigo-200/50"><div class="flex items-center text-indigo-700 text-[10px] font-medium"><i class="fas fa-users mr-0.5"></i><span class="font-semibold">Customer chat</span></div></div>
      </div>

      <!-- 6. Total leads closing -->
      <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 p-2.5 rounded-lg border border-emerald-200 shadow-xs hover:shadow-sm transition-shadow fade-up" style="--delay:.38s">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <p class="text-[10px] font-semibold text-emerald-800 uppercase tracking-wider mb-0.5">LEADS CLOSING</p>
            <p class="text-lg font-bold text-emerald-900"><?= esc($stats['totalLeadsClosing'] ?? 0); ?></p>
          </div>
          <div class="flex items-center justify-center w-8 h-8 bg-emerald-600 rounded-md text-white ml-2"><i class="fas fa-handshake text-xs"></i></div>
        </div>
        <div class="mt-1.5 pt-1.5 border-t border-emerald-200/50"><div class="flex items-center text-emerald-700 text-[10px] font-medium"><i class="fas fa-check-double mr-0.5"></i><span class="font-semibold">Deal selesai</span></div></div>
      </div>
    </div>

    <!-- ROW 2 -->
    <div class="flex flex-col lg:flex-row gap-3 mb-4">
      <!-- ======= Daftar Vendor (pengajuan terbaru) - 70% ======= -->
      <div class="lg:w-[70%] bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.44s">
        <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
          <h3 class="text-sm font-semibold text-gray-800 flex items-center"><i class="fa-solid fa-building mr-2 text-blue-600 text-xs"></i>Daftar Pengajuan Vendor</h3>
        </div>

        <div class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full table-auto divide-y divide-gray-100">
              <thead class="bg-blue-600">
                <tr>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">NO</th>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">VENDOR</th>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">PEMILIK</th>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">KONTAK</th>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">KOMISI DIMINTA</th>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">STATUS</th>
                  <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">AKSI</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <?php
                  $__reqs = $commissionRequests ?? [];
                  $__reqs = array_slice($__reqs, 0, 3);
                  $no = 0;
                  foreach ($__reqs as $r):
                    $no++;
                    $delay = number_format(0.48 + 0.04*$no, 2, '.', '');
                    $id       = (int)($r['id'] ?? 0);
                    $usaha    = $r['usaha'] ?? ($r['business_name'] ?? '-');
                    $pemilik  = $r['pemilik'] ?? ($r['owner_name'] ?? '-');

                    // normalisasi kontak dari controller (sudah di-COALESCE), tapi tetap jaga-jaga
                    $phone    = trim((string)($r['phone'] ?? ''));
                    $waRaw    = trim((string)($r['wa'] ?? ($r['whatsapp_number'] ?? '')));

                    $komisi   = is_numeric($r['komisi'] ?? null) ? (float)$r['komisi'] : (float)preg_replace('/[^0-9.]/','', (string)($r['komisi'] ?? 0));
                    $status   = strtolower((string)($r['status'] ?? 'pending'));
                    $approved = ($status === 'verified');
                ?>
                <tr id="req-row-<?= $id ?>" class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= $delay ?>s;">
                  <td class="px-4 py-4 whitespace-nowrap align-top"><div class="text-sm font-bold text-gray-900"><?= $no ?></div></td>
                  <td class="px-4 py-4 whitespace-nowrap align-top"><div class="text-sm font-semibold text-gray-900"><?= esc($usaha) ?></div></td>
                  <td class="px-4 py-4 whitespace-nowrap align-top"><div class="text-sm text-gray-900"><?= esc($pemilik) ?></div></td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <div class="flex flex-col gap-1">
                      <div class="flex items-center text-sm text-gray-900"><i class="fa-solid fa-phone text-xs text-gray-400 mr-1.5 w-3"></i><span><?= $phone !== '' ? esc($phone) : '—' ?></span></div>
                      <div class="flex items-center">
                        <i class="fa-brands fa-whatsapp text-xs text-gray-400 mr-1.5 w-3"></i>
                        <?php if ($waRaw !== ''): ?>
                          <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $waRaw) ?>" target="_blank" rel="noopener" class="text-sm text-green-600 hover:text-green-700"><?= esc($waRaw) ?></a>
                        <?php else: ?>
                          <span class="text-sm text-gray-900">—</span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      <?= number_format($komisi,1) ?>%
                    </span>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <?php if ($approved): ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Disetujui</span>
                    <?php else: ?>
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Belum Disetujui</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-4 py-4 whitespace-nowrap align-top">
                    <?php if ($approved): ?>
                      <span class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-500 text-xs font-semibold px-3 py-1.5 rounded-xl cursor-not-allowed">Sudah Disetujui</span>
                    <?php else: ?>
                      <div class="flex flex-wrap gap-2">
                        <button class="inline-flex items-center gap-1.5 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm"
                          onclick="approveVendorRequest(event, <?= $id ?>)">Setujui</button>
                        <button class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm"
                          onclick="rejectVendorRequest(event, <?= $id ?>)">Tolak</button>
                      </div>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($__reqs)): ?>
                  <tr class="fade-up-soft" style="--delay:.52s">
                    <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                      Belum ada pengajuan vendor saat ini.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Quick Actions - 30% -->
      <div class="lg:w-[30%] bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden fade-up" style="--delay:.50s">
        <div class="px-3 py-2 border-b border-gray-100"><h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3></div>
        <div class="p-3">
          <div class="space-y-2">
            <a href="<?= site_url('admin/users/create?role=seo_team'); ?>" class="flex items-center p-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition text-xs w-full fade-up-soft" style="--delay:.54s">
              <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white mr-2"><i class="fas fa-users text-sm"></i></div>
              <div class="text-left"><span class="font-semibold block">Add New Tim SEO</span><span class="text-[11px] text-blue-600">Tambah anggota tim SEO</span></div>
            </a>
            <a href="<?= site_url('admin/announcements'); ?>" class="flex items-center p-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition text-xs w-full fade-up-soft" style="--delay:.58s">
              <div class="flex items-center justify-center h-8 w-8 rounded-full bg-green-600 text-white mr-2"><i class="fas fa-bullhorn text-sm"></i></div>
              <div class="text-left"><span class="font-semibold block">Post Announcement</span><span class="text-[11px] text-green-600">Broadcast messages</span></div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- LEADS TERBARU (tetap) -->
    <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100 fade-up" style="--delay:.62s">
      <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <h3 class="text-sm font-semibold text-gray-800 flex items-center"><i class="fas fa-list mr-2 text-blue-600 text-xs"></i>Leads Terbaru</h3>
        <a href="<?= site_url('admin/leads'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold inline-flex items-center gap-1 visited:text-white"><i class="fas fa-eye text-[10px]"></i> Lihat Semua</a>
      </div>

      <div class="p-0">
        <div class="overflow-x-auto">
          <table class="w-full table-auto divide-y divide-gray-100">
            <thead class="bg-blue-600">
                <tr>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">NO</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">VENDOR</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">MASUK</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">CLOSING</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">TANGGAL</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">UPDATE</th>
                    <th class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">AKSI</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php
                $rows = $recentLeads ?? [];
                $no = 0;
                ?>
                <?php foreach ($rows as $lead): $no++; $delay = number_format(0.66 + 0.04 * $no, 2, '.', ''); ?>
                    <tr class="hover:bg-gray-50 fade-up-soft" style="--delay: <?= $delay ?>s;">
                        <td class="px-4 py-4 whitespace-nowrap align-top">
                            <div class="text-sm font-bold text-gray-900"><?= $no ?></div>
                        </td>
                        <!-- VENDOR / business_name -->
                        <td class="px-4 py-4 whitespace-nowrap align-top">
                            <div class="text-sm font-bold text-gray-900">
                                <?= esc($lead['business_name'] ?? '-') ?>
                            </div>
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

    <script>
      async function approveVendorRequest(e, id) {
        e.preventDefault();
        if (!confirm("Yakin ingin menyetujui vendor ini?")) return;

        const formData = new FormData();
        formData.append("id", id);

        const res = await fetch("<?= site_url('admin/vendorrequests/approve') ?>", {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" }
        });

        const data = await res.json();
        alert(data.message);

        if (data.status === "success") location.reload();
      }

      async function rejectVendorRequest(e, id) {
        e.preventDefault();
        const reason = prompt("Masukkan alasan penolakan:", "Pengajuan ditolak admin");
        if (!reason) return;

        const formData = new FormData();
        formData.append("id", id);
        formData.append("reason", reason);

        const res = await fetch("<?= site_url('admin/vendorrequests/reject') ?>", {
          method: "POST",
          body: formData,
          headers: { "X-Requested-With": "XMLHttpRequest" }
        });

        const data = await res.json();
        alert(data.message);

        if (data.status === "success") location.reload();
      }
      </script>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>