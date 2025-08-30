<div class="mb-4">
  <h2 class="text-lg font-semibold">Edit Layanan</h2>
</div>
<form method="post" action="<?= site_url('vendoruser/services/'.$item['id'].'/update'); ?>" class="space-y-4">
  <?= csrf_field() ?>
  <div>
    <label class="text-sm font-semibold mb-1 block">Nama Layanan <span class="text-red-500">*</span></label>
    <input name="name" value="<?= esc($item['name']); ?>" required class="w-full border rounded-lg px-3 py-2">
  </div>
  <div>
    <label class="text-sm font-semibold mb-1 block">Deskripsi</label>
    <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2"><?= esc($item['description']); ?></textarea>
  </div>
  <div class="pt-2 flex justify-end space-x-2">
    <button type="button" onclick="closeModal()" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">Batal</button>
    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update</button>
  </div>
</form>