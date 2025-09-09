<div class="max-h-[75vh] overflow-y-auto p-4 border border-gray-200 rounded-lg bg-white shadow">
  <style>[x-cloak]{display:none}</style>

  <form id="createForm" method="post" action="<?= route_to('sp_store') ?>" enctype="multipart/form-data"
        class="space-y-6"
        x-data="{ productCount: 1 }">
    <!-- CSRF bawaan -->
    <?= csrf_field() ?>

    <!-- Info Layanan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Nama Layanan</label>
        <input type="text" name="service_name"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Masukkan nama layanan" required>
      </div>
      <div class="space-y-1">
        <label class="block text-sm font-medium text-gray-700">Deskripsi Layanan</label>
        <textarea name="service_description" rows="2"
          class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Masukkan deskripsi layanan"></textarea>
      </div>
    </div>

    <!-- Produk -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-sm md:text-base">Produk di Layanan ini</h3>
        <button type="button" @click="productCount++"
          class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs md:text-sm hover:bg-blue-700">
          + Tambah Produk
        </button>
      </div>

      <div class="space-y-5" x-show="productCount > 0" x-cloak>
        <template x-for="(_, index) in Array.from({ length: productCount })" :key="index">
          <div class="product-row border rounded-lg p-5 bg-gray-50">
            <div class="flex items-start justify-between mb-4">
              <span class="font-medium text-sm">Produk <span x-text="index + 1"></span></span>
              <button type="button"
                @click="if(productCount>1){productCount--} else {productCount=0}"
                class="text-red-600 hover:text-red-800 text-xs">
                Hapus produk
              </button>
            </div>

            <input type="hidden" :name="'products['+index+'][id]'" value="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1">
                <label class="text-sm">Nama Produk</label>
                <input type="text" :name="'products['+index+'][product_name]'"
                  class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                  placeholder="Nama produk">
              </div>

              <div class="space-y-1">
                <label class="text-sm">Harga (Rp)</label>
                <input type="number" :name="'products['+index+'][price]'"
                  class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                  placeholder="0" step="0.01" min="0">
              </div>

              <div class="md:col-span-2 space-y-1">
                <label class="text-sm">Deskripsi Produk</label>
                <textarea :name="'products['+index+'][product_description]'" rows="2"
                  class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500"
                  placeholder="Deskripsi produk"></textarea>
              </div>
              
              <!-- Bagian yang ditambahkan: Upload File dan URL Lampiran -->
              <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-1">
                  <label class="block text-sm font-medium text-gray-700">Unggah Lampiran</label>
                  <input type="file" :name="'products[' + index + '][attachment]'"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm
                           file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                           file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                           hover:file:bg-blue-100">
                  <p class="text-xs text-gray-500">PDF/JPG/PNG maks 2MB</p>
                </div>
                <div class="space-y-1">
                  <label class="block text-sm font-medium text-gray-700">atau URL Lampiran</label>
                  <input type="url" :name="'products[' + index + '][attachment_url]'"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="https://contoh.com/file.pdf">
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-3 pt-5 border-t">
      <button type="button" onclick="closeModal()"
        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300">
        Batal
      </button>
      <button type="submit"
        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
        Simpan
      </button>
    </div>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const swalMini = {
  popup: 'rounded-md text-sm p-3 shadow',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

async function refreshCsrf() {
  try {
    const response = await fetch('<?= site_url('csrf-refresh') ?>', {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (response.ok) {
      const data = await response.json();
      // Update hidden input csrf_field()
      document.querySelectorAll('input[name="<?= csrf_token() ?>"]').forEach(input => {
        input.name  = data.token; // update name
        input.value = data.hash;  // update value
      });

      // Update <meta> agar bisa dipakai di header fetch
      document.querySelector('meta[name="csrf-header"]').setAttribute('content', data.token);
      document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.hash);

      return data;
    }
  } catch (error) {
    console.error('CSRF refresh failed:', error);
  }
}

(function(){
  const form = document.querySelector('#createForm');
  if (!form) return;

  form.addEventListener('submit', async function(e){
    e.preventDefault();

    const productCount = form.querySelectorAll('.product-row').length;
    if(productCount === 0){
      Swal.fire({ 
        icon: 'warning', 
        title: 'Produk belum ditambahkan',
        text: 'Tambahkan minimal 1 produk', 
        customClass: swalMini 
      });
      return;
    }

    // Refresh CSRF token dulu
    const csrfData = await refreshCsrf();

    Swal.fire({
      title: 'Menyimpan...',
      text: 'Data sedang diproses',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => { Swal.showLoading(); },
      customClass: swalMini
    });

    const formData = new FormData(form);

    // Ambil token terbaru dari <meta>
    const csrfHeader = document.querySelector('meta[name="csrf-header"]').getAttribute('content');
    const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        [csrfHeader]: csrfToken  // ⬅️ kirim CSRF di header juga
      }
    })
    .then(response => response.json())
    .then(data => {
      Swal.close();
      if (data.csrfHash) {
        // update meta setelah submit
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', data.csrfHash);
      }
      if (data.status === 'success') {
        Swal.fire({ 
          icon: 'success', 
          title: 'Berhasil',
          text: data.message,
          customClass: swalMini 
        }).then(() => {
          window.location.href = data.redirect ?? window.location.href;
        });
      } else {
        Swal.fire({ 
          icon: 'error', 
          title: 'Gagal',
          text: data.message,
          customClass: swalMini 
        });
      }
    })
    .catch(error => {
      Swal.close();
      Swal.fire({ 
        icon: 'error', 
        title: 'Error',
        text: 'Terjadi kesalahan saat menyimpan data',
        customClass: swalMini 
      });
    });
  });
})();
</script>
