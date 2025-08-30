<!-- Tambah Produk -->
<div class="w-full">
  <h2 class="text-lg font-semibold mb-4">Tambah Produk</h2>
  <form method="post" action="<?= site_url('vendoruser/products/store'); ?>" 
        class="space-y-4" onsubmit="return validateForm(this)">
    <?= csrf_field() ?>
    
    <div>
      <label class="text-sm font-semibold mb-1 block">Nama Produk *</label>
      <input name="product_name" 
             value="<?= old('product_name') ?>" 
             required 
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Deskripsi *</label>
      <textarea name="description" rows="3" required 
                class="w-full border rounded-lg px-3 py-2"><?= old('description') ?></textarea>
    </div>

    <div>
      <label class="text-sm font-semibold mb-1 block">Harga (Rp) *</label>
      <input type="number" name="price" 
             value="<?= old('price') ?>" 
             required min="1" step="1" 
             class="w-full border rounded-lg px-3 py-2">
    </div>

    <div class="pt-2 flex gap-2">
      <!-- Tombol Simpan -->
      <button type="submit" 
              class="px-4 py-2 bg-blue-600 text-white rounded-lg 
                     hover:bg-blue-700 transition-colors duration-200">
        Simpan
      </button>
      <!-- Tombol Batal -->
      <button type="button" onclick="closeModal()" 
              class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg 
                     hover:bg-gray-200 transition-colors duration-200">
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

  if (!nama || !desc || !price || parseFloat(price) <= 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Kolom wajib diisi',
      text: 'Semua kolom harus diisi dengan benar!',
      width: 350,
      customClass: { popup: 'rounded-lg text-sm' }
    });
    return false;
  }
  return true;
}
</script>
