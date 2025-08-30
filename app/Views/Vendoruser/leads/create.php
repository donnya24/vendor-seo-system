<form method="post" action="<?= site_url('vendoruser/leads/store') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="block text-sm">Tanggal</label>
    <input type="date" name="tanggal" class="w-full border rounded p-2" required>
  </div>
  <div class="mb-3">
    <label class="block text-sm">Layanan</label>
    <select name="service_id" class="w-full border rounded p-2" required>
      <option value="">-- Pilih Layanan --</option>
      <?php foreach ($services as $s): ?>
        <option value="<?= $s['id'] ?>"><?= esc($s['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="block text-sm">Masuk</label>
      <input type="number" name="jumlah_leads_masuk" class="w-full border rounded p-2" required>
    </div>
    <div>
      <label class="block text-sm">Diproses</label>
      <input type="number" name="jumlah_leads_diproses" class="w-full border rounded p-2" required>
    </div>
    <div>
      <label class="block text-sm">Ditolak</label>
      <input type="number" name="jumlah_leads_ditolak" class="w-full border rounded p-2" required>
    </div>
    <div>
      <label class="block text-sm">Closing</label>
      <input type="number" name="jumlah_leads_closing" class="w-full border rounded p-2" required>
    </div>
  </div>

  <div class="flex justify-end gap-2 mt-4">
    <button type="submit" 
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
      Simpan
    </button>
    <button type="button" onclick="closeModal()" 
            class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
      Batal
    </button>
  </div>
</form>
