<?= $this->include('vendoruser/layouts/header'); ?> 

<main class="app-main flex-1 p-4 bg-gray-50">
  <div class="flex items-center justify-between mb-3">
    <h2 class="text-lg md:text-xl font-semibold">Daftar Laporan Leads</h2>
    <div class="flex gap-2">
      <button onclick="openModal('<?= site_url('vendoruser/leads/create') ?>')" 
        class="px-3.5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm md:text-base">
        + Tambah Laporan
      </button>
      <button id="deleteSelectedBtn" 
        class="px-3.5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hidden text-sm md:text-base">
        Hapus Terpilih
      </button>
    </div>
  </div>

  <?php
    // ===== Pagination (15 per page) =====
    $perPage     = 15;
    $allLeads    = $leads ?? [];
    $total       = is_array($allLeads) ? count($allLeads) : 0;
    $page        = max(1, (int)($_GET['page'] ?? 1));
    $totalPages  = max(1, (int)ceil($total / $perPage));
    if ($page > $totalPages) { $page = $totalPages; }
    $offset      = ($page - 1) * $perPage;
    $rows        = array_slice($allLeads, $offset, $perPage);
    $startNo     = $offset + 1;

    // helper build URL dengan mempertahankan filter
    $qs = $_GET ?? [];
    unset($qs['page']);
    $baseUrl = site_url('vendoruser/leads');
    $pageUrl = function(int $p) use ($baseUrl, $qs) {
      $q = array_merge($qs, ['page' => $p]);
      return $baseUrl . (empty($q) ? '' : '?' . http_build_query($q));
    };

    // rentang nomor (maks 5 tombol angka)
    $pgStart = max(1, $page - 2);
    $pgEnd   = min($totalPages, $pgStart + 4);
    $pgStart = max(1, $pgEnd - 4);

    // ambil filter tanggal dari query (biar form terisi kembali)
    $start_date = $_GET['start_date'] ?? '';
    $end_date   = $_GET['end_date'] ?? '';
  ?>

  <!-- FILTER + PAGINATION (sejajar) -->
  <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
    <!-- FILTER TANGGAL -->
    <form method="get" action="<?= site_url('vendoruser/leads'); ?>" class="flex flex-wrap items-end gap-2">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Dari</label>
        <input type="date" name="start_date" value="<?= esc($start_date) ?>"
               class="border rounded-lg px-3 py-2 text-sm">
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
        <input type="date" name="end_date" value="<?= esc($end_date) ?>"
               class="border rounded-lg px-3 py-2 text-sm">
      </div>
      <div class="flex gap-2">
        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
          Filter
        </button>
        <a href="<?= site_url('vendoruser/leads'); ?>" 
           class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
          Reset
        </a>
      </div>
    </form>

    <!-- PAGINATION (pojok kanan atas) -->
    <?php if ($totalPages > 1): ?>
      <nav class="flex items-center">
        <ul class="inline-flex items-center rounded-md border border-blue-400 overflow-hidden text-sm">
          <!-- First -->
          <li>
            <a href="<?= $page>1 ? $pageUrl(1) : 'javascript:void(0)'; ?>"
              class="px-3 py-1 border-r border-blue-400 text-blue-700 <?= $page==1 ? 'opacity-40 pointer-events-none' : 'hover:bg-blue-50' ?>">«</a>
          </li>
          <!-- Prev -->
          <li>
            <a href="<?= $page>1 ? $pageUrl($page-1) : 'javascript:void(0)'; ?>"
              class="px-3 py-1 border-r border-blue-400 text-blue-700 <?= $page==1 ? 'opacity-40 pointer-events-none' : 'hover:bg-blue-50' ?>">‹</a>
          </li>

          <?php for ($p = $pgStart; $p <= $pgEnd; $p++): ?>
            <li>
              <a href="<?= $pageUrl($p) ?>"
                class="px-3.5 py-1 border-r border-blue-400 <?= $p == $page ? 'bg-blue-500 text-white' : 'text-blue-700 hover:bg-blue-50' ?>">
                <?= $p ?>
              </a>
            </li>
          <?php endfor; ?>

          <!-- Next -->
          <li>
            <a href="<?= $page < $totalPages ? $pageUrl($page+1) : 'javascript:void(0)'; ?>"
              class="px-3 py-1 border-r border-blue-400 text-blue-700 <?= $page==$totalPages ? 'opacity-40 pointer-events-none' : 'hover:bg-blue-50' ?>">›</a>
          </li>
          <!-- Last -->
          <li>
            <a href="<?= $page < $totalPages ? $pageUrl($totalPages) : 'javascript:void(0)'; ?>"
              class="px-3 py-1 text-blue-700 <?= $page==$totalPages ? 'opacity-40 pointer-events-none' : 'hover:bg-blue-50' ?>">»</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>
  </div>

  <!-- TABLE (sticky header + scroll wrapper seperti komisi) -->
  <div class="bg-white rounded-xl shadow mb-2">
    <div class="max-h-[60vh] overflow-y-auto overflow-x-auto rounded-lg border border-gray-200">
      <table class="min-w-full text-xs md:text-sm border-collapse text-center">
        <thead class="bg-blue-600 text-white uppercase tracking-wide">
          <tr>
            <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-10">No</th>
            <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1]">Tanggal</th>
            <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] whitespace-nowrap">Leads Masuk</th>
            <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] whitespace-nowrap">Leads Closing</th>
            <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1]">Aksi</th>
            <!-- CHECKBOX DIPINDAH KE SEBELAH KANAN -->
            <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-12">
              <input type="checkbox" id="selectAll" class="w-3 h-3 mx-auto accent-blue-600 cursor-pointer">
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if (!empty($rows)): ?>
            <?php foreach ($rows as $i => $l): ?>
              <tr class="hover:bg-gray-50 transition group">
                <td class="px-2 py-2 font-medium"><?= $startNo + $i ?></td>
                <td class="px-2 py-2"><?= esc($l['tanggal']); ?></td>
                <td class="px-2 py-2"><?= esc($l['jumlah_leads_masuk']); ?></td>
                <td class="px-2 py-2"><?= esc($l['jumlah_leads_closing']); ?></td>
                <td class="px-2 py-2">
                  <div class="flex justify-center items-center gap-1">
                    <button onclick="openModal('<?= site_url('vendoruser/leads/'.$l['id'].'/edit') ?>')" 
                      class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-[11px] md:text-xs transition-colors">
                      Edit
                    </button>
                    <button onclick="showLeadDetail(<?= (int)$l['id'] ?>)" 
                      class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700 text-[11px] md:text-xs transition-colors">
                      Detail
                    </button>
                  </div>
                </td>
                <!-- CHECKBOX DIPINDAH KE SEBELAH KANAN -->
                <td class="px-2 py-2">
                  <input type="checkbox" class="rowCheckbox w-3 h-3 mx-auto accent-blue-600 cursor-pointer transition-transform duration-200 hover:scale-110" value="<?= esc($l['id']); ?>">
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="p-4 text-center text-gray-500">Belum ada laporan leads.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Info jumlah data (opsional, biar konsisten dengan komisi) -->
  <div class="text-xs text-gray-500">
    Menampilkan <span class="font-medium"><?= min($perPage, max(0, $total - $offset)) ?></span> dari <span class="font-medium"><?= $total ?></span> data.
  </div>
