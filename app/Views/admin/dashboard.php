<!-- app/Views/vendoruser/dashboard.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>

<div class="flex-1 md:ml-64 p-4">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="bg-white rounded-xl p-4 shadow">
      <div class="text-sm text-gray-500">Leads Baru</div>
      <div class="text-3xl font-bold"><?= (int)$stats['leads_new']; ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow">
      <div class="text-sm text-gray-500">Diproses</div>
      <div class="text-3xl font-bold"><?= (int)$stats['leads_inprogress']; ?></div>
    </div>
    <div class="bg-white rounded-xl p-4 shadow">
      <div class="text-sm text-gray-500">Closed</div>
      <div class="text-3xl font-bold"><?= (int)$stats['leads_closed']; ?></div>
    </div>
  </div>

  <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
    <div class="bg-white rounded-xl p-4 shadow">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Notifikasi</h3>
        <a class="text-sm text-blue-600" href="<?= site_url('vendor/notifications'); ?>">Lihat semua</a>
      </div>
      <p class="text-gray-600 mt-2">Belum dibaca: <b><?= (int)$notifUnread; ?></b></p>
    </div>
    <div class="bg-white rounded-xl p-4 shadow">
      <h3 class="font-semibold">Komisi Bulan Ini</h3>
      <?php if($commission): ?>
        <p class="mt-2 text-gray-700">Periode: <?= esc($commission['period_start']); ?> – <?= esc($commission['period_end']); ?></p>
        <p class="text-lg font-bold mt-1">Rp <?= number_format($commission['amount'],0,',','.'); ?> (<?= esc($commission['status']); ?>)</p>
      <?php else: ?>
        <p class="mt-2 text-gray-500">Belum ada rekap periode berjalan.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->include('vendoruser/layouts/footer'); ?>
