<?= $this->include('vendoruser/layouts/header'); ?> 
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Daftar Laporan Leads</h2>
    <button onclick="openModal('<?= site_url('vendoruser/leads/create') ?>')" 
      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      + Tambah Laporan
    </button>
  </div>

  <!-- TABLE -->
  <div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-2">Tanggal</th>
          <th class="px-4 py-2">Layanan</th>
          <th class="px-4 py-2 text-center">Masuk</th>
          <th class="px-4 py-2 text-center">Diproses</th>
          <th class="px-4 py-2 text-center">Ditolak</th>
          <th class="px-4 py-2 text-center">Closing</th>
          <th class="px-4 py-2 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($leads)): ?>
          <?php foreach ($leads as $l): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= esc($l['tanggal']); ?></td>
              <td class="px-4 py-2"><?= esc($l['service_name'] ?? '-'); ?></td>
              <td class="px-4 py-2 text-center"><?= esc($l['jumlah_leads_masuk']); ?></td>
              <td class="px-4 py-2 text-center"><?= esc($l['jumlah_leads_diproses']); ?></td>
              <td class="px-4 py-2 text-center"><?= esc($l['jumlah_leads_ditolak']); ?></td>
              <td class="px-4 py-2 text-center"><?= esc($l['jumlah_leads_closing']); ?></td>
              <td class="px-4 py-2 text-center space-x-2">
                <button onclick="openModal('<?= site_url('vendoruser/leads/'.$l['id'].'/edit') ?>')" 
                  class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>

                <form method="post" action="<?= site_url('vendoruser/leads/'.$l['id'].'/delete'); ?>" 
                      class="inline delete-form">
                  <?= csrf_field() ?>
                  <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="px-4 py-4 text-center text-gray-500">Belum ada laporan leads.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ========== MODAL ========== -->
<div id="leadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-lg p-6">
    <div id="modalContent">Loading...</div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const swalMini = {
  popup: 'rounded-md text-sm p-3 shadow',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

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

// CREATE & EDIT via AJAX
document.addEventListener('submit', function(e) {
  const form = e.target;
  if (form.closest('#modalContent')) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const oldText = btn.textContent;
    btn.textContent = 'Loading...'; btn.disabled = true;

    fetch(form.action, {
      method: form.method,
      body: new FormData(form),
      headers: {'X-Requested-With':'XMLHttpRequest'}
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: data.message,
          width: 260,
          timer: 1800,
          showConfirmButton: false,
          customClass: swalMini
        }).then(() => window.location.reload());
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: data.message || 'Terjadi kesalahan',
          width: 260,
          customClass: swalMini
        });
        btn.textContent = oldText; btn.disabled = false;
      }
    })
    .catch(() => {
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Terjadi kesalahan koneksi',
        width: 260,
        customClass: swalMini
      });
      btn.textContent = oldText; btn.disabled = false;
    });
  }
});

// DELETE pakai SweetAlert konfirmasi
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      Swal.fire({
        title: 'Yakin hapus?',
        text: "Laporan ini akan dihapus permanen.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya',
        cancelButtonText: 'Batal',
        width: 260,
        customClass: swalMini
      }).then((result) => {
        if (result.isConfirmed) {
          fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {'X-Requested-With':'XMLHttpRequest'}
          })
          .then(res => res.json())
          .then(data => {
            if (data.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                width: 260,
                timer: 1800,
                showConfirmButton: false,
                customClass: swalMini
              }).then(() => window.location.reload());
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: data.message || 'Terjadi kesalahan',
                width: 260,
                customClass: swalMini
              });
            }
          })
          .catch(() => {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Terjadi kesalahan koneksi',
              width: 260,
              customClass: swalMini
            });
          });
        }
      });
    });
  });
});
</script>

<?= $this->include('vendoruser/layouts/footer'); ?>
