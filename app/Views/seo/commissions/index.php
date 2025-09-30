<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="space-y-8">
  <!-- Header -->
  <div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
      <i class="fas fa-wallet text-blue-600"></i> Komisi Vendor
    </h1>
  </div>

  <!-- Table -->
  <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm divide-y divide-gray-200">
        <thead class="bg-blue-600 text-white text-xs uppercase tracking-wider">
          <tr>
            <th class="px-4 py-3 text-center">No</th>
            <th class="px-4 py-3">Periode</th>
            <th class="px-4 py-3">Nama Vendor</th>
            <th class="px-4 py-3 text-right">Jumlah Komisi</th>
            <th class="px-4 py-3 text-center">Bukti Transfer</th>
            <th class="px-4 py-3 text-center">Status</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          <?php if (!empty($commissions) && is_array($commissions)): ?>
            <?php $no = 1; foreach ($commissions as $c): ?>
              <tr class="hover:bg-blue-50 transition-colors">
                <!-- No -->
                <td class="px-4 py-3 text-center text-gray-600"><?= $no++ ?></td>

                <!-- Periode -->
                <td class="px-4 py-3 text-gray-900">
                  <div class="font-medium"><?= esc($c['period_start']) ?></div>
                  <div class="text-xs text-gray-500">— <?= esc($c['period_end']) ?></div>
                </td>

                <!-- Vendor -->
                <td class="px-4 py-3 font-medium text-gray-900"><?= esc($c['vendor_name'] ?? '—') ?></td>

                <!-- Jumlah -->
                <td class="px-4 py-3 text-right font-semibold text-gray-900">
                  Rp <?= number_format($c['amount'] ?? 0, 0, ',', '.') ?>
                </td>

                <!-- Bukti -->
                <td class="px-4 py-3 text-center">
                  <?php if (!empty($c['proof'])): ?>
                    <?php 
                      $ext = strtolower(pathinfo($c['proof'], PATHINFO_EXTENSION));
                      $imgExt = ['jpg','jpeg','png','gif','webp'];
                    ?>
                    <?php if (in_array($ext, $imgExt)): ?>
                      <a href="<?= base_url('uploads/commissions/'.$c['proof']) ?>" 
                        target="_blank"
                        class="inline-flex items-center px-2 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        <i class="fas fa-eye mr-1"></i> Lihat Bukti
                      </a>
                    <?php else: ?>
                      <span class="text-gray-400">-</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <span class="text-gray-400">-</span>
                  <?php endif; ?>
                </td>

                <!-- Status -->
                <td class="px-4 py-3 text-center">
                  <?php 
                    $status = strtolower($c['status'] ?? '-');
                    $badgeClass = 'bg-gray-100 text-gray-700';
                    $label = ucfirst($status);
                    if ($status === 'approved' || $status === 'paid') {
                      $badgeClass = 'bg-green-100 text-green-700';
                      $label = 'Approved';
                    } elseif ($status === 'rejected') {
                      $badgeClass = 'bg-red-100 text-red-700';
                      $label = 'Rejected';
                    } elseif ($status === 'unpaid') {
                      $badgeClass = 'bg-yellow-100 text-yellow-700';
                      $label = 'Unpaid';
                    }
                  ?>
                  <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= $badgeClass ?>">
                    <?= $label ?>
                  </span>
                </td>

                <!-- Aksi -->
                <td class="px-4 py-3 text-center">
                  <div class="flex flex-col sm:flex-row gap-2 justify-center">
                    <?php if ($status === 'unpaid'): ?>
                      <form action="<?= site_url('seo/commissions/approve/'.$c['id'].'?vendor_id='.$vendorId) ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                class="px-3 py-1.5 bg-green-600 text-white rounded-md text-xs font-medium shadow-sm hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
                                onclick="return confirm('Setujui komisi ini?')">
                          <i class="fas fa-check mr-1"></i> Verify
                        </button>
                      </form>
                      <form action="<?= site_url('seo/commissions/reject/'.$c['id'].'?vendor_id='.$vendorId) ?>" method="post" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" 
                                class="px-3 py-1.5 bg-red-600 text-white rounded-md text-xs font-medium shadow-sm hover:bg-red-700 focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition"
                                onclick="return confirm('Tolak komisi ini?')">
                          <i class="fas fa-times mr-1"></i> Reject
                        </button>
                      </form>
                    <?php elseif ($status === 'paid'): ?>
                      <span class="text-green-700 font-semibold text-sm">Sudah Dibayar</span>
                    <?php elseif ($status === 'rejected'): ?>
                      <span class="text-red-700 font-semibold text-sm">Ditolak</span>
                    <?php else: ?>
                      <span class="text-gray-500 text-sm">Status: <?= ucfirst($c['status']) ?></span>
                    <?php endif ?>
                  </div>
                </td>
              </tr>
            <?php endforeach ?>
          <?php else: ?>
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-gray-500">
                <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
                <p>Tidak ada data komisi</p>
              </td>
            </tr>
          <?php endif ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
