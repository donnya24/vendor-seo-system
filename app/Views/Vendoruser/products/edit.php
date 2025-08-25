<!-- app/Views/vendoruser/products/edit.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-6 shadow max-w-2xl">
    <h2 class="text-lg font-semibold mb-4">Edit Produk</h2>
    <form method="post" action="<?= site_url('vendor/products/'.$item['id'].'/update'); ?>" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label class="text-sm font-semibold mb-1 block">Nama Produk</label>
        <input name="product_name" value="<?= esc($item['product_name']); ?>" required class="w-full border rounded-lg px-3 py-2">
      </div>
      <div>
        <label class="text-sm font-semibold mb-1 block">Deskripsi</label>
        <textarea name="description" rows="4" class="w-full border rounded-lg px-3 py-2"><?= esc($item['description']); ?></textarea>
      </div>
      <div>
        <label class="text-sm font-semibold mb-1 block">Harga (Rp)</label>
        <input type="number" name="price" value="<?= esc($item['price']); ?>" class="w-full border rounded-lg px-3 py-2" min="0">
      </div>
      <div class="pt-2">
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Update</button>
      </div>
    </form>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
