<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<!-- CONTENT WRAPPER -->
<div
  id="pageWrap"
  class="flex-1 flex flex-col min-h-screen bg-gray-50 pb-16 md:pb-0
         transition-[margin] duration-300 ease-in-out"
  :class="sidebarOpen && isDesktop ? 'md:ml-64' : 'md:ml-0'"
>
  <!-- MAIN CONTENT -->
  <main
    id="pageMain"
    class="flex-1 overflow-y-auto p-3 md:p-4 no-scrollbar transition-opacity duration-300 opacity-0"
  >
    <!-- STATS CARDS - BOLD TEXT -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-6">
      <!-- Total Vendors Card -->
      <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-2.5 rounded-lg border border-blue-200 shadow-xs hover:shadow-sm transition-shadow">
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

      <!-- Monthly Deals Card -->
      <div class="bg-gradient-to-br from-green-50 to-green-100 p-2.5 rounded-lg border border-green-200 shadow-xs hover:shadow-sm transition-shadow">
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

      <!-- Top Keywords Card -->
      <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-2.5 rounded-lg border border-purple-200 shadow-xs hover:shadow-sm transition-shadow">
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

    <!-- SECOND ROW -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
      <!-- Active Projects Card -->
      <div class="lg:col-span-2 bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden">
        <div class="px-3 py-2 border-b border-gray-100 flex justify-between items-center">
          <h3 class="text-sm font-semibold text-gray-800">Active Projects</h3>
          <a href="<?= site_url('admin/projects'); ?>" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
            <span class="font-semibold">Create new</span>
          </a>
        </div>
        <div class="p-3">
          <div class="space-y-2">
            <div class="flex items-start justify-between p-2 bg-gray-50 rounded-md">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Label Baja Stratilaya</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Surabaya, Malang</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-08-01</p>
              </div>
              <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-[11px] font-semibold rounded-full">Active</span>
            </div>
            
            <div class="flex items-start justify-between p-2 hover:bg-gray-50 rounded-md">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Cetak Yasin Bangkalan</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Bangkalan</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-08-05</p>
              </div>
              <span class="px-1.5 py-0.5 bg-green-100 text-green-800 text-[11px] font-semibold rounded-full">Active</span>
            </div>
            
            <div class="flex items-start justify-between p-2 hover:bg-gray-50 rounded-md">
              <div class="flex-1">
                <h4 class="font-bold text-gray-800 text-xs mb-0.5">Kursus Bahasa Inggris</h4>
                <p class="text-xs text-gray-500 mb-0.5"><span class="font-medium">Location:</span> Semarang</p>
                <p class="text-[11px] text-gray-400"><span class="font-medium">Created:</span> 2025-07-28</p>
              </div>
              <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 text-[11px] font-semibold rounded-full">Planning</span>
            </div>
            
            <div class="flex items-start justify-between p-2 hover:bg-gray-50 rounded-md">
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

      <!-- Quick Actions Card (Simplified) -->
      <div class="lg:col-span-1 bg-white rounded-lg shadow-xs border border-gray-100 overflow-hidden">
        <div class="px-3 py-2 border-b border-gray-100">
          <h3 class="text-sm font-semibold text-gray-800">Quick Actions</h3>
        </div>
        <div class="p-3">
          <div class="space-y-2">
            <!-- Add New Tim SEO -->
            <button onclick="openSeoTeamModal()" class="flex items-center p-2 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition text-xs w-full">
              <div class="flex items-center justify-center h-8 w-8 rounded-full bg-blue-600 text-white mr-2">
                <i class="fas fa-users text-sm"></i>
              </div>
              <div class="text-left">
                <span class="font-semibold block">Add New Tim SEO</span>
                <span class="text-[11px] text-blue-600">Tambah anggota tim SEO</span>
              </div>
            </button>
            
            <!-- Post Announcement -->
            <a href="<?= site_url('admin/announcements/create'); ?>" class="flex items-center p-2 bg-green-50 text-green-700 rounded-md hover:bg-green-100 transition text-xs">
              <div class="flex items-center justify-center h-8 w-8 rounded-full bg-green-600 text-white mr-2">
                <i class="fas fa-bullhorn text-sm"></i>
              </div>
              <div class="text-left">
                <span class="font-semibold block">Post Announcement</span>
                <span class="text-[11px] text-green-600">Broadcast messages</span>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- RECENT LEADS (updated with gradient header) -->
    <!-- RECENT LEADS – versi fleksibel -->
        <section class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-100">
          <!-- Title bar -->
          <div class="px-4 py-3 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <h3 class="text-sm font-semibold text-gray-800 flex items-center">
              <i class="fas fa-list mr-2 text-blue-600 text-xs"></i>
              Recent Leads
            </h3>
            <a href="<?= site_url('admin/leads'); ?>"
              class="bg-blue-600 hover:bg-blue-700 text-white px-2.5 py-1 rounded-lg text-xs font-semibold inline-flex items-center gap-1">
              <i class="fas fa-eye text-[10px]"></i> View All
            </a>
          </div>

          <div class="p-0">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-100">
                <!-- HEADER gradasi -->
                <thead class="bg-gradient-to-r from-blue-600 to-indigo-700">
                  <tr>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Tanggal</th>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Pelanggan</th>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Vendor</th>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Service</th>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Source</th>
                    <th scope="col" class="px-4 py-3 text-left text-[11px] font-semibold text-white uppercase tracking-wider">Aksi</th>
                  </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-100">
                  <?php
                    // ====== DATA ======
                    // Kirim dari controller sebagai $recentLeads.
                    // Fallback demo agar layout tetap tampil bila belum ada data.
                    $rows = $recentLeads ?? [
                      [
                        'id_lead'   => 'L-202508-001',
                        'id_vendor' => '1',
                        'service'   => '1',
                        'nama'      => 'Galih Natsir M.TI.',
                        'no_telp'   => '0721 5579 101',
                        'status'    => 'new',
                        'source'    => 'vendor_manual',
                        'chat_at'   => date('c'), // ISO
                      ],
                      [
                        'id_lead'   => 'L-202508-002',
                        'id_vendor' => '1',
                        'service'   => '1',
                        'nama'      => 'Gabriella Puspita',
                        'no_telp'   => '0856 450 477',
                        'status'    => 'in_progress',
                        'source'    => 'wa_inbox',
                        'chat_at'   => date('c', strtotime('-2 hours')),
                      ],
                      [
                        'id_lead'   => 'L-202508-003',
                        'id_vendor' => '1',
                        'service'   => '1',
                        'nama'      => 'Devi Laksita M.Pd',
                        'no_telp'   => '(+62) 829 377 690',
                        'status'    => 'close',
                        'source'    => 'wa_outbox',
                        'chat_at'   => date('c', strtotime('-1 day -15 minutes')),
                      ],
                    ];

                    // Helper status -> label + warna chip
                    function statusChip($s) {
                      $s = strtolower(trim($s));
                      if ($s === 'in progress') $s = 'in_progress';
                      $map = [
                        'new'         => ['Baru',      'bg-blue-100 text-blue-700'],
                        'in_progress' => ['Proses',    'bg-amber-100 text-amber-700'],
                        'close'       => ['Tertutup',  'bg-emerald-100 text-emerald-700'],
                        'closed'      => ['Tertutup',  'bg-emerald-100 text-emerald-700'],
                      ];
                      return $map[$s] ?? [ucwords($s), 'bg-gray-100 text-gray-700'];
                    }
                  ?>

                  <?php foreach ($rows as $lead): ?>
                    <?php
                      [$statusLabel, $statusClass] = statusChip($lead['status'] ?? 'new');
                      $ts = $lead['chat_at'] ?? time();
                      $tsIso = is_numeric($ts) ? date('c', (int)$ts) : (string)$ts;
                      $lihatUrl = isset($lead['id_lead']) ? site_url('admin/leads/'.$lead['id_lead']) : '#';
                    ?>
                    <tr class="hover:bg-gray-50">
                      <!-- TANGGAL (2 baris: tanggal tebal, waktu kecil) -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <div class="text-sm font-bold text-gray-900">
                          <time class="js-date" data-ts="<?= esc($tsIso) ?>">—</time>
                        </div>
                        <div class="text-xs text-gray-500 leading-tight">
                          <time class="js-time" data-ts="<?= esc($tsIso) ?>">—</time>
                        </div>
                      </td>

                      <!-- PELANGGAN (nama tebal, telp kecil) -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <div class="text-sm font-semibold text-gray-900"><?= esc($lead['nama'] ?? '-') ?></div>
                        <div class="text-xs text-gray-500"><?= esc($lead['no_telp'] ?? ($lead['no_telp_cust'] ?? '-')) ?></div>
                      </td>

                      <!-- VENDOR -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <div class="text-sm text-gray-900"><?= esc($lead['id_vendor'] ?? '-') ?></div>
                      </td>

                      <!-- SERVICE -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <div class="text-sm text-gray-900"><?= esc($lead['service'] ?? '-') ?></div>
                      </td>

                      <!-- STATUS (chip) -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <span class="px-2 py-1 rounded-full text-[12px] font-semibold inline-flex <?= $statusClass ?>">
                          <?= esc($statusLabel) ?>
                        </span>
                      </td>

                      <!-- SOURCE -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <div class="text-sm text-gray-800"><?= esc($lead['source'] ?? '-') ?></div>
                      </td>

                      <!-- AKSI -->
                      <td class="px-4 py-4 whitespace-nowrap align-top">
                        <a href="<?= $lihatUrl ?>"
                          class="inline-flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-3 py-1.5 rounded-xl shadow-sm">
                          <i class="fa-regular fa-eye text-[11px]"></i> Lihat
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>

                  <?php if (empty($rows)): ?>
                    <tr>
                      <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                        Belum ada data leads.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- Waktu real-time (tanggal & jam) -->
        <script>
          (function () {
            const fmtDate = new Intl.DateTimeFormat('id-ID', { day:'2-digit', month:'short', year:'numeric' });
            const fmtTime = new Intl.DateTimeFormat('id-ID', { hour:'2-digit', minute:'2-digit' });

            function render() {
              document.querySelectorAll('.js-date').forEach(el => {
                const ts = el.getAttribute('data-ts');
                const d = new Date(ts);
                el.textContent = isNaN(d) ? '—' : fmtDate.format(d);
              });
              document.querySelectorAll('.js-time').forEach(el => {
                const ts = el.getAttribute('data-ts');
                const d = new Date(ts);
                el.textContent = isNaN(d) ? '—' : fmtTime.format(d);
              });
            }
            render();
            // Jika ingin waktu bergerak per menit, aktifkan interval di bawah:
            setInterval(render, 1000 * 30); // update tiap 30 detik (hemat)
          })();
        </script>
  </main>
