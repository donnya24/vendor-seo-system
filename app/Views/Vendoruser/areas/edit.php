<h2 class="text-xl font-bold mb-4">Edit Area</h2>
<form method="post" action="<?= site_url('vendoruser/areas/update/'.$area['id']) ?>">
  <?= csrf_field() ?>
  <div class="mb-3">
    <label>Nama Area</label>
    <input type="text" name="name" value="<?= esc($area['name']) ?>" class="w-full border rounded p-2" required>
  </div>
  <div class="mb-3">
    <label>Deskripsi</label>
    <textarea name="description" class="w-full border rounded p-2"><?= esc($area['description']) ?></textarea>
  </div>
  <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Update</button>
  <a href="<?= site_url('vendoruser/areas') ?>" class="px-4 py-2 bg-gray-400 text-white rounded">Batal</a>
</form>
