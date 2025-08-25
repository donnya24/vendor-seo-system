<!-- app/Views/vendoruser/services/edit.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-6 shadow max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Edit Layanan</h2>
    <form method="post" action="<?= site_url('vendor/services/'.$item['id'].'/update'); ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-semibold mb-1 block">Nama Layanan</label>
        <input name="name" value="<?= esc($item['name']); ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold mb-1 block">Tipe</label>
        <input name="service_type" value="<?= esc($item['service_type']); ?>" class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold mb-1 block">Deskripsi</label>
        <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2"><?= esc($item['description']); ?></textarea>
      </div>
      <div class="pt-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Update</button>
      </div>
    </form>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
