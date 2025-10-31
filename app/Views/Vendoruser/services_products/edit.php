<div class="max-h-[65vh] overflow-y-auto p-1">
  <form id="editForm" method="post" action="<?= route_to('sp_update_group') ?>" enctype="multipart/form-data" class="space-y-6">
    <?= csrf_field() ?>

    <!-- Nama & Deskripsi Layanan -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">Nama Layanan <span class="text-red-500">*</span></label>
        <input type="text" name="service_name" value="<?= esc($serviceName ?? '') ?>"
          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Masukkan nama layanan" required>
      </div>
      <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">Deskripsi Layanan</label>
        <textarea name="service_description" rows="3"
          class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          placeholder="Masukkan deskripsi layanan"><?= esc($serviceDescription ?? '') ?></textarea>
      </div>
    </div>

    <input type="hidden" name="service_name_original" value="<?= esc($serviceName ?? '') ?>">

    <!-- Produk -->
    <div>
      <div class="flex items-center justify-between mb-4">
        <h3 class="font-semibold text-gray-800 text-sm md:text-base">Produk di Layanan ini</h3>
        <button type="button" id="addProductBtn"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg text-xs md:text-sm hover:bg-blue-700 transition-colors">
          + Tambah Produk
        </button>
      </div>

      <div id="productsWrapper" class="space-y-4">
        <!-- Produk yang sudah ada -->
        <?php $i = 0; foreach ($products as $row): ?>
          <?php
            $pid   = $row['id'] ?? '';
            $pname = $row['product_name'] ?? '';
            $pdesc = $row['product_description'] ?? '';
            $price = isset($row['price']) ? number_format((float)$row['price'], 0, ',', '.') : '';
            $att   = $row['attachment'] ?? '';
            $aurl  = $row['attachment_url'] ?? '';
          ?>
          <div class="product-row border border-gray-200 rounded-lg p-5 bg-gray-50" id="product-row-<?= $i ?>">
            <div class="flex items-start justify-between mb-4">
              <div class="font-medium text-sm text-gray-700">Produk <?= $i + 1 ?></div>
              <button type="button" onclick="deleteProduct(<?= $i ?>, <?= $pid ?>)" class="text-red-600 hover:text-red-800 text-xs transition-colors"
                data-existing-id="<?= esc($pid) ?>">Hapus produk</button>
            </div>

            <input type="hidden" name="products[<?= $i ?>][id]" value="<?= esc($pid) ?>" id="product-id-<?= $i ?>">
            <input type="hidden" name="products[<?= $i ?>][delete_flag]" value="0" id="delete-flag-<?= $i ?>">
            <input type="hidden" name="products[<?= $i ?>][remove_attachment]" value="0" id="remove-attachment-<?= $i ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="products[<?= $i ?>][product_name]" value="<?= esc($pname) ?>"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-name-input"
                  placeholder="Nama produk" required>
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="text" name="products[<?= $i ?>][price]" value="<?= esc($price) ?>"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-price-input price-input"
                  placeholder="0" required>
              </div>
              <div class="md:col-span-2 space-y-2">
                <label class="block text-sm font-medium text-gray-700">Deskripsi Produk</label>
                <textarea name="products[<?= $i ?>][product_description]" rows="3"
                  class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Deskripsi produk"><?= esc($pdesc) ?></textarea>
              </div>

              <div class="md:col-span-2">
                <?php if (!empty($att)): ?>
                  <div class="current-attachment mb-4" id="current-attachment-<?= $i ?>">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lampiran Saat Ini</label>
                    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-white text-sm">
                      <a href="<?= base_url('uploads/vendor_products/'.$att) ?>" target="_blank"
                        class="text-blue-600 hover:underline truncate">Lihat Lampiran</a>
                      <button type="button" onclick="removeAttachment(<?= $i ?>)" class="text-red-600 text-xs hover:underline">
                        Hapus lampiran
                      </button>
                    </div>
                  </div>
                <?php endif; ?>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">
                      <?= !empty($att) ? 'Ganti Lampiran' : 'Unggah Lampiran' ?>
                    </label>
                    <input type="file" name="products[<?= $i ?>][attachment]"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                      accept=".pdf,.jpg,.jpeg,.png">
                    <p class="text-xs text-gray-500 mt-1">PDF/JPG/PNG maks 2MB</p>
                  </div>
                  <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">atau URL Lampiran</label>
                    <input type="url" name="products[<?= $i ?>][attachment_url]" value="<?= esc($aurl) ?>"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="https://contoh.com/file.pdf">
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php $i++; endforeach; ?>

        <!-- Container untuk produk baru -->
        <div id="newProductsContainer"></div>
      </div>
    </div>

    <!-- Tombol Aksi - Layout diperbaiki -->
    <div class="flex justify-end gap-3 pt-6 border-t mt-6">
      <button type="button" onclick="closeModal()"
        class="px-5 py-2.5 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition-colors">
        Batal
      </button>
      <button type="submit" id="submitButton"
        class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors">
        Update Semua
      </button>
    </div>
  </form>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// KONFIGURASI SWEETALERT YANG SAMA PERSIS DENGAN ADMIN
