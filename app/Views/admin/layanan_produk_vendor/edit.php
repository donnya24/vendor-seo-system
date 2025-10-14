<div class="min-h-[60vh] flex items-center justify-center p-4">
  <div class="w-full max-w-2xl md:max-w-3xl">
    <div class="relative">

      <!-- tombol tutup -->
      <button type="button"
              onclick="(window.closeAreasPopup ? closeAreasPopup() : (window.closeModal ? closeModal() : history.back()))"
              class="absolute top-2 right-2 inline-flex h-9 w-9 items-center justify-center rounded-full
                     text-gray-500 hover:text-gray-800 hover:bg-gray-100 focus:outline-none"
              aria-label="Tutup">
        &times;
      </button>

      <!-- card form -->
      <div class="max-h-[70vh] overflow-y-auto p-4 border border-gray-200 rounded-xl bg-white shadow
                  scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 scrollbar-thumb-rounded-full">

        <form id="editForm" method="post" action="<?= site_url('admin/services/update') ?>" enctype="multipart/form-data" class="space-y-5">
          <?= csrf_field() ?>

          <!-- Info Vendor -->
          <div>
            <label class="block text-sm font-medium mb-1 text-gray-700">Vendor <span class="text-red-500">*</span></label>
            <select name="vendor_id" required
              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <?php foreach ($vendors as $vendor): ?>
                <option value="<?= esc($vendor['id']) ?>" <?= (!empty($service) && $service['vendor_id'] == $vendor['id']) ? 'selected' : '' ?>>
                  <?= esc($vendor['business_name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Nama & Deskripsi Layanan -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Nama Layanan <span class="text-red-500">*</span></label>
              <input type="text" name="service_name" value="<?= esc($service['service_name'] ?? '') ?>"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan nama layanan" required>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Deskripsi Layanan</label>
              <textarea name="service_description" rows="2"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="Masukkan deskripsi layanan"><?= esc($service['service_description'] ?? '') ?></textarea>
            </div>
          </div>

          <input type="hidden" name="service_name_original" value="<?= esc($service['service_name'] ?? '') ?>">
          <input type="hidden" name="vendor_id_original" value="<?= esc($service['vendor_id'] ?? '') ?>">

          <!-- Produk -->
          <div>
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-semibold text-gray-800 text-sm md:text-base">Produk di Layanan ini</h3>
              <button type="button" id="addProductBtn"
                class="px-3 py-1.5 bg-blue-600 text-white rounded-lg text-xs md:text-sm hover:bg-blue-700 transition-colors">
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
                  $price = $row['price'] ?? '';
                  $att   = $row['attachment'] ?? '';
                  $aurl  = $row['attachment_url'] ?? '';
                ?>
                <div class="product-row border border-gray-200 rounded-lg p-4 bg-gray-50" id="product-row-<?= $i ?>">
                  <div class="flex items-start justify-between mb-3">
                    <div class="font-medium text-sm text-gray-700">Produk <?= $i + 1 ?></div>
                    <button type="button" onclick="deleteProduct(<?= $i ?>, <?= $pid ?>)" class="text-red-600 hover:text-red-800 text-xs transition-colors"
                      data-existing-id="<?= esc($pid) ?>">Hapus produk</button>
                  </div>

                  <input type="hidden" name="products[<?= $i ?>][id]" value="<?= esc($pid) ?>" id="product-id-<?= $i ?>">
                  <input type="hidden" name="products[<?= $i ?>][delete_flag]" value="0" id="delete-flag-<?= $i ?>">
                  <input type="hidden" name="products[<?= $i ?>][remove_attachment]" value="0" id="remove-attachment-<?= $i ?>">

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium mb-1 text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
                      <input type="text" name="products[<?= $i ?>][product_name]" value="<?= esc($pname) ?>"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-name-input"
                        placeholder="Nama produk" required>
                    </div>
                    <div>
                      <label class="block text-sm font-medium mb-1 text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
                      <input type="text" name="products[<?= $i ?>][price]" value="<?= number_format((float)$price, 0, ',', '.') ?>" 
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-price-input price-input"
                        placeholder="0" required>
                    </div>
                    <div class="md:col-span-2">
                      <label class="block text-sm font-medium mb-1 text-gray-700">Deskripsi Produk</label>
                      <textarea name="products[<?= $i ?>][product_description]" rows="2"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Deskripsi produk"><?= esc($pdesc) ?></textarea>
                    </div>

                    <div class="md:col-span-2">
                      <?php if (!empty($att)): ?>
                        <div class="current-attachment mb-3" id="current-attachment-<?= $i ?>">
                          <label class="block text-sm font-medium mb-1 text-gray-700">Lampiran Saat Ini</label>
                          <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg bg-white text-sm">
                            <a href="<?= base_url('uploads/vendor_products/'.$att) ?>" target="_blank"
                              class="text-blue-600 hover:underline truncate">Lihat Lampiran</a>
                            <button type="button" onclick="removeAttachment(<?= $i ?>)" class="text-red-600 text-xs hover:underline">
                              Hapus lampiran
                            </button>
                          </div>
                          <input type="hidden" name="products[<?= $i ?>][existing_attachment]" value="<?= esc($att) ?>">
                        </div>
                      <?php endif; ?>

                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">
                            <?= !empty($att) ? 'Ganti Lampiran' : 'Unggah Lampiran' ?>
                          </label>
                          <!-- Perbaikan: Ubah nama input file agar konsisten -->
                          <input type="file" name="products[<?= $i ?>][attachment]"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            accept=".pdf,.jpg,.jpeg,.png">
                          <p class="text-xs text-gray-500 mt-1">PDF/JPG/PNG maks 2MB</p>
                        </div>
                        <div>
                          <label class="block text-sm font-medium mb-1 text-gray-700">atau URL Lampiran</label>
                          <input type="url" name="products[<?= $i ?>][attachment_url]" value="<?= esc($aurl) ?>"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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

          <!-- Tombol Aksi -->
          <div class="flex justify-end gap-3 pt-4 border-t sticky bottom-0 bg-white">
            <button type="button"
              onclick="(window.closeAreasPopup ? closeAreasPopup() : (window.closeModal ? closeModal() : history.back()))"
              class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm hover:bg-gray-300 transition-colors">
              Batal
            </button>
            <button type="submit" id="submitButton"
              class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition-colors">
              Update Semua
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Konfigurasi SweetAlert
const swalMini = {
  popup: 'rounded-md p-4',
  title: 'text-lg font-semibold',
  htmlContainer: 'text-sm',
  confirmButton: 'px-4 py-2 rounded-lg text-sm',
  cancelButton: 'px-4 py-2 rounded-lg text-sm'
};

// Fungsi format currency
function formatCurrency(value) {
  let number = value.replace(/[^\d]/g, '');
  if (number === '') return '';
  return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function unformatCurrency(formattedValue) {
  return formattedValue.replace(/[^\d]/g, '');
}

// SIMPAN JUMLAH PRODUK AWAL DAN INDEX BERIKUTNYA
const existingProductCount = <?= count($products) ?>;
let nextProductIndex = existingProductCount;

// Notifikasi
function showNotification(icon, title, text) {
  Swal.fire({
    icon, title, text, toast: true, position: 'top-end',
    showConfirmButton: false, timer: 3000, timerProgressBar: true,
    customClass: swalMini
  });
}

// Hapus produk lama
function deleteProduct(index, productId) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Produk ini akan dihapus dari layanan',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal',
    customClass: swalMini
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

// Hapus lampiran
function removeAttachment(index) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Lampiran ini akan dihapus',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal',
    customClass: swalMini
  }).then((r) => {
    if (!r.isConfirmed) return;
    document.getElementById('remove-attachment-' + index).value = '1';
    const div = document.getElementById('current-attachment-' + index);
    if (div) div.style.display = 'none';
    showNotification('success', 'Berhasil', 'Lampiran akan dihapus saat disimpan');
  });
}

