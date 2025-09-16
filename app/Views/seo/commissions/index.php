<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-6">
  <div class="flex items-center justify-between">
    <h2 class="text-2xl font-semibold">Komisi Vendor</h2>
  </div>

  <div class="bg-white p-4 rounded-xl shadow border border-blue-50 overflow-x-auto">
    <table class="min-w-full text-sm border">
      <thead class="bg-blue-600 text-white">
        <tr>
          <th class="p-3 border">No</th>
          <th class="p-3 border">Periode</th>
          <th class="p-3 border">Nama Vendor</th>
          <th class="p-3 border">Jumlah Komisi</th>
          <th class="p-3 border">Bukti Transfer</th>
          <th class="p-3 border">Status</th>
          <th class="p-3 border text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if (!empty($commissions) && is_array($commissions)): ?>
          <?php $no = 1; foreach ($commissions as $c): ?>
            <tr class="hover:bg-blue-50">
              <td class="p-3 border text-center"><?= $no++ ?></td>
              <td class="p-3 border">
                <?= esc($c['period_start']) ?> — <?= esc($c['period_end']) ?>
              </td>
              <td class="p-3 border"><?= esc($c['vendor_name'] ?? '—') ?></td>
              <td class="p-3 border text-right">
                Rp <?= number_format($c['amount'] ?? 0, 0, ',', '.') ?>
              </td>
              <td class="p-3 border text-center">
                <?php if (!empty($c['proof'])): ?>
                  <?php 
                    $ext = strtolower(pathinfo($c['proof'], PATHINFO_EXTENSION));
                    $imgExt = ['jpg','jpeg','png','gif','webp'];
                  ?>
                  <?php if (in_array($ext, $imgExt)): ?>
                    <img src="<?= base_url('uploads/proofs/'.$c['proof']) ?>" 
                         alt="Bukti Transfer" class="h-12 mx-auto rounded border">
                  <?php else: ?>
                    <a href="<?= base_url('uploads/proofs/'.$c['proof']) ?>" 
                       target="_blank" class="text-blue-600 hover:underline">
                      Lihat File
                    </a>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-gray-400">-</span>
                <?php endif; ?>
              </td>
              <td class="p-3 border text-center">
                <?php if (($c['status'] ?? '') === 'approved' || ($c['status'] ?? '') === 'paid'): ?>
                  <span class="px-2 py-1 rounded bg-green-100 text-green-700">Approved</span>
                <?php elseif (($c['status'] ?? '') === 'rejected'): ?>
                  <span class="px-2 py-1 rounded bg-red-100 text-red-700">Rejected</span>
                <?php elseif (($c['status'] ?? '') === 'unpaid'): ?>
                  <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-700">Unpaid</span>
                <?php else: ?>
                  <span class="px-2 py-1 rounded bg-gray-100 text-gray-700"><?= ucfirst($c['status'] ?? '-') ?></span>
                <?php endif; ?>
              </td>
              <td class="p-3 border text-center space-x-2">
                <?php if (($c['status'] ?? '') === 'unpaid'): ?>
                  <form action="<?= site_url('seo/commissions/approve/'.$c['id'].'?vendor_id='.$vendorId) ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" 
                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                            onclick="return confirm('Setujui komisi ini?')">Verify</button>
                  </form>
                  <form action="<?= site_url('seo/commissions/reject/'.$c['id'].'?vendor_id='.$vendorId) ?>" method="post" class="inline">
                    <?= csrf_field() ?>
                    <button type="submit" 
                            class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700"
                            onclick="return confirm('Tolak komisi ini?')">Reject</button>
                  </form>
                <?php elseif (($c['status'] ?? '') === 'paid'): ?>
                  <span class="text-green-700 font-semibold">Sudah Dibayar</span>
                <?php elseif (($c['status'] ?? '') === 'rejected'): ?>
                  <span class="text-red-700 font-semibold">Ditolak</span>
                <?php else: ?>
                  <span class="text-gray-500">Status: <?= ucfirst($c['status']) ?></span>
                <?php endif ?>
              </td>
            </tr>
          <?php endforeach ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="text-center p-4 text-gray-500">Tidak ada data komisi</td>
          </tr>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
