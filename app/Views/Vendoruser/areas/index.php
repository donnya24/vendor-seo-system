<!-- app/Views/vendoruser/areas/index.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-4 shadow">
    <h2 class="text-lg font-semibold mb-4">Area Layanan</h2>
    <?php if(session()->getFlashdata('success')): ?>
      <div class="p-3 mb-3 bg-green-50 text-green-700 rounded"><?= session()->getFlashdata('success'); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
      <?php foreach($areas as $a): $attached = in_array($a['id'], $attachedIds); ?>
        <div class="border rounded-lg p-3">
          <div class="font-semibold"><?= esc($a['name']); ?></div>
          <div class="text-xs text-gray-500"><?= esc($a['type']); ?></div>
          <div class="mt-3">
            <?php if(!$attached): ?>
              <form method="post" action="<?= site_url('vendor/areas/attach'); ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="area_id" value="<?= $a['id']; ?>">
                <button class="px-3 py-1 rounded bg-blue-600 text-white text-sm">Tambahkan</button>
              </form>
            <?php else: ?>
              <form method="post" action="<?= site_url('vendor/areas/'.$a['id'].'/detach'); ?>">
                <?= csrf_field() ?>
                <button class="px-3 py-1 rounded bg-red-600 text-white text-sm">Hapus</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