// Hapus produk baru
function removeNewProduct(uniqueId) {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Produk baru ini akan dihapus',
    icon: 'warning', showCancelButton: true,
    confirmButtonColor: '#3085d6', cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!', cancelButtonText: 'Batal',
    customClass: swalMini
  }).then((r) => {
    if (!r.isConfirmed) return;
    const row = document.getElementById('new-product-row-' + uniqueId);
    if (row) row.remove();
    // Update nomor produk setelah penghapusan
    updateProductNumbers();
    showNotification('success', 'Berhasil', 'Produk baru dihapus');
  });
}

// Fungsi untuk mengupdate nomor produk
function updateProductNumbers() {
  // Update produk yang sudah ada
  const existingRows = document.querySelectorAll('#productsWrapper .product-row[id^="product-row-"]');
  existingRows.forEach((row, index) => {
    const titleElement = row.querySelector('.font-medium.text-sm.text-gray-700');
    if (titleElement) {
      titleElement.textContent = 'Produk ' + (index + 1);
    }
  });
  
  // Update produk baru
  const newRows = document.querySelectorAll('#newProductsContainer .product-row[id^="new-product-row-"]');
  const existingCount = existingRows.length;
  newRows.forEach((row, index) => {
    const titleElement = row.querySelector('.font-medium.text-sm.text-gray-700');
    if (titleElement) {
      titleElement.textContent = 'Produk ' + (existingCount + index + 1);
    }
  });
}

