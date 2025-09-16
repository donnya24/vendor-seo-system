<main class="app-main flex-1 p-2 md:p-4 bg-gray-50">
  <!-- Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 md:gap-4 mb-4 md:mb-6 mt-4 md:mt-6">
    <div class="bg-white p-3 md:p-4 rounded-lg shadow-[0_2px_10px_rgba(59,130,246,0.3)] md:shadow-[0_4px_20px_rgba(59,130,246,0.3)] hover:shadow-[0_4px_16px_rgba(59,130,246,0.5)] md:hover:shadow-[0_6px_24px_rgba(59,130,246,0.5)] hover:scale-[1.02] transition-transform duration-200">
      <div class="flex items-center">
        <div class="p-2 md:p-3 rounded-full mr-3 md:mr-4 bg-blue-100 text-blue-600"><i class="fas fa-bullseye text-md md:text-lg"></i></div>
        <div><p class="text-xs md:text-sm font-medium text-gray-500">Total Leads Masuk</p><p class="text-xl md:text-2xl font-semibold"><?= $stats['leads_new'] ?? 0 ?></p></div>
      </div>
    </div>

    <div class="bg-white p-3 md:p-4 rounded-lg shadow-[0_2px_10px_rgba(34,197,94,0.3)] md:shadow-[0_4px_20px_rgba(34,197,94,0.3)] hover:shadow-[0_4px_16px_rgba(34,197,94,0.5)] md:hover:shadow-[0_6px_24px_rgba(34,197,94,0.5)] hover:scale-[1.02] transition-transform duration-200">
      <div class="flex items-center">
        <div class="p-2 md:p-3 rounded-full mr-3 md:mr-4 bg-green-100 text-green-600"><i class="fas fa-check-circle text-md md:text-lg"></i></div>
        <div><p class="text-xs md:text-sm font-medium text-gray-500">Total Leads Closing</p><p class="text-xl md:text-2xl font-semibold"><?= $stats['leads_closing'] ?? 0 ?></p></div>
      </div>
    </div>

    <div class="bg-white p-3 md:p-4 rounded-lg shadow-[0_2px_10px_rgba(99,102,241,0.3)] md:shadow-[0_4px_20px_rgba(99,102,241,0.3)] hover:shadow-[0_4px_16px_rgba(99,102,241,0.5)] md:hover:shadow-[0_6px_24px_rgba(99,102,241,0.5)] hover:scale-[1.02] transition-transform duration-200">
      <div class="flex items-center">
        <div class="p-2 md:p-3 rounded-full mr-3 md:mr-4 bg-indigo-100 text-indigo-600"><i class="fas fa-sign-in-alt text-md md:text-lg"></i></div>
        <div><p class="text-xs md:text-sm font-medium text-gray-500">Leads Masuk Hari Ini</p><p class="text-xl md:text-2xl font-semibold"><?= $stats['leads_today'] ?? 0 ?></p></div>
      </div>
    </div>

    <div class="bg-white p-3 md:p-4 rounded-lg shadow-[0_2px_10px_rgba(168,85,247,0.3)] md:shadow-[0_4px_20px_rgba(168,85,247,0.3)] hover:shadow-[0_4px_16px_rgba(168,85,247,0.5)] md:hover:shadow-[0_6px_24px_rgba(168,85,247,0.5)] hover:scale-[1.02] transition-transform duration-200">
      <div class="flex items-center">
        <div class="p-2 md:p-3 rounded-full mr-3 md:mr-4 bg-purple-100 text-purple-600"><i class="fas fa-check-double text-md md:text-lg"></i></div>
        <div><p class="text-xs md:text-sm font-medium text-gray-500">Leads Closing Hari Ini</p><p class="text-xl md:text-2xl font-semibold"><?= $stats['leads_closing_today'] ?? 0 ?></p></div>
      </div>
    </div>

    <div class="bg-white p-3 md:p-4 rounded-lg shadow-[0_2px_10px_rgba(234,179,8,0.3)] md:shadow-[0_4px_20px_rgba(234,179,8,0.3)] hover:shadow-[0_4px_16px_rgba(234,179,8,0.5)] md:hover:shadow-[0_6px_24px_rgba(234,179,8,0.5)] hover:scale-[1.02] transition-transform duration-200">
      <div class="flex items-center">
        <div class="p-2 md:p-3 rounded-full mr-3 md:mr-4 bg-yellow-100 text-yellow-600"><i class="fas fa-key text-md md:text-lg"></i></div>
        <div><p class="text-xs md:text-sm font-medium text-gray-500">Total Keyword</p><p class="text-xl md:text-2xl font-semibold"><?= $stats['keywords_total'] ?? 0 ?></p></div>
      </div>
    </div>
  </div>