const swalMini = {
  popup: 'rounded-md text-sm p-3 shadow',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

// Format mata uang dengan titik
function formatCurrency(value) {
  // Hapus semua karakter non-angka
  let number = value.replace(/[^\d]/g, '');
  
  // Jika kosong, return kosong
  if (number === '') return '';
  
  // Format dengan titik sebagai pemisah ribuan
  return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Hapus format mata uang
function unformatCurrency(formattedValue) {
  return formattedValue.replace(/[^\d]/g, '');
}

// Counter untuk produk baru
let newProductCount = 0;
let totalProducts = <?= count($products) ?>;

// NOTIFIKASI TOAST YANG SAMA DENGAN ADMIN
function showNotification(icon, title, text) {
  Swal.fire({
    icon: icon,
    title: title,
    text: text,
    toast: true,
    position: 'top-end',
    timer: 3000,
    timerProgressBar: true,
    showConfirmButton: false,
    customClass: swalMini
  });
}

// Hapus produk lama - DENGAN SWEETALERT YANG LEBIH BESAR
function deleteProduct(index, productId) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Produk ini akan dihapus dari layanan',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    width: 400,
    customClass: {
      popup: 'rounded-md p-4',
      title: 'text-lg font-semibold mb-2',
      htmlContainer: 'text-sm mb-3',
      confirmButton: 'px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 mr-2',
      cancelButton: 'px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300'
    }
  }).then((r) => {
    if (!r.isConfirmed) return;
    document.getElementById('delete-flag-' + index).value = '1';
    const row = document.getElementById('product-row-' + index);
    if (row) {
      row.style.opacity = '0.5';
      row.style.pointerEvents = 'none';
      const inputs = row.querySelectorAll('input:not([type="hidden"]), textarea, select');
      inputs.forEach(el => { el.disabled = true; el.required = false; });
    }
    showNotification('success', 'Berhasil', 'Produk akan dihapus saat formulir disimpan');
  });
}

// Hapus lampiran - DENGAN SWEETALERT YANG LEBIH BESAR
function removeAttachment(index) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Lampiran ini akan dihapus',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    width: 400,
    customClass: {
      popup: 'rounded-md p-4',
      title: 'text-lg font-semibold mb-2',
      htmlContainer: 'text-sm mb-3',
      confirmButton: 'px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 mr-2',
      cancelButton: 'px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300'
    }
  }).then((r) => {
    if (!r.isConfirmed) return;
    document.getElementById('remove-attachment-' + index).value = '1';
    const div = document.getElementById('current-attachment-' + index);
    if (div) div.style.display = 'none';
    showNotification('success', 'Berhasil', 'Lampiran akan dihapus saat formulir disimpan');
  });
}