</div>

<!-- ========== MODAL ========== -->
<div id="leadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
    <div id="modalContent">Loading...</div>
  </div>
</div>

<!-- Modal untuk Detail Lead -->
<div id="leadDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
    <div class="p-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-xl font-semibold">Detail Laporan Leads</h3>
        <button type="button" onclick="closeLeadDetailModal()" class="text-gray-500 hover:text-gray-700">
          <i class="fas fa-times"></i>
        </button>
      </div>
      <div id="leadDetailContent" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
        <!-- Konten akan diisi oleh JavaScript -->
      </div>
      <div class="mt-6 flex justify-end space-x-2">
        <button type="button" onclick="closeLeadDetailModal()" 
                class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
const swalMini = {
  popup: 'rounded-md text-sm p-3 shadow',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

function getCsrfHeaders() {
  return {
    [document.querySelector('meta[name="csrf-header"]').content]:
      document.querySelector('meta[name="csrf-token"]').content
  };
}

function openModal(url) {
  document.getElementById('leadModal').classList.remove('hidden');
  document.getElementById('leadModal').classList.add('flex');
  document.getElementById('modalContent').innerHTML = "Loading...";

  fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(res => res.text())
    .then(html => document.getElementById('modalContent').innerHTML = html)
    .catch(() => document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Error loading form.</p>');
}

function closeModal() {
  document.getElementById('leadModal').classList.add('hidden');
  document.getElementById('leadModal').classList.remove('flex');
}

// Fungsi untuk menampilkan detail lead dalam modal
function showLeadDetail(leadId) {
  // Tampilkan loading
  document.getElementById('leadDetailContent').innerHTML = `
    <div class="col-span-2 flex justify-center items-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
    </div>
  `;
  
  // Tampilkan modal
  document.getElementById('leadDetailModal').classList.remove('hidden');
  
  // Ambil data lead via AJAX
  fetch(`<?= site_url('vendoruser/leads/') ?>${leadId}`)
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.text();
    })
    .then(html => {
      // Parse HTML response untuk mengambil konten utama
      const parser = new DOMParser();
      const doc = parser.parseFromString(html, 'text/html');
      const leadContent = doc.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-4.text-sm');
      
      if (leadContent) {
        document.getElementById('leadDetailContent').innerHTML = leadContent.innerHTML;
      } else {
        document.getElementById('leadDetailContent').innerHTML = `
          <div class="col-span-2 text-center py-4 text-red-500">
            Gagal memuat detail lead.
          </div>
        `;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      document.getElementById('leadDetailContent').innerHTML = `
        <div class="col-span-2 text-center py-4 text-red-500">
          Terjadi kesalahan saat memuat data.
        </div>
      `;
    });
}

// Fungsi untuk menutup modal detail
function closeLeadDetailModal() {
  document.getElementById('leadDetailModal').classList.add('hidden');
}

// Tutup modal ketika klik di luar konten
document.getElementById('leadDetailModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeLeadDetailModal();
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const selectAll  = document.getElementById('selectAll');
  const deleteBtn  = document.getElementById('deleteSelectedBtn');

  function toggleDeleteBtn() {
    const checked = document.querySelectorAll('.rowCheckbox:checked').length;
    deleteBtn.classList.toggle('hidden', checked === 0);
  }

  // Select all
  selectAll?.addEventListener('change', () => {
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = selectAll.checked);
    toggleDeleteBtn();
  });
  document.querySelectorAll('.rowCheckbox').forEach(cb => cb.addEventListener('change', toggleDeleteBtn));

  // DELETE SELECTED
  deleteBtn.addEventListener('click', () => {
    const ids = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);
    if (ids.length === 0) return;

    Swal.fire({
      title: 'Hapus terpilih?',
      text: `Ada ${ids.length} laporan yang akan dihapus.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      width: 300,
      customClass: swalMini
    }).then((result) => {
      if (result.isConfirmed) {
        fetch("<?= site_url('vendoruser/leads/delete-multiple') ?>", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
            ...getCsrfHeaders()
          },
          body: JSON.stringify({ ids })
        })
        .then(res => res.json())
        .then(data => {
          if (data.csrfHash) {
            document.querySelector("meta[name='csrf-token']").setAttribute("content", data.csrfHash);
          }
          if (data.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: data.message || 'Data terpilih telah dihapus.',
              width: 300,
              timer: 1800,
              showConfirmButton: false,
              customClass: swalMini
            }).then(() => window.location.reload());
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: data.message || 'Terjadi kesalahan',
              width: 300,
              customClass: swalMini
            });
          }
        })
        .catch(() => {
          Swal.fire('Error', 'Koneksi gagal', 'error');
        });
      }
    });
  });
});
</script>

<?= $this->include('vendoruser/layouts/footer'); ?>