<!-- QUICK ACTIONS + CONVERSION RATE & TOP KEYWORDS -->
<div class="flex flex-col lg:flex-row gap-4 md:gap-6 mb-4 md:mb-6 items-stretch">
  <!-- Quick Actions -->
  <div class="w-full lg:w-96 xl:w-[26rem] flex-none bg-white rounded-lg 
              shadow-[0_2px_10px_rgba(59,130,246,0.3)] md:shadow-[0_4px_20px_rgba(59,130,246,0.3)] 
              hover:shadow-[0_4px_16px_rgba(59,130,246,0.5)] md:hover:shadow-[0_6px_24px_rgba(59,130,246,0.5)] 
              overflow-hidden flex flex-col transition-transform duration-200 hover:scale-[1.01]">
    <div class="px-3 md:px-4 py-3 md:py-4 border-b border-gray-200">
      <h3 class="text-md md:text-lg font-medium text-gray-900">Quick Actions</h3>
    </div>
    <div class="p-3 md:p-4 space-y-2 md:space-y-3 flex-1 flex flex-col justify-start">
      <?php
        $canUpload = isset($canUpload) ? (bool)$canUpload : (($vp['status'] ?? '') === 'verified');
      ?>
      <?php if ($canUpload): ?>
        <a href="<?= site_url('vendoruser/services-products') ?>"
          class="w-full inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors">
          <i class="fas fa-boxes mr-2"></i> Kelola Layanan & Produk
        </a>
        <a href="<?= site_url('vendoruser/leads') ?>"
          class="w-full inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
          <i class="fas fa-bullseye mr-2"></i> Input Lead
        </a>
      <?php else: ?>
        <button type="button" @click="$store.ui.modal='profileEdit'"
                class="w-full inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium text-white bg-gray-400 cursor-not-allowed">
          <i class="fas fa-boxes mr-2"></i> Kelola Layanan & Produk (butuh verifikasi)
        </button>
        <button type="button" @click="$store.ui.modal='profileEdit'"
                class="w-full inline-flex items-center justify-center px-3 md:px-4 py-2 rounded-md text-xs md:text-sm font-medium text-white bg-gray-400 cursor-not-allowed">
          <i class="fas fa-bullseye mr-2"></i> Input Lead (butuh verifikasi)
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Container Conversion Rate & Top Keywords -->
  <div class="flex-1 flex flex-col lg:flex-row gap-4 md:gap-6 min-w-0">
    <!-- Conversion Rate -->
    <div class="w-full lg:flex-1 bg-white rounded-lg 
                shadow-[0_2px_10px_rgba(34,197,94,0.3)] md:shadow-[0_4px_20px_rgba(34,197,94,0.3)] 
                hover:shadow-[0_4px_16px_rgba(34,197,94,0.5)] md:hover:shadow-[0_6px_24px_rgba(34,197,94,0.5)] 
                overflow-hidden flex flex-col transition-transform duration-200 hover:scale-[1.01]">
      <div class="px-3 md:px-4 py-2 md:py-3 border-b border-gray-200">
        <h3 class="text-md md:text-lg font-medium text-gray-900">
          <i class="fas fa-percentage mr-2 text-green-600"></i>Conversion Rate
        </h3>
      </div>
      <div class="p-4 md:p-6 flex flex-col items-center justify-center flex-1">
        <?php
        $totalLeads = $stats['leads_new'] ?? 0;
        $totalClosing = $stats['leads_closing'] ?? 0;
        $conversionRate = ($totalLeads > 0) ? round(($totalClosing / $totalLeads) * 100, 1) : 0;
        $rateColor = 'text-green-600';
        if ($conversionRate < 20) { $rateColor = 'text-red-600'; }
        elseif ($conversionRate < 40) { $rateColor = 'text-black-600'; }
        ?>
        <div class="text-3xl md:text-4xl font-bold <?= $rateColor ?> mb-2"><?= $conversionRate ?>%</div>
        <div class="text-xs md:text-sm text-gray-600 text-center"><?= $totalClosing ?> closing dari <?= $totalLeads ?> leads</div>
        <div class="w-full bg-gray-200 rounded-full h-2 mt-3 md:mt-4">
          <div class="bg-green-600 h-2 rounded-full" style="width: <?= min($conversionRate, 100) ?>%"></div>
        </div>
      </div>
    </div>

    <!-- Top Keywords -->
    <div class="w-full lg:flex-1 bg-white rounded-lg 
                shadow-[0_2px_10px_rgba(59,130,246,0.3)] md:shadow-[0_4px_20px_rgba(59,130,246,0.3)] 
                hover:shadow-[0_4px_16px_rgba(59,130,246,0.5)] md:hover:shadow-[0_6px_24px_rgba(59,130,246,0.5)] 
                overflow-hidden flex flex-col transition-transform duration-200 hover:scale-[1.01]">
      <div class="px-3 md:px-4 py-3 md:py-4 border-b border-gray-200">
        <h3 class="text-md md:text-lg font-medium text-gray-900">
          <i class="fas fa-chart-line mr-2 text-blue-600"></i>Top Keywords
        </h3>
      </div>
      <div class="divide-y divide-gray-200 max-h-60 md:max-h-80 overflow-y-auto">
        <template x-if="$store.app.topKeywords.length === 0">
          <div class="p-4 text-center text-xs md:text-sm text-gray-500">Belum ada keyword.</div>
        </template>
        <template x-for="k in $store.app.topKeywords" :key="k.id">
          <div class="px-3 md:px-4 py-2 md:py-3 hover:bg-gray-50">
            <div class="flex items-start justify-between">
              <div class="flex-1 min-w-0">
                <p class="text-xs md:text-sm font-medium text-gray-900 truncate" x-text="k.text || 'Unknown'"></p>
                <p class="text-xs text-gray-500 truncate" x-text="k.project || 'Unknown project'"></p>
              </div>
              <div class="ml-2 flex-shrink-0 flex flex-col items-end">
                <span class="inline-flex items-center justify-center h-6 w-6 md:h-7 md:w-7 rounded-full text-xs font-semibold"
                      :class="{
                        'bg-green-100 text-green-700': (k.position || 999) <= 5,
                        'bg-yellow-100 text-yellow-700': (k.position || 999) > 5 && (k.position || 999) <= 10,
                        'bg-gray-100 text-gray-700': (k.position || 999) > 10
                      }"
                      x-text="k.position || '-'"></span>
                <div class="text-xs mt-1" :class="(k.change || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                  <template x-if="k.change !== null && k.change !== undefined">
                    <span><i class="fas" :class="(k.change || 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'"></i> <span x-text="Math.abs(k.change || 0)"></span></span>
                  </template>
                  <template x-if="k.change === null || k.change === undefined"><span>-</span></template>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</div>

  <!-- Leads Table -->
  <div class="bg-white rounded-lg md:rounded-xl shadow overflow-hidden mt-4 md:mt-6">
    <div class="px-4 md:px-6 py-3 md:py-4 border-b border-gray-200 flex items-center justify-between">
      <h3 class="text-md md:text-lg font-semibold text-gray-800">
        <i class="fas fa-bullseye text-blue-600 mr-2"></i>
        Leads Terbaru
      </h3>
      <a href="<?= site_url('vendoruser/leads') ?>" class="text-xs md:text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1">
        <i class="fas fa-eye"></i> Lihat Semua
      </a>
    </div>

    <?php
      $rows  = array_slice($recentLeads ?? [], 0, 10);
      $start = 1;
    ?>

    <div class="overflow-x-auto">
      <table class="table-auto w-full border-collapse text-xs md:text-sm text-gray-700">
        <thead class="bg-blue-600 text-white">
          <tr>
            <th class="px-2 md:px-3 py-1 md:py-2 uppercase font-medium border text-center w-12 md:w-14">No</th>
            <th class="px-2 md:px-4 py-1 md:py-2 uppercase font-medium border text-center">Tanggal</th>
            <th class="px-2 md:px-4 py-1 md:py-2 uppercase font-medium border text-center">Leads Masuk</th>
            <th class="px-2 md:px-4 py-1 md:py-2 uppercase font-medium border text-center">Leads Closing</th>
            <th class="px-2 md:px-4 py-1 md:py-2 uppercase font-medium border text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="5" class="px-3 md:px-4 py-3 md:py-4 text-center text-gray-500">Belum ada lead.</td>
            </tr>
          <?php else: foreach ($rows as $idx => $lead): ?>
            <tr class="hover:bg-gray-50 transition">
              <td class="px-2 md:px-3 py-1 md:py-2 border text-center font-medium"><?= $start + $idx ?></td>
              <td class="px-2 md:px-4 py-1 md:py-2 border text-center"><?= esc($lead['tanggal']) ?></td>
              <td class="px-2 md:px-4 py-1 md:py-2 border text-center"><?= esc($lead['masuk']) ?></td>
              <td class="px-2 md:px-4 py-1 md:py-2 border text-center"><?= esc($lead['closing']) ?></td>
              <td class="px-2 md:px-4 py-1 md:py-2 border text-center">
                <button class="text-blue-600 font-medium hover:text-blue-800 text-xs md:text-sm"
                        onclick="showLeadDetail(<?= (int)$lead['id'] ?>)">Detail</button>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Detail Lead (khusus dashboard) -->
  <div id="leadDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden p-2 md:p-4">
    <div class="bg-white rounded-lg md:rounded-lg shadow-lg w-full max-w-2xl mx-auto max-h-[90vh] overflow-y-auto">
      <div class="p-4 md:p-6">
        <div class="flex items-center justify-between mb-3 md:mb-4">
          <h3 class="text-lg md:text-xl font-semibold">Detail Laporan Leads</h3>
          <button type="button" onclick="closeLeadDetailModal()" class="text-gray-500 hover:text-gray-700 text-lg">
            <i class="fas fa-times"></i>
          </button>
        </div>
        <div id="leadDetailContent" class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4 text-xs md:text-sm"></div>
        <div class="mt-4 md:mt-6 flex justify-end space-x-2">
          <button type="button" onclick="closeLeadDetailModal()" class="px-3 md:px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-xs md:text-sm">Tutup</button>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