// Hapus produk baru - DENGAN SWEETALERT YANG LEBIH BESAR
function removeNewProduct(i) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Produk baru ini akan dihapus',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, Hapus',
    cancelButtonText: 'Batal',
    width: 400,
    customClass: {
      popup: 'rounded-md p-4',
      title: 'text-lg font-semibold mb-2',
      htmlContainer: 'text-sm mb-3',
      confirmButton: 'px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700 mr-2',
      cancelButton: 'px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300'
    }
  }).then((r) => {
    if (!r.isConfirmed) return;
    const row = document.getElementById('new-product-row-' + i);
    if (row) row.remove();
    showNotification('success', 'Berhasil', 'Produk baru dihapus');
  });
}

// Tambah produk baru
function addNewProduct() {
  const index = totalProducts + newProductCount;
  const i = newProductCount;
  const html = `
    <div class="product-row border border-gray-200 rounded-lg p-5 bg-gray-50 mb-4" id="new-product-row-${i}">
      <div class="flex items-start justify-between mb-4">
        <div class="font-medium text-sm text-gray-700">Produk Baru</div>
        <button type="button" onclick="removeNewProduct(${i})" class="text-red-600 hover:text-red-800 text-xs transition-colors">
          Hapus produk
        </button>
      </div>
      <input type="hidden" name="products[${index}][id]" value="">
      <input type="hidden" name="products[${index}][delete_flag]" value="0">
      <input type="hidden" name="products[${index}][remove_attachment]" value="0">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
          <input type="text" name="products[${index}][product_name]"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-name-input"
            placeholder="Nama produk" required>
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
          <input type="text" name="products[${index}][price]"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-price-input price-input"
            placeholder="0" required>
        </div>
        <div class="md:col-span-2 space-y-2">
          <label class="block text-sm font-medium text-gray-700">Deskripsi Produk</label>
          <textarea name="products[${index}][product_description]" rows="3"
            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Deskripsi produk"></textarea>
        </div>

        <div class="md:col-span-2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">Unggah Lampiran</label>
              <input type="file" name="products[${index}][attachment]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                accept=".pdf,.jpg,.jpeg,.png">
              <p class="text-xs text-gray-500 mt-1">PDF/JPG/PNG maks 2MB</p>
            </div>
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700">atau URL Lampiran</label>
              <input type="url" name="products[${index}][attachment_url]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://contoh.com/file.pdf">
            </div>
          </div>
        </div>
      </div>
    </div>`;
  document.getElementById('newProductsContainer').insertAdjacentHTML('beforeend', html);
  newProductCount++;

  const newRow = document.getElementById('new-product-row-' + (newProductCount - 1));
  if (newRow) {
    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    const firstInput = newRow.querySelector('input');
    if (firstInput) firstInput.focus();
  }
}

// Validasi form
function validateForm(e) {
  const serviceName = document.querySelector('input[name="service_name"]').value.trim();
  if (!serviceName) {
    e.preventDefault();
    showNotification('error', 'Error', 'Nama layanan harus diisi');
    document.querySelector('input[name="service_name"]').focus();
    return false;
  }

  const nameInputs = document.querySelectorAll('.product-name-input');
  const priceInputs = document.querySelectorAll('.product-price-input');
  let isValid = true, msg = '';

  nameInputs.forEach((inp) => {
    const row = inp.closest('.product-row');
    const del = row ? row.querySelector('input[name$="[delete_flag]"]') : null;
    if (row && row.style.opacity !== '0.5' && (!del || del.value === '0')) {
      if (!inp.value.trim()) { isValid = false; msg = 'Nama produk harus diisi untuk semua produk'; inp.focus(); }
    }
  });

  if (isValid) {
    priceInputs.forEach((inp) => {
      const row = inp.closest('.product-row');
      const del = row ? row.querySelector('input[name$="[delete_flag]"]') : null;
      if (row && row.style.opacity !== '0.5' && (!del || del.value === '0')) {
        const v = parseFloat(inp.value.replace(/[^\d]/g, ''));
        if (isNaN(v) || v < 0) { isValid = false; msg = 'Harga produk harus valid (minimal 0)'; inp.focus(); }
      }
    });
  }

  if (!isValid) {
    e.preventDefault();
    showNotification('error', 'Error', msg);
    return false;
  }

  const btn = document.getElementById('submitButton');
  if (btn) { btn.disabled = true; btn.innerHTML = 'Menyimpan...'; }
  return true;
}

