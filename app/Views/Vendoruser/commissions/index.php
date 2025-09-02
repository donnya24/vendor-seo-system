<!-- app/Views/vendoruser/commissions/index.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-2xl p-6 shadow">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-xl font-semibold">Rekap Komisi</h2>
      <!-- Tombol tambah komisi -->
      <button onclick="openModal('<?= site_url('vendoruser/commissions/create') ?>')" 
        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
        + Tambah Komisi
      </button>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden text-center">
        <thead>
          <tr class="bg-blue-600 text-white uppercase text-xs tracking-wide">
            <th class="p-3">Periode</th>
            <th class="p-3">Nominal</th>
            <th class="p-3">Status</th>
            <th class="p-3">Bukti</th>
            <th class="p-3">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <?php if(!empty($items)): ?>
            <?php foreach($items as $it): ?>
              <tr class="hover:bg-gray-50">
                <td class="p-3"><?= esc($it['period_start']); ?> – <?= esc($it['period_end']); ?></td>
                <td class="p-3 font-medium text-gray-800">Rp <?= number_format((float)$it['amount'],0,',','.'); ?></td>
                <td class="p-3">
                  <span class="px-2 py-1 rounded-lg text-xs 
                    <?= $it['status'] === 'paid' ? 'bg-green-100 text-green-700' : 
                       ($it['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600'); ?>">
                    <?= esc(ucfirst($it['status'])); ?>
                  </span>
                </td>
                <td class="p-3">
                  <?php if(!empty($it['proof']) && file_exists(FCPATH.'uploads/commissions/'.$it['proof'])): ?>
                    <a href="<?= base_url('uploads/commissions/'.$it['proof']) ?>" target="_blank" class="text-blue-600 hover:underline">
                      Lihat Bukti
                    </a>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>
                <td class="p-3 flex justify-center gap-2">
                  <button onclick="openModal('<?= site_url('vendoruser/commissions/'.$it['id'].'/edit') ?>')" 
                    class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>

                  <form method="post" action="<?= site_url('vendoruser/commissions/'.$it['id'].'/delete') ?>" class="inline delete-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="p-4 text-center text-gray-500">Belum ada data komisi.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>

    <!-- Catatan verifikasi pembayaran -->
    <div class="mt-3 text-sm text-gray-500">
      Catatan: Pembayaran komisi akan diverifikasi terlebih dahulu oleh Admin/Tim SEO sebelum statusnya menjadi "Paid".
    </div>
    </div>
  </div>
</div>

<!-- Modal untuk create/edit komisi -->
<div id="commissionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
    <div id="modalContent">Loading...</div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal(url) {
  const modal = document.getElementById('commissionModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
  document.getElementById('modalContent').innerHTML = "Loading...";
  fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(res => res.text())
    .then(html => document.getElementById('modalContent').innerHTML = html)
    .catch(() => document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Error loading form.</p>');
}
function closeModal() {
  const modal = document.getElementById('commissionModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
}

// Handle delete dengan SweetAlert2
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Yakin hapus?',
        text: "Data komisi akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        width: 260,
        customClass: {popup:'rounded-md text-sm', title:'text-sm font-semibold', htmlContainer:'text-sm'}
      }).then((result) => {
        if (result.isConfirmed) form.submit();
      });
    });
  });

  // SweetAlert untuk session flash messages
  <?php if(session()->getFlashdata('success')): ?>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: '<?= session()->getFlashdata('success') ?>',
      showConfirmButton: false,
      timer: 2000,
      width: 300,
      customClass: {popup:'rounded-md text-sm', title:'text-sm font-semibold', htmlContainer:'text-sm'}
    });
  <?php endif; ?>

  <?php if(session()->getFlashdata('errors')): ?>
    Swal.fire({
      icon: 'error',
      title: 'Terjadi Kesalahan',
      html: '<?php foreach(session()->getFlashdata('errors') as $e) { echo esc($e) . "<br>"; } ?>',
      width: 300,
      customClass: {popup:'rounded-md text-sm', title:'text-sm font-semibold', htmlContainer:'text-sm'}
    });
  <?php endif; ?>
});
</script>

<?= $this->include('vendoruser/layouts/footer'); ?>
