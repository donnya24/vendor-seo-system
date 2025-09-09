<form method="post" action="<?= site_url('vendoruser/leads/store') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="block text-sm">Tanggal</label>
    <input type="date" name="tanggal" class="w-full border rounded p-2" required>
  </div>
  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="block text-sm">Leads Masuk</label>
      <input type="number" name="jumlah_leads_masuk" class="w-full border rounded p-2" required>
    </div>
    <div>
      <label class="block text-sm">Leads Closing</label>
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