</div>

<!-- Modal Form Tim SEO -->
<div id="seoTeamModal" class="modal-overlay" onclick="if(event.target === this) closeSeoTeamModal()">
  <div class="profile-modal">
    <div class="modal-header">
      <h3 class="modal-title">Tambah Tim SEO Baru</h3>
      <button class="modal-close" onclick="closeSeoTeamModal()">
        <i class="fa-solid fa-times"></i>
      </button>
    </div>
    
    <div class="p-6 space-y-4">
      <form id="seoTeamForm" class="space-y-4">
        <!-- Nama Anggota -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Nama Anggota <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400">
              <i class="fas fa-user"></i>
            </span>
            <input 
              type="text" 
              name="nama" 
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
              placeholder="Masukkan nama lengkap"
              required
            >
          </div>
        </div>
        
        <!-- Email -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Email <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400">
              <i class="fas fa-envelope"></i>
            </span>
            <input 
              type="email" 
              name="email" 
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
              placeholder="email@contoh.com"
              required
            >
          </div>
        </div>
        
        <!-- No Telepon -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            No Telepon <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400">
              <i class="fas fa-phone"></i>
            </span>
            <input 
              type="tel" 
              name="telepon" 
              class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
              placeholder="0812-3456-7890"
              required
            >
          </div>
        </div>
        
        <!-- Password -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">
            Password <span class="text-red-500">*</span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-3 text-gray-400">
              <i class="fas fa-lock"></i>
            </span>
            <input 
              type="password" 
              name="password" 
              id="password"
              class="w-full pl-10 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" 
              placeholder="Minimal 8 karakter"
              required
              minlength="8"
            >
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-gray-400 hover:text-gray-600">
              <i class="fas fa-eye" id="passwordIcon"></i>
            </button>
          </div>
          <p class="text-xs text-gray-500">Gunakan kombinasi huruf, angka, dan simbol</p>
        </div>
      </form>
    </div>
    
    <div class="modal-footer">
      <button class="btn-secondary" onclick="closeSeoTeamModal()">
        Batal
      </button>
      <button class="btn-success" onclick="document.getElementById('seoTeamForm').submit()">
        Simpan Anggota
      </button>
    </div>
  </div>