// ==== Script khusus dashboard (biarkan di view ini) ====
function showLeadDetail(leadId) {
  document.getElementById('leadDetailContent').innerHTML = `
    <div class="col-span-2 flex justify-center items-center py-6 md:py-8">
      <div class="animate-spin rounded-full h-6 w-6 md:h-8 md:w-8 border-b-2 border-blue-600"></div>
    </div>`;
  document.getElementById('leadDetailModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden'; // Mencegah scroll latar belakang

  fetch(`<?= site_url('vendoruser/leads/') ?>${leadId}`)
    .then(r => { if(!r.ok) throw new Error('Network'); return r.text(); })
    .then(html => {
      const doc = new DOMParser().parseFromString(html, 'text/html');
      const leadContent = doc.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-4.text-sm');
      document.getElementById('leadDetailContent').innerHTML =
        leadContent ? leadContent.innerHTML :
        `<div class="col-span-2 text-center py-4 text-red-500">Gagal memuat detail lead.</div>`;
    })
    .catch(() => {
      document.getElementById('leadDetailContent').innerHTML =
        `<div class="col-span-2 text-center py-4 text-red-500">Terjadi kesalahan saat memuat data.</div>`;
    });
}

function closeLeadDetailModal(){ 
  document.getElementById('leadDetailModal').classList.add('hidden');
  document.body.style.overflow = 'auto'; // Mengembalikan scroll
}

document.getElementById('leadDetailModal').addEventListener('click', e => { 
  if (e.target === e.currentTarget) closeLeadDetailModal(); 
});

// Touch event untuk swipe close modal (opsional)
let touchStartY = 0;
let touchEndY = 0;

document.getElementById('leadDetailModal').addEventListener('touchstart', e => {
  touchStartY = e.changedTouches[0].screenY;
});

document.getElementById('leadDetailModal').addEventListener('touchend', e => {
  touchEndY = e.changedTouches[0].screenY;
  // Jika swipe down lebih dari 100px, tutup modal
  if (touchEndY - touchStartY > 100) {
    closeLeadDetailModal();
  }
});
</script>