<!-- app/Views/vendoruser/commissions/index.php -->
<?= $this->include('vendoruser/layouts/header'); ?>
<?= $this->include('vendoruser/layouts/sidebar'); ?>
<div class="flex-1 md:ml-64 p-4">
  <div class="bg-white rounded-xl p-4 shadow">
    <h2 class="text-lg font-semibold mb-3">Rekap Komisi</h2>

    <?php if(session()->getFlashdata('success')): ?>
      <div class="p-3 mb-3 bg-green-50 text-green-700 rounded"><?= session()->getFlashdata('success'); ?></div>
    <?php endif; ?>

    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead><tr class="text-left bg-gray-50">
          <th class="p-2">Periode</th><th class="p-2">Leads</th><th class="p-2">Nominal</th><th class="p-2">Status</th>
        </tr></thead>
        <tbody>
        <?php foreach($items as $it): ?>
          <tr class="border-b">
            <td class="p-2"><?= esc($it['period_start']); ?> – <?= esc($it['period_end']); ?></td>
            <td class="p-2"><?= (int)$it['leads_count']; ?></td>
            <td class="p-2">Rp <?= number_format((float)$it['amount'],0,',','.'); ?></td>
            <td class="p-2"><?= esc($it['status']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mt-6 border-t pt-4">
      <h3 class="font-semibold mb-2">Kirim Konfirmasi Pembayaran</h3>
      <form method="post" action="<?= site_url('vendor/commissions/request-paid'); ?>" class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <?= csrf_field() ?>
        <input type="number" min="0" name="amount" placeholder="Nominal transfer (Rp)" required class="border rounded-lg px-3 py-2">
        <input type="text" name="note" placeholder="Catatan/Bukti (URL/Referensi)" class="border rounded-lg px-3 py-2 md:col-span-2">
        <div class="md:col-span-3">
          <button class="px-4 py-2 bg-blue-600 text-white rounded-lg">Kirim</button>
        </div>
      </form>
      <p class="text-xs text-gray-500 mt-2">* Admin/SEO akan melakukan verifikasi pembayaran kamu.</p>
    </div>
  </div>
</div>
<?= $this->include('vendoruser/layouts/footer'); ?>
