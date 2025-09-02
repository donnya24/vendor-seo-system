<!-- Edit Produk -->
<div class="w-full">
  <h2 class="text-lg font-semibold mb-4">Edit Produk</h2>
  <form method="post" action="<?= site_url('vendoruser/products/'.$item['id'].'/update'); ?>" 
        class="space-y-4" onsubmit="return validateForm(this)" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div>
      <label class="text-sm font-semibold mb-1 block">Nama Produk *</label>
      <input name="product_name" 
             value="<?= old('product_name', esc($item['product_name'])) ?>" 
             required 
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Deskripsi *</label>
      <textarea name="description" rows="3" required 
                class="w-full border rounded-lg px-3 py-2"><?= old('description', esc($item['description'])) ?></textarea>
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Harga (Rp) *</label>
      <input type="number" name="price" 
             value="<?= old('price', isset($item['price']) ? (float)$item['price'] : '') ?>" 
             required min="1" step="1" 
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Lampiran</label>
      <?php if(!empty($item['attachment'])): ?>
        <div class="mb-2">
          <a href="<?= base_url('uploads/vendor_products/'.$item['attachment']); ?>" target="_blank" class="text-blue-600 hover:underline">
            Lihat Lampiran Saat Ini
          </a>
        </div>
      <?php endif; ?>
      <input type="file" name="attachment" 
             class="w-full border rounded-lg px-3 py-2"
             accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
      <p class="text-sm text-gray-500 mt-1">Format: PDF, JPG, PNG, Word, Excel, PPT (Opsional, Maks 10MB)</p>
    </div>

    <div class="pt-2 flex gap-2">
      <button type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
        Update
      </button>
      <button type="button" 
              onclick="closeModal()" 
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors duration-200">
        Batal
      </button>
    </div>
  </form>
</div>

<!-- Validasi Frontend -->
<script>
function validateForm(form) {
  let nama = form.querySelector('[name="product_name"]').value.trim();
  let desc = form.querySelector('[name="description"]').value.trim();
  let price = form.querySelector('[name="price"]').value.trim();
  let file  = form.querySelector('[name="attachment"]').files[0];

  if (!nama || !desc || !price || parseFloat(price) <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Kolom wajib diisi',
      text: 'Nama produk, deskripsi, dan harga harus diisi dengan benar!',
      width: 350,
      customClass: { popup: 'rounded-lg text-sm' }
    });
    return false;
  }

  // Validasi file max 10MB
  if (file && file.size > 10 * 1024 * 1024) {
    Swal.fire({
      icon: 'warning',
      title: 'File terlalu besar',
      text: 'Lampiran maksimal 10MB',
      width: 350,
      customClass: { popup: 'rounded-lg text-sm' }
    });
    return false;
  }

  return true;
}
</script>
