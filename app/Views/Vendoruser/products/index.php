<!-- app/Views/vendoruser/products/index.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-4 shadow">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold">Produk</h2>
      <a href="<?= site_url('vendor/products/create'); ?>" class="px-3 py-2 rounded bg-blue-600 text-white">Tambah</a>
    </div>
    <?php if(session()->getFlashdata('success')): ?>
      <div class="p-3 mb-3 bg-green-50 text-green-700 rounded"><?= session()->getFlashdata('success'); ?></div>
    <?php endif; ?>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead><tr class="text-left bg-gray-50">
          <th class="p-2">Nama</th><th class="p-2">Deskripsi</th><th class="p-2">Harga</th><th class="p-2 w-40">Aksi</th>
        </tr></thead>
        <tbody>
        <?php foreach($items as $it): ?>
          <tr class="border-b">
            <td class="p-2"><?= esc($it['product_name']); ?></td>
            <td class="p-2 text-gray-600"><?= esc($it['description']); ?></td>
            <td class="p-2">Rp <?= number_format((float)$it['price'],0,',','.'); ?></td>
            <td class="p-2">
              <a class="text-blue-600 mr-3" href="<?= site_url('vendor/products/'.$it['id'].'/edit'); ?>">Edit</a>
              <form method="post" action="<?= site_url('vendor/products/'.$it['id'].'/delete'); ?>" class="inline">
                <?= csrf_field() ?>
                <button onclick="return confirm('Hapus produk ini?')" class="text-red-600">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->include('vendoruser/layouts/footer'); ?>
