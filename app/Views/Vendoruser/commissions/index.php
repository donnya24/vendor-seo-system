<main class="app-main flex-1 p-4 bg-gray-50">
  <div class="bg-white rounded-2xl p-5 shadow">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg md:text-xl font-semibold">Rekap Komisi</h2>
      <div class="flex gap-2">
        <button onclick="openModal('<?= site_url('vendoruser/commissions/create') ?>')" 
          class="px-3.5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm md:text-base">
          + Tambah Komisi
        </button>
        <button id="deleteSelectedBtn" 
          class="px-3.5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 hidden text-sm md:text-base">
          Hapus Terpilih
        </button>
      </div>
    </div>

    <!-- ========= FILTER & PAGINATION BAR ========= -->
    <?php
      $qs = $_GET ?? [];
      $start_date = $qs['start_date'] ?? '';
      $end_date   = $qs['end_date']   ?? '';

      $startDateObj = $start_date ? date_create($start_date) : null;
      $endDateObj   = $end_date   ? date_create($end_date)   : null;

      $sourceItems = $items ?? [];
      $filtered    = array_values(array_filter((array)$sourceItems, function($row) use ($startDateObj, $endDateObj) {
        $s = isset($row['period_start']) ? date_create($row['period_start']) : null;
        $e = isset($row['period_end'])   ? date_create($row['period_end'])   : null;
        if (!$s || !$e) return false;
        $okStart = $startDateObj ? ($e >= $startDateObj) : true;
        $okEnd   = $endDateObj   ? ($s <= $endDateObj)   : true;
        return $okStart && $okEnd;
      }));

      // Pagination 10/hal
      $perPage     = 10;
      $total       = count($filtered);
      $page        = max(1, (int)($qs['page'] ?? 1));
      $totalPages  = max(1, (int)ceil($total / $perPage));
      if ($page > $totalPages) { $page = $totalPages; }
      $offset      = ($page - 1) * $perPage;
      $rows        = array_slice($filtered, $offset, $perPage);
      $startNo     = $offset + 1;

      $baseUrl = site_url('vendoruser/commissions');
      $buildUrl = function(array $extra = []) use ($baseUrl, $qs) {
        $merged = array_merge($qs, $extra);
        if (!isset($extra['page'])) unset($merged['page']);
        $q = http_build_query($merged);
        return $q ? $baseUrl.'?'.$q : $baseUrl;
      };

      $pgStart = max(1, $page - 2);
      $pgEnd   = min($totalPages, $pgStart + 4);
      $pgStart = max(1, $pgEnd - 4);
    ?>

    <div class="mb-3 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
      <!-- Filter -->
      <form method="get" action="<?= site_url('vendoruser/commissions'); ?>" class="flex flex-wrap items-end gap-2">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Periode Dari</label>
          <input type="date" name="start_date" value="<?= esc($start_date) ?>" class="border rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Sampai</label>
          <input type="date" name="end_date" value="<?= esc($end_date) ?>" class="border rounded-lg px-3 py-2 text-sm">
        </div>
        <div class="flex gap-2">
          <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">
            Filter
          </button>
          <a href="<?= site_url('vendoruser/commissions'); ?>" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 text-sm">
            Reset
          </a>
        </div>
      </form>

      <!-- Pagination (pojok kanan atas) -->
      <?php if ($totalPages > 1): ?>
      <nav class="flex items-center">
        <ul class="inline-flex items-center rounded-md border border-blue-400 overflow-hidden text-sm">
          <li>
            <a href="<?= $page>1 ? $buildUrl(['page'=>1]) : 'javascript:void(0)'; ?>"
               class="px-3 py-1 border-r border-blue-400 text-blue-700 <?= $page==1?'opacity-40 pointer-events-none':'hover:bg-blue-50' ?>">«</a>
          </li>
          <li>
            <a href="<?= $page>1 ? $buildUrl(['page'=>$page-1]) : 'javascript:void(0)'; ?>"
               class="px-3 py-1 border-r border-blue-400 text-blue-700 <?= $page==1?'opacity-40 pointer-events-none':'hover:bg-blue-50' ?>">‹</a>
          </li>
          <?php for ($p=$pgStart; $p<=$pgEnd; $p++): ?>
          <li>
            <a href="<?= $buildUrl(['page'=>$p]) ?>"
               class="px-3.5 py-1 border-r border-blue-400 <?= $p==$page ? 'bg-blue-500 text-white' : 'text-blue-700 hover:bg-blue-50' ?>">
              <?= $p ?>
            </a>
          </li>
          <?php endfor; ?>
          <li>
            <a href="<?= $page<$totalPages ? $buildUrl(['page'=>$page+1]) : 'javascript:void(0)'; ?>"
               class="px-3 py-1 border-r border-blue-400 text-blue-700 <?= $page==$totalPages?'opacity-40 pointer-events-none':'hover:bg-blue-50' ?>">›</a>
          </li>
          <li>
            <a href="<?= $page<$totalPages ? $buildUrl(['page'=>$totalPages]) : 'javascript:void(0)'; ?>"
               class="px-3 py-1 text-blue-700 <?= $page==$totalPages?'opacity-40 pointer-events-none':'hover:bg-blue-50' ?>">»</a>
          </li>
        </ul>
      </nav>
      <?php endif; ?>
    </div>
    <!-- ========= /FILTER & PAGINATION BAR ========= -->

    <form id="bulkDeleteForm" method="post" action="<?= site_url('vendoruser/commissions/delete-multiple') ?>">
      <?= csrf_field() ?>

      <!-- WRAPPER SCROLL -->
      <div class="max-h-[60vh] overflow-y-auto overflow-x-auto rounded-lg border border-gray-200 mb-3 scrollbar-thin scrollbar-thumb-gray-400 scrollbar-track-gray-100 scrollbar-thumb-rounded-full">
        <table class="min-w-full text-xs md:text-sm text-center">
          <thead class="bg-blue-600 text-white uppercase tracking-wide">
            <tr>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-10">No</th>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-40">Periode</th>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-32">Penghasilan</th>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-28">Nominal</th>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-24">Status</th>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-28">Bukti</th>
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-20">Aksi</th>
              <!-- CHECKBOX HEADER DIPINDAH KE SEBELAH KANAN -->
              <th class="px-2 py-2 sticky top-0 bg-blue-600 z-[1] w-12">
                <input type="checkbox" id="selectAll" class="w-3.5 h-3.5 mx-auto accent-blue-600 cursor-pointer">
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if(!empty($rows)): ?>
              <?php foreach($rows as $i => $it): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-2 py-2 font-medium"><?= $startNo + $i ?></td>
                  <td class="px-2 py-2 whitespace-nowrap"><?= esc($it['period_start']); ?> – <?= esc($it['period_end']); ?></td>
                  <td class="px-2 py-2 font-medium text-gray-800">Rp <?= number_format((float)$it['earning'],0,',','.'); ?></td>
                  <td class="px-2 py-2 font-medium text-gray-800">Rp <?= number_format((float)$it['amount'],0,',','.'); ?></td>
                  <td class="px-2 py-2">
                    <span class="px-2 py-1 rounded-lg text-[11px] md:text-xs 
                      <?= $it['status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'; ?>">
                      <?= esc(ucfirst($it['status'])); ?>
                    </span>
                  </td>
                  <td class="px-2 py-2">
                    <?php if(!empty($it['proof']) && file_exists(FCPATH.'uploads/commissions/'.$it['proof'])): ?>
                      <a href="<?= base_url('uploads/commissions/'.$it['proof']) ?>" target="_blank" class="text-blue-600 hover:underline">
                        Lihat
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400">-</span>
                    <?php endif; ?>
                  </td>
                  <td class="px-2 py-2">
                    <?php if($it['status'] === 'paid'): ?>
                      <button 
                        type="button"
                        disabled
                        class="px-2 py-1 bg-gray-400 text-white rounded cursor-not-allowed text-[11px] md:text-xs opacity-50">
                        Edit
                      </button>
                    <?php else: ?>
                      <button 
                        onclick="openModal('<?= site_url('vendoruser/commissions/'.$it['id'].'/edit') ?>')" 
                        type="button"
                        class="px-2 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-[11px] md:text-xs">
                        Edit
                      </button>
                    <?php endif; ?>
                  </td>
                  <!-- CHECKBOX ROW DIPINDAH KE SEBELAH KANAN -->
                  <td class="px-2 py-2">
                    <?php if($it['status'] !== 'paid'): ?>
                      <input type="checkbox" name="ids[]" value="<?= $it['id'] ?>" 
                             class="rowCheckbox w-3.5 h-3.5 mx-auto accent-blue-600 cursor-pointer transition-transform duration-200 hover:scale-110">
                    <?php else: ?>
                      <input type="checkbox" disabled class="w-3.5 h-3.5 mx-auto opacity-30 cursor-not-allowed">
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="p-4 text-center text-gray-500">Belum ada data komisi.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <!-- END WRAPPER SCROLL -->
    </form>

    <div class="mt-2 text-xs md:text-sm text-gray-500">
      Menampilkan <span class="font-medium"><?= count($rows) ?></span> dari <span class="font-medium"><?= $total ?></span> data.
    </div>

    <div class="mt-2 text-xs md:text-sm text-gray-500">
      Catatan: Pembayaran komisi akan diverifikasi terlebih dahulu oleh Admin/Tim SEO sebelum statusnya menjadi "Paid".
    </div>
  </div>
</main>

<!-- Modal -->
<div id="commissionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
    <div id="modalContent">Loading...</div>
  </div>
</div>

<script>
const swalMini = { popup:'rounded-md text-sm p-3 shadow', title:'text-sm font-semibold', htmlContainer:'text-sm' };

function getCsrfHeaders() {
  const header = document.querySelector('meta[name="csrf-header"]')?.content;
  const token  = document.querySelector('meta[name="csrf-token"]')?.content;
  return header && token ? { [header]: token } : {};
}

function openModal(url) {
  const modal = document.getElementById('commissionModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
  document.getElementById('modalContent').innerHTML = "Loading...";
  fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(res => {
      if (!res.ok) {
        throw new Error('Network response was not ok');
      }
      return res.text();
    })
    .then(html => document.getElementById('modalContent').innerHTML = html)
    .catch(() => {
      document.getElementById('modalContent').innerHTML = 
        '<div class="text-center p-4">' +
        '<p class="text-red-500 mb-2">Error loading form.</p>' +
        '<button onclick="closeModal()" class="px-4 py-2 bg-blue-600 text-white rounded">Tutup</button>' +
        '</div>';
    });
}

function closeModal() {
  const modal = document.getElementById('commissionModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
}

document.addEventListener('DOMContentLoaded', () => {
  const selectAll = document.getElementById('selectAll');
  const deleteBtn = document.getElementById('deleteSelectedBtn');

  function toggleDeleteBtn() {
    const checked = document.querySelectorAll('.rowCheckbox:checked').length;
    if (checked > 0) {
      deleteBtn.classList.remove('hidden');
    } else {
      deleteBtn.classList.add('hidden');
    }
  }

  selectAll?.addEventListener('change', () => {
    document.querySelectorAll('.rowCheckbox:not(:disabled)').forEach(cb => cb.checked = selectAll.checked);
    toggleDeleteBtn();
  });

  document.querySelectorAll('.rowCheckbox').forEach(cb => cb.addEventListener('change', toggleDeleteBtn));

  deleteBtn.addEventListener('click', (e) => {
    e.preventDefault();
    const ids = Array.from(document.querySelectorAll('.rowCheckbox:checked')).map(cb => cb.value);
    if (ids.length === 0) return;

    Swal.fire({
      title: 'Hapus terpilih?',
      text: `Ada ${ids.length} komisi yang akan dihapus.`,
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#2563eb',
      cancelButtonColor: '#6b7280',
      confirmButtonText: 'Ya, hapus',
      cancelButtonText: 'Batal',
      width: 300,
      customClass: swalMini
    }).then((result) => {
      if (!result.isConfirmed) return;

      fetch("<?= site_url('vendoruser/commissions/delete-multiple') ?>", {
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
        if (data?.csrfHash) {
          const meta = document.querySelector("meta[name='csrf-token']");
          if (meta) meta.setAttribute("content", data.csrfHash);
        }
        if (data?.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: data.message || 'Data komisi terpilih telah dihapus.',
            timer: 1800,
            showConfirmButton: false,
            width: 300,
            customClass: swalMini
          }).then(() => window.location.reload());
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: data?.message || 'Terjadi kesalahan saat menghapus.',
            width: 300,
            customClass: swalMini
          });
        }
      })
      .catch(() => Swal.fire('Error','Koneksi gagal','error'));
    });
  });
});
</script>