// Tambah produk baru
function addNewProduct() {
  const currentIndex = nextProductIndex++;
  const uniqueId = 'new-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
  
  console.log('Adding new product with index:', currentIndex, 'uniqueId:', uniqueId);
  
  // Hitung jumlah produk yang ada untuk memberi nomor yang benar
  const existingRows = document.querySelectorAll('#productsWrapper .product-row[id^="product-row-"]').length;
  const newRows = document.querySelectorAll('#newProductsContainer .product-row[id^="new-product-row-"]').length;
  const productNumber = existingRows + newRows + 1;
  
  const html = `
    <div class="product-row border border-gray-200 rounded-lg p-4 bg-gray-50" id="new-product-row-${uniqueId}">
      <div class="flex items-start justify-between mb-3">
        <div class="font-medium text-sm text-gray-700">Produk ${productNumber}</div>
        <button type="button" onclick="removeNewProduct('${uniqueId}')" class="text-red-600 hover:text-red-800 text-xs transition-colors">
          Hapus produk
        </button>
      </div>
      <input type="hidden" name="products[${currentIndex}][id]" value="">
      <input type="hidden" name="products[${currentIndex}][delete_flag]" value="0">
      <input type="hidden" name="products[${currentIndex}][remove_attachment]" value="0">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
          <input type="text" name="products[${currentIndex}][product_name]"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-name-input"
            placeholder="Nama produk" required>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
          <input type="text" name="products[${currentIndex}][price]"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-price-input price-input"
            placeholder="0" required>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1 text-gray-700">Deskripsi Produk</label>
          <textarea name="products[${currentIndex}][product_description]" rows="2"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Deskripsi produk"></textarea>
        </div>

        <div class="md:col-span-2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Unggah Lampiran</label>
              <input type="file" name="products[${currentIndex}][attachment]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                accept=".pdf,.jpg,.jpeg,.png">
              <p class="text-xs text-gray-500 mt-1">PDF/JPG/PNG maks 2MB</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">atau URL Lampiran</label>
              <input type="url" name="products[${currentIndex}][attachment_url]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://contoh.com/file.pdf">
            </div>
          </div>
        </div>
      </div>
    </div>`;
  
  const container = document.getElementById('newProductsContainer');
  container.insertAdjacentHTML('beforeend', html);

  const newRow = document.getElementById('new-product-row-' + uniqueId);
  if (newRow) {
    newRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    const firstInput = newRow.querySelector('input[type="text"]');
    if (firstInput) firstInput.focus();
  }
}

// Tutup modal
function closeModal() {
  const modal = document.querySelector('.modal');
  if (modal) modal.style.display = 'none';
}

