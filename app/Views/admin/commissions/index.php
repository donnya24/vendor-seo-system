<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<div class="flex-1 flex flex-col overflow-hidden" :class="{ 'md:ml-64': sidebarOpen }">
  <header class="bg-white shadow z-20 sticky top-0"><div class="p-4 font-semibold text-gray-700">Commissions</div></header>
  <main class="flex-1 overflow-y-auto p-4 bg-gray-50">
    <?php if (session()->getFlashdata('success')): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100 text-xs uppercase text-gray-600">
          <tr>
            <th class="px-4 py-2 text-left">Bulan</th>
            <th class="px-4 py-2 text-left">Vendor</th>
            <th class="px-4 py-2 text-left">Nominal</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Paid At</th>
            <th class="px-4 py-2 text-right">Aksi</th>
          </tr>
        </thead>
        <tbody class="text-sm">
          <?php foreach(($items ?? []) as $c): ?>
            <tr class="border-t">
              <td class="px-4 py-2"><?= esc($c['period'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= esc($c['vendor_name'] ?? ($c['vendor_id'] ?? '-')) ?></td>
              <td class="px-4 py-2"><?= number_format((float)($c['amount'] ?? 0), 0, ',', '.') ?></td>
              <td class="px-4 py-2"><?= esc($c['status'] ?? '-') ?></td>
              <td class="px-4 py-2"><?= esc($c['paid_at'] ?? '-') ?></td>
              <td class="px-4 py-2 text-right">
                <?php if (($c['status'] ?? 'pending') !== 'paid'): ?>
                <form action="<?= site_url('admin/commissions/'.$c['id'].'/paid'); ?>" method="post" onsubmit="return confirm('Tandai sudah dibayar?')" class="inline">
                  <?= csrf_field() ?>
                  <button class="px-2 py-1 rounded bg-blue-600 text-white text-xs">Mark Paid</button>
                </form>
                <?php else: ?>
                  <span class="text-green-600 text-xs font-semibold">PAID</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (empty($items)): ?>
            <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Tidak ada data.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<?= $this->include('admin/layouts/footer'); ?>