// CSRF refresh (opsional)
async function refreshCsrf() {
  try {
    const r = await fetch('<?= site_url('csrf-refresh') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (r.ok) {
      const data = await r.json();
      const csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
      if (csrfInput) csrfInput.value = data.hash;
      return data;
    }
  } catch (e) { console.error('CSRF refresh failed:', e); }
}

// Init
document.addEventListener('DOMContentLoaded', function() {
  const addBtn = document.getElementById('addProductBtn');
  if (addBtn) addBtn.addEventListener('click', addNewProduct);

  const form = document.getElementById('editForm');
  if (form) {
    form.addEventListener('submit', function(e) {
      if (!validateForm(e)) return;
      
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
      
      handleFormSubmit(form);
    });
  }

  <?php if (session()->getFlashdata('success')): ?>
    showNotification('success', 'Berhasil', '<?= addslashes(session()->getFlashdata('success')) ?>');
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    showNotification('error', 'Error', '<?= addslashes(session()->getFlashdata('error')) ?>');
  <?php endif; ?>
});

// Format input harga saat mengetik
document.addEventListener('input', function(e) {
  if (e.target.matches('.price-input')) {
    // Hanya izinkan angka
    let value = e.target.value.replace(/[^\d]/g, '');
    
    // Simpan posisi kursor
    const cursorPosition = e.target.selectionStart;
    
    // Format nilai
    e.target.value = formatCurrency(value);
    
    // Kembalikan posisi kursor
    e.target.setSelectionRange(cursorPosition, cursorPosition);
  }
});

// Format saat paste
document.addEventListener('paste', function(e) {
  if (e.target.matches('.price-input')) {
    e.preventDefault();
    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
    // Hapus semua karakter non-digit
    const cleanValue = pastedText.replace(/[^\d]/g, '');
    // Format nilai
    e.target.value = formatCurrency(cleanValue);
  }
});

// Format saat focus out (untuk memastikan format benar)
document.addEventListener('focusout', function(e) {
  if (e.target.matches('.price-input')) {
    // Format nilai saat keluar dari input
    e.target.value = formatCurrency(e.target.value);
  }
});

// Hapus format saat focus in (untuk memudahkan edit)
document.addEventListener('focusin', function(e) {
  if (e.target.matches('.price-input')) {
    // Hapus format saat fokus untuk memudahkan edit
    e.target.value = unformatCurrency(e.target.value);
  }
});

function handleFormSubmit(form){
  const formData = new FormData(form);
  fetch(form.action, { method:'POST', headers: getCsrfHeaders(), body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.csrfHash) document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.csrfHash);
      if (data.status === 'success') {
        Swal.fire({ 
          icon:'success', 
          title:'Berhasil', 
          text:data.message, 
          timer:1500, 
          showConfirmButton:false, 
          width:300, 
          customClass:swalMini 
        })
        .then(() => { 
          closeModal(); 
          window.location.reload(); 
        });
      } else {
        Swal.fire({ 
          icon:'error', 
          title:'Gagal', 
          text:data.message||'Terjadi kesalahan', 
          width:300, 
          customClass:swalMini 
        });
      }
    })
    .catch(() => Swal.fire({ 
      icon:'error', 
      title:'Error', 
      text:'Koneksi gagal', 
      width:300, 
      customClass:swalMini 
    }));
}

function getCsrfHeaders(){
  const header = document.querySelector('meta[name="csrf-header"]')?.content;
  const token  = document.querySelector('meta[name="csrf-token"]')?.content;
  return header && token ? { [header]: token, 'X-Requested-With': 'XMLHttpRequest' } : { 'X-Requested-With': 'XMLHttpRequest' };
}
</script>