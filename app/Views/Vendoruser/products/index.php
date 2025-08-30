<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Daftar Produk</h2>
    <button onclick="openModal('<?= site_url('vendoruser/products/create') ?>')" 
      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      + Tambah Produk
    </button>
  </div>

  <div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-2">Nama</th>
          <th class="px-4 py-2">Deskripsi</th>
          <th class="px-4 py-2">Harga</th>
          <th class="px-4 py-2 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($items)): foreach($items as $it): ?>
          <tr class="border-b">
            <td class="px-4 py-2"><?= esc($it['product_name']); ?></td>
            <td class="px-4 py-2">
              <div class="max-w-xs truncate" title="<?= esc($it['description'] ?: 'Tidak ada deskripsi'); ?>">
                <?= esc($it['description'] ?: 'Tidak ada deskripsi'); ?>
              </div>
            </td>
            <td class="px-4 py-2">Rp <?= number_format((float)$it['price'],0,',','.'); ?></td>
            <td class="px-4 py-2 text-center space-x-2">
              <button onclick="openModal('<?= site_url('vendoruser/products/'.$it['id'].'/edit') ?>')" 
                class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>

              <form method="post" action="<?= site_url('vendoruser/products/'.$it['id'].'/delete'); ?>" 
                    class="inline delete-form">
                <?= csrf_field() ?>
                <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada produk.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal -->
<div id="productModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
    <div id="modalContent">Loading...</div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal(url) {
  const modal = document.getElementById('productModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
  document.getElementById('modalContent').innerHTML = "Loading...";

  fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(res => res.text())
    .then(html => document.getElementById('modalContent').innerHTML = html)
    .catch(() => document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Error loading form.</p>');
}

function closeModal() {
  const modal = document.getElementById('productModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
}

// 🔹 Kelas kustom mini dengan teks sedikit lebih besar
const swalMini = {
  popup: 'rounded-md text-sm p-2',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

// 🔹 Handle create & update via AJAX
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

// 🔹 Handle delete dengan SweetAlert2
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      Swal.fire({
        title: 'Yakin hapus?',
        text: "Produk akan dihapus permanen.",
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
            headers: { 'X-Requested-With':'XMLHttpRequest' }
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
