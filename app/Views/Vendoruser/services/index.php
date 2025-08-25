<!-- app/Views/vendoruser/services/index.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-4 shadow">
    <div class="flex items-center justify-between mb-3">
      <h2 class="text-lg font-semibold">Layanan</h2>
      <a href="<?= site_url('vendor/services/create'); ?>" class="px-3 py-2 rounded bg-blue-600 text-white">Tambah</a>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead><tr class="text-left bg-gray-50">
          <th class="p-2">Nama</th><th class="p-2">Tipe</th><th class="p-2">Status</th><th class="p-2 w-40">Aksi</th>
        </tr></thead>
        <tbody>
        <?php foreach($items as $it): ?>
          <tr class="border-b">
            <td class="p-2"><?= esc($it['name']); ?></td>
            <td class="p-2 text-gray-600"><?= esc($it['service_type'] ?: '-'); ?></td>
            <td class="p-2">
              <span class="px-2 py-1 rounded text-xs <?= $it['status']==='active'?'bg-green-100 text-green-700':($it['status']==='pending'?'bg-yellow-100 text-yellow-700':'bg-gray-100 text-gray-700') ?>">
                <?= esc($it['status']); ?>
              </span>
            </td>
            <td class="p-2">
              <a class="text-blue-600 mr-3" href="<?= site_url('vendor/services/'.$it['id'].'/edit'); ?>">Edit</a>
              <form method="post" action="<?= site_url('vendor/services/'.$it['id'].'/delete'); ?>" class="inline">
                <?= csrf_field() ?>
                <button onclick="return confirm('Hapus layanan ini?')" class="text-red-600">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="text-xs text-gray-500 mt-3">Status “pending” menunggu verifikasi Admin/SEO.</p>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
