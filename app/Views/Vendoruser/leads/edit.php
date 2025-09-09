<form method="post" action="<?= site_url('vendoruser/leads/'.$lead['id'].'/update') ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label class="block text-sm">Tanggal</label>
    <input type="date" name="tanggal" class="w-full border rounded p-2" value="<?= esc($lead['tanggal']) ?>" required>
  </div>

  <div class="grid grid-cols-2 gap-3">
    <div>
      <label class="block text-sm">Leads Masuk</label>
      <input type="number" name="jumlah_leads_masuk" class="w-full border rounded p-2" value="<?= esc($lead['jumlah_leads_masuk']) ?>" required>
    </div>
    <div>
      <label class="block text-sm">Leads Closing</label>
      <input type="number" name="jumlah_leads_closing" class="w-full border rounded p-2" value="<?= esc($lead['jumlah_leads_closing']) ?>" required>
    </div>
  </div>

  <div class="flex justify-end mt-4 gap-2">
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Update</button>
    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">Batal</button>
  </div>
</form>