// Validasi form
function validateForm(e) {
  const vendorId = document.querySelector('select[name="vendor_id"]').value;
  if (!vendorId) {
    e.preventDefault();
    showNotification('error', 'Error', 'Vendor harus dipilih');
    document.querySelector('select[name="vendor_id"]').focus();
    return false;
  }

  const serviceName = document.querySelector('input[name="service_name"]').value.trim();
  if (!serviceName) {
    e.preventDefault();
    showNotification('error', 'Error', 'Nama layanan harus diisi');
    document.querySelector('input[name="service_name"]').focus();
    return false;
  }

  const nameInputs = document.querySelectorAll('.product-name-input');
  const priceInputs = document.querySelectorAll('.price-input');
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
        const v = parseFloat(unformatCurrency(inp.value));
        if (isNaN(v) || v < 0) { isValid = false; msg = 'Harga produk harus valid (minimal 0)'; inp.focus(); }
      }
    });
  }

  if (!isValid) {
    e.preventDefault();
    showNotification('error', 'Error', msg);
    return false;
  }

  // Format semua input harga sebelum submit
  const form = document.getElementById('editForm');
  form.querySelectorAll('.price-input').forEach(input => {
    const originalName = input.name;
    const originalValue = input.value;
    const unformattedValue = unformatCurrency(originalValue);
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = originalName;
    hiddenInput.value = unformattedValue;
    form.appendChild(hiddenInput);
    
    input.name = originalName + '_formatted';
  });

  const btn = document.getElementById('submitButton');
  if (btn) { btn.disabled = true; btn.innerHTML = 'Menyimpan...'; }
  return true;
}

// Format input harga
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

// Handle form submit dengan debugging
function handleFormSubmit(form) {
  // Debug: Log semua data form sebelum submit
  console.log('Form data before submit:');
  const formData = new FormData(form);
  for (let pair of formData.entries()) {
    console.log(pair[0] + ':', pair[1]);
  }
  
  const fileInputs = form.querySelectorAll('input[type="file"]');
  console.log('File inputs found:', fileInputs.length);
  fileInputs.forEach((input, index) => {
    console.log(`File input ${index}:`, {
      name: input.name,
      files: input.files.length,
      fileName: input.files[0] ? input.files[0].name : 'none'
    });
  });

  // Format semua input harga sebelum submit
  form.querySelectorAll('.price-input').forEach(input => {
    const originalName = input.name;
    const originalValue = input.value;
    const unformattedValue = unformatCurrency(originalValue);
    
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = originalName;
    hiddenInput.value = unformattedValue;
    form.appendChild(hiddenInput);
    
    input.name = originalName + '_formatted';
  });

  // Buat FormData baru untuk memastikan file terkirim
  const submitFormData = new FormData(form);
  
  console.log('FormData to be submitted:');
  for (let pair of submitFormData.entries()) {
    console.log(pair[0] + ':', pair[1]);
  }

  fetch(form.action, { 
    method:'POST', 
    body: submitFormData,
    headers: getCsrfHeaders()
  })
    .then(res => {
      console.log('Response status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Response data:', data);
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
          .then(() => { closeModal(); window.location.reload(); });
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
    .catch(error => {
      console.error('Fetch error:', error);
      Swal.fire({ 
        icon:'error', 
        title:'Error', 
        text:'Koneksi gagal', 
        width:300, 
        customClass:swalMini 
      });
    });
}

// Init
document.addEventListener('DOMContentLoaded', function() {
  console.log('DOM Content Loaded, initializing...');
  console.log('Existing product count:', existingProductCount);
  console.log('Next product index:', nextProductIndex);
  
  const addBtn = document.getElementById('addProductBtn');
  if (addBtn) {
    addBtn.style.cursor = 'pointer';
    addBtn.addEventListener('click', function(e) {
      e.preventDefault();
      console.log('Add product button clicked!');
      addNewProduct();
    });
    console.log('Add product button listener attached');
  } else {
    console.error('Add product button not found!');
  }

  const form = document.getElementById('editForm');
  if (form) form.addEventListener('submit', validateForm);

  <?php if (session()->getFlashdata('success')): ?>
    showNotification('success', 'Berhasil', '<?= addslashes(session()->getFlashdata('success')) ?>');
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    showNotification('error', 'Error', '<?= addslashes(session()->getFlashdata('error')) ?>');
  <?php endif; ?>
});

// Ekspos fungsi ke global scope
window.addNewProduct = addNewProduct;
window.removeNewProduct = removeNewProduct;
window.deleteProduct = deleteProduct;
window.removeAttachment = removeAttachment;
</script>