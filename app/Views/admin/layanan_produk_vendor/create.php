<div class="max-h-[65vh] overflow-y-auto">
  <style>[x-cloak]{display:none}</style>

  <form id="createForm" method="post" action="<?= site_url('admin/services/store') ?>" enctype="multipart/form-data"
        class="space-y-6 px-2 py-2"
        x-data="{ productCount: 1 }">
    <!-- CSRF bawaan -->
    <?= csrf_field() ?>

    <!-- Info Vendor -->
    <div class="space-y-2">
      <label class="block text-sm font-medium text-gray-700">Vendor <span class="text-red-500">*</span></label>
      <select name="vendor_id" required
        class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
        <option value="">Pilih Vendor</option>
        <?php foreach ($vendors as $vendor): ?>
          <option value="<?= esc($vendor['id']) ?>"><?= esc($vendor['business_name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Info Layanan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">Nama Layanan <span class="text-red-500">*</span></label>
        <input type="text" name="service_name"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          placeholder="Masukkan nama layanan" required>
      </div>
      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">Deskripsi Layanan</label>
        <textarea name="service_description" rows="3"
          class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
          placeholder="Masukkan deskripsi layanan"></textarea>
      </div>
    </div>

    <!-- Produk -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-base">Produk di Layanan ini</h3>
        <button type="button" @click="productCount++"
          class="px-4 py-2.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors font-medium">
          + Tambah Produk
        </button>
      </div>

      <div class="space-y-5" x-show="productCount > 0" x-cloak>
        <template x-for="(_, index) in Array.from({ length: productCount })" :key="index">
          <div class="product-row border border-gray-200 rounded-xl p-6 bg-gray-50/50">
            <div class="flex items-start justify-between mb-4">
              <span class="font-medium text-sm text-gray-700">Produk <span x-text="index + 1"></span></span>
              <button type="button"
                @click="if(productCount>1){productCount--} else {productCount=0}"
                class="text-red-600 hover:text-red-800 text-xs font-medium transition-colors">
                Hapus produk
              </button>
            </div>

            <input type="hidden" :name="'products['+index+'][id]'" value="">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" :name="'products['+index+'][product_name]'"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  placeholder="Nama produk" required>
              </div>

              <div class="space-y-2">
                <label class="text-sm font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="text" :name="'products['+index+'][price]'" 
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors price-input"
                  placeholder="0" required>
              </div>

              <div class="md:col-span-2 space-y-2">
                <label class="text-sm font-medium text-gray-700">Deskripsi Produk</label>
                <textarea :name="'products['+index+'][product_description]'" rows="3"
                  class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                  placeholder="Deskripsi produk"></textarea>
              </div>
              
              <!-- Bagian yang ditambahkan: Upload File dan URL Lampiran -->
              <div class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">Unggah Lampiran</label>
                  <input type="file" :name="'products[' + index + '][attachment]'"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm
                           file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                           file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700
                           hover:file:bg-blue-100 transition-colors">
                  <p class="text-xs text-gray-500 mt-2">PDF/JPG/PNG maks 2MB</p>
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">atau URL Lampiran</label>
                  <input type="url" :name="'products[' + index + '][attachment_url]'"
                    class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                    placeholder="https://contoh.com/file.pdf">
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <!-- Actions - Layout diperbaiki -->
    <div class="flex justify-end gap-3 pt-6 pb-2">
      <button type="button" onclick="closeModal()"
        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition-colors font-medium">
        Batal
      </button>
      <button type="submit"
        class="px-6 py-3 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors font-medium shadow-sm">
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

    // Validasi vendor
    const vendorId = form.querySelector('select[name="vendor_id"]').value;
    if(!vendorId){
      Swal.fire({ 
        icon: 'warning', 
        title: 'Vendor belum dipilih',
        text: 'Pilih vendor terlebih dahulu', 
        customClass: swalMini 
      });
      return;
    }

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

    // Format semua input harga sebelum submit
    form.querySelectorAll('.price-input').forEach(input => {
      // Simpan nilai asli tanpa format
      const originalName = input.name;
      const originalValue = input.value;
      const unformattedValue = unformatCurrency(originalValue);
      
      // Buat input hidden untuk menyimpan nilai asli
      const hiddenInput = document.createElement('input');
      hiddenInput.type = 'hidden';
      hiddenInput.name = originalName;
      hiddenInput.value = unformattedValue;
      form.appendChild(hiddenInput);
      
      // Ubah nama input asli agar tidak terkirim dua kali
      input.name = originalName + '_formatted';
    });

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

// Format Currency
function formatCurrency(value) {
  // Hapus semua karakter non-angka
  let number = value.replace(/[^\d]/g, '');
  
  // Jika kosong, return kosong
  if (number === '') return '';
  
  // Format dengan titik sebagai pemisah ribuan
  return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function unformatCurrency(formattedValue) {
  return formattedValue.replace(/[^\d]/g, '');
}

// Event listeners untuk format harga
document.addEventListener('input', function(e) {
  if (e.target.matches('.price-input')) {
    let value = e.target.value.replace(/[^\d]/g, '');
    const cursorPosition = e.target.selectionStart;
    e.target.value = formatCurrency(value);
    e.target.setSelectionRange(cursorPosition, cursorPosition);
  }
});

document.addEventListener('paste', function(e) {
  if (e.target.matches('.price-input')) {
    e.preventDefault();
    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
    const cleanValue = pastedText.replace(/[^\d]/g, '');
    e.target.value = formatCurrency(cleanValue);
  }
});

document.addEventListener('focusout', function(e) {
  if (e.target.matches('.price-input')) {
    e.target.value = formatCurrency(e.target.value);
  }
});

document.addEventListener('focusin', function(e) {
  if (e.target.matches('.price-input')) {
    e.target.value = unformatCurrency(e.target.value);
  }
});
</script>