<?= $this->include('vendoruser/layouts/header'); ?> 
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Daftar Layanan</h2>
    <button onclick="openModal('<?= site_url('vendoruser/services/create') ?>')" 
      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      + Tambah Layanan
    </button>
  </div>

  <!-- TABLE -->
  <div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="px-4 py-2">Nama Layanan</th>
          <th class="px-4 py-2">Deskripsi</th>
          <th class="px-4 py-2 text-center">Status</th>
          <th class="px-4 py-2 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($items)): ?>
          <?php foreach($items as $it): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= esc($it['name']); ?></td>
              <td class="px-4 py-2">
                <?php if (!empty($it['description'])): ?>
                  <div class="max-w-xs truncate" title="<?= esc($it['description']); ?>">
                    <?= esc($it['description']); ?>
                  </div>
                  <button type="button" 
                          class="text-blue-600 text-xs underline hover:text-blue-800" 
                          onclick="showDescription('<?= esc(addslashes($it['name'])) ?>', `<?= esc(addslashes($it['description'])) ?>`)">
                    Lihat
                  </button>
                <?php else: ?>
                  <span class="text-gray-400 italic">Tidak ada deskripsi</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2 text-center">
                <span class="px-2 py-1 rounded text-xs 
                  <?= $it['status']==='active'
                        ? 'bg-green-100 text-green-700'
                        : ($it['status']==='pending'
                              ? 'bg-yellow-100 text-yellow-700'
                              : 'bg-gray-100 text-gray-700') ?>">
                  <?= esc(ucfirst($it['status'])); ?>
                </span>
              </td>
              <td class="px-4 py-2 text-center space-x-2">
                <button onclick="openModal('<?= site_url('vendoruser/services/'.$it['id'].'/edit') ?>')" 
                  class="px-2 py-1 bg-indigo-600 text-white rounded hover:bg-indigo-700">Edit</button>
                
                <form method="post" action="<?= site_url('vendoruser/services/'.$it['id'].'/delete'); ?>" 
                      class="inline delete-form">
                  <?= csrf_field() ?>
                  <button type="submit" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Hapus</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="px-4 py-4 text-center text-gray-500">Belum ada layanan.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <p class="text-xs text-gray-500 mt-3">Status "pending" menunggu verifikasi Admin/SEO.</p>
</div>

<!-- MODAL FORM SERVICE -->
<div id="serviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 max-h-[90vh] overflow-y-auto">
    <div id="modalContent">Loading...</div>
  </div>
</div>

<!-- MODAL DESKRIPSI FULL -->
<div id="descModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6">
    <h3 id="descTitle" class="text-lg font-semibold mb-3">Deskripsi</h3>
    <p id="descContent" class="text-gray-700 whitespace-pre-line"></p>
    <div class="flex justify-end mt-4">
      <button type="button" onclick="closeDescModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Tutup</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function openModal(url) {
  const modal = document.getElementById('serviceModal');
  modal.classList.remove('hidden'); modal.classList.add('flex');
  document.getElementById('modalContent').innerHTML = "Loading...";

  fetch(url, { headers: {'X-Requested-With':'XMLHttpRequest'} })
    .then(res => res.text())
    .then(html => document.getElementById('modalContent').innerHTML = html)
    .catch(() => document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Error loading form.</p>');
}

function closeModal() {
  const modal = document.getElementById('serviceModal');
  modal.classList.add('hidden'); modal.classList.remove('flex');
}

function showDescription(title, content) {
  document.getElementById('descTitle').innerText = title;
  document.getElementById('descContent').innerText = content;
  document.getElementById('descModal').classList.remove('hidden');
  document.getElementById('descModal').classList.add('flex');
}

function closeDescModal() {
  document.getElementById('descModal').classList.add('hidden');
  document.getElementById('descModal').classList.remove('flex');
}

// 🔹 Kelas kustom mini (sama dengan Product)
const swalMini = {
  popup: 'rounded-md text-sm p-2',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

// 🔹 CREATE & UPDATE AJAX
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

// 🔹 DELETE AJAX + SweetAlert
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.delete-form').forEach(form => {
    form.addEventListener('submit', function(e) {
      e.preventDefault();

      Swal.fire({
        title: 'Yakin hapus?',
        text: "Layanan akan dihapus permanen.",
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
