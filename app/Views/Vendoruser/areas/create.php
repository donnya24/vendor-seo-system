<!-- File: app/Views/vendoruser/areas/create.php -->
<h2 class="text-xl font-bold mb-4">Tambah Area Baru</h2>
<form method="post" action="<?= site_url('vendoruser/areas/store') ?>" class="space-y-4" id="areaForm">
  <?= csrf_field() ?>
  
  <div>
    <label class="block text-sm font-medium mb-1">Nama Area</label>
    <input type="text" name="name" class="w-full border rounded-lg px-3 py-2" required 
           placeholder="Masukkan nama area">
  </div>
  
  <div>
    <label class="block text-sm font-medium mb-1">Tipe Area</label>
    <select name="type" id="typeSelect" class="w-full border rounded-lg px-3 py-2" required>
      <option value="">Pilih Tipe Area</option>
      <option value="city">Kota</option>
      <option value="province">Provinsi</option>
      <option value="region">Wilayah</option>
    </select>
  </div>
  
  <div class="flex justify-end gap-2 pt-2">
    <button type="button" onclick="closeModal()" 
      class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Batal</button>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan</button>
  </div>
</form>

<script>
// Debug: Pastikan select value tidak dioverride
document.addEventListener('DOMContentLoaded', function() {
  const typeSelect = document.getElementById('typeSelect');
  
  // Log ketika value berubah
  typeSelect.addEventListener('change', function() {
    console.log('Type selected:', this.value);
  });
});
</script>