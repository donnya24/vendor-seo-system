<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Area Layanan</h2>
    <button onclick="openModal('<?= site_url('vendoruser/areas/create') ?>')" 
      class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
      + Tambah Area
    </button>
  </div>

  <div class="bg-white rounded-xl shadow overflow-x-auto">
    <table class="w-full text-sm border border-gray-300 border-collapse text-center">
      <thead class="bg-blue-600 text-white">
        <tr class="uppercase">
          <th class="px-4 py-2 text-center border border-gray-300 font-semibold">ID AREA</th>
          <th class="px-4 py-2 text-center border border-gray-300 font-semibold">NAMA AREA</th>
          <th class="px-4 py-2 text-center border border-gray-300 font-semibold">TIPE</th>
          <th class="px-4 py-2 text-center border border-gray-300 font-semibold">TANGGAL DIBUAT</th>
          <th class="px-4 py-2 text-center border border-gray-300 font-semibold">AKSI</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($areas)): foreach($areas as $a): ?>
          <tr class="hover:bg-gray-50 transition">
            <td class="px-4 py-2 text-center border border-gray-300 font-mono text-sm">
              <?= esc($a['id']) ?>
            </td>
            <td class="px-4 py-2 text-center border border-gray-300">
              <?= esc($a['name']) ?>
            </td>
            <td class="px-4 py-2 text-center border border-gray-300">
              <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700 capitalize">
                <?= esc($a['type']) ?>
              </span>
            </td>
            <td class="px-4 py-2 text-sm text-center border border-gray-300">
              <?= date('d M Y', strtotime($a['created_at'])) ?>
            </td>
            <td class="px-4 py-2 text-center border border-gray-300">
              <?php if(in_array($a['id'],$attachedIds)): ?>
                <button onclick="deleteArea(<?= $a['id'] ?>)" 
                  class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                  Hapus
                </button>
              <?php else: ?>
                <span class="text-gray-400 text-sm">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; else: ?>
          <tr>
            <td colspan="5" class="px-4 py-4 text-center border border-gray-300 text-gray-500">
              Belum ada area layanan.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
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
// 🔹 Kelas kustom mini dengan teks sedikit lebih besar
const swalMini = {
  popup: 'rounded-md text-sm p-2',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

function openModal(url) {
  const modal = document.getElementById('productModal');
  modal.classList.remove('hidden'); 
  modal.classList.add('flex');
  document.getElementById('modalContent').innerHTML = "Loading...";

  fetch(url, { 
    headers: {
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
    } 
  })
  .then(res => res.text())
  .then(html => {
    document.getElementById('modalContent').innerHTML = html;
  })
  .catch(() => {
    document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Error loading form.</p>';
  });
}

function closeModal() {
  const modal = document.getElementById('productModal');
  modal.classList.add('hidden'); 
  modal.classList.remove('flex');
}

// Fungsi untuk menghapus area - DIPERBAIKI DENGAN CSRF TOKEN
function deleteArea(areaId) {
  console.log('Attempting to delete area:', areaId);
  
  Swal.fire({
    title: 'Yakin hapus?',
    text: "Area akan dihapus permanen dari sistem.",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    width: 300,
    customClass: swalMini
  }).then((result) => {
    if (result.isConfirmed) {
      const btn = document.querySelector(`button[onclick="deleteArea(${areaId})"]`);
      const oldText = btn.textContent;
      btn.textContent = 'Menghapus...'; 
      btn.disabled = true;

      // PERBAIKAN: Gunakan FormData dengan CSRF token
      const formData = new FormData();
      formData.append('area_id', areaId);
      formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
      
      fetch('<?= site_url('vendoruser/areas/delete') ?>', {
        method: 'POST',
        body: formData,
        headers: {
          'X-Requested-With': 'XMLHttpRequest'
        }
      })
      .then(response => {
        console.log('Response status:', response.status);
        if (!response.ok) {
          // Jika response tidak ok, coba parsing error message
          return response.text().then(text => {
            throw new Error(`Server error: ${response.status} - ${text}`);
          });
        }
        return response.json();
      })
      .then(data => {
        console.log('Response data:', data);
        if (data.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: data.message,
            width: 260,
            timer: 1800,
            showConfirmButton: false,
            customClass: swalMini
          }).then(() => {
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: data.message || 'Terjadi kesalahan',
            width: 260,
            customClass: swalMini
          });
          btn.textContent = oldText; 
          btn.disabled = false;
        }
      })
      .catch(error => {
        console.error('Error:', error);
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Terjadi kesalahan: ' + error.message,
          width: 260,
          customClass: swalMini
        });
        btn.textContent = oldText; 
        btn.disabled = false;
      });
    }
  });
}

// 🔹 Handle create via AJAX
document.addEventListener('submit', function(e) {
  const form = e.target;
  // Pastikan form berada dalam modal content
  if (form.closest('#modalContent')) {
    e.preventDefault();
    const btn = form.querySelector('button[type="submit"]');
    const oldText = btn.textContent;
    btn.textContent = 'Loading...'; 
    btn.disabled = true;

    fetch(form.action, {
      method: form.method,
      body: new FormData(form),
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(res => {
      if (!res.ok) {
        return res.text().then(text => {
          throw new Error(`Server error: ${res.status} - ${text}`);
        });
      }
      return res.json();
    })
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
        }).then(() => {
          closeModal();
          window.location.reload();
        });
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: data.message || 'Terjadi kesalahan',
          width: 260,
          customClass: swalMini
        });
        btn.textContent = oldText; 
        btn.disabled = false;
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'Terjadi kesalahan: ' + error.message,
        width: 260,
        customClass: swalMini
      });
      btn.textContent = oldText; 
      btn.disabled = false;
    });
  }
});
</script>

<?= $this->include('vendoruser/layouts/footer'); ?>