</div>

<script>
// ===== Real-time render tanggal & waktu chat pada tabel leads =====
(function () {
  const fmt = new Intl.DateTimeFormat('id-ID', {
    year: 'numeric', month: '2-digit', day: '2-digit',
    hour: '2-digit', minute: '2-digit', second: '2-digit'
  });

  function renderTimes() {
    document.querySelectorAll('.js-time').forEach(el => {
      const ts = el.getAttribute('data-ts');
      let d = ts ? new Date(ts) : new Date();
      if (isNaN(d.getTime())) d = new Date();
      el.textContent = fmt.format(d);
      el.setAttribute('title', d.toISOString());
    });
  }

  renderTimes();
  setInterval(renderTimes, 1000);
})();

// Toggle password visibility
function togglePassword() {
  const passwordInput = document.getElementById('password');
  const passwordIcon = document.getElementById('passwordIcon');
  
  if (passwordInput.type === 'password') {
    passwordInput.type = 'text';
    passwordIcon.classList.remove('fa-eye');
    passwordIcon.classList.add('fa-eye-slash');
  } else {
    passwordInput.type = 'password';
    passwordIcon.classList.remove('fa-eye-slash');
    passwordIcon.classList.add('fa-eye');
  }
}

// Modal open/close
function openSeoTeamModal() {
  const modal = document.getElementById('seoTeamModal');
  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
}
function closeSeoTeamModal() {
  const modal = document.getElementById('seoTeamModal');
  modal.classList.remove('active');
  document.body.style.overflow = '';
  document.getElementById('seoTeamForm').reset();
}

// Simulasi submit form
document.getElementById('seoTeamForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const submitBtn = this.querySelector('button[type="submit"]');
  const originalText = submitBtn ? submitBtn.innerHTML : 'Simpan Anggota';
  if (submitBtn) {
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Menyimpan...';
    submitBtn.disabled = true;
  }
  setTimeout(() => {
    alert('Data tim SEO berhasil disimpan!');
    if (submitBtn) {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
    closeSeoTeamModal();
  }, 1500);
});

// Validasi sederhana
document.querySelectorAll('#seoTeamForm input, #seoTeamForm select').forEach(input => {
  input.addEventListener('blur', function() {
    if (!this.value && this.hasAttribute('required')) {
      this.classList.add('border-red-500');
    } else {
      this.classList.remove('border-red-500');
    }
  });
});

// ESC untuk tutup modal
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeSeoTeamModal();
  }
});
</script>

<?= $this->include('admin/layouts/footer'); ?>
