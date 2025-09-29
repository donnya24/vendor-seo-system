<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="bg-white shadow rounded-xl p-6">
  <!-- Title -->
  <h2 class="text-xl font-bold mb-6 flex items-center gap-2 text-gray-800">
    <i class="fas fa-building text-blue-600"></i> Daftar Vendor
  </h2>

  <!-- Flash Messages -->
  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-2 rounded-lg mb-4 text-sm">
      <i class="fas fa-check-circle mr-1"></i> <?= session()->getFlashdata('success') ?>
    </div>
  <?php elseif (session()->getFlashdata('error')): ?>
    <div class="bg-red-100 border border-red-200 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm">
      <i class="fas fa-exclamation-circle mr-1"></i> <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <!-- Table -->
  <div class="overflow-x-auto rounded-lg border border-gray-200">
    <table class="min-w-full text-sm divide-y divide-gray-200">
      <thead class="bg-blue-600 text-white text-xs uppercase tracking-wider">
        <tr>
          <th class="px-4 py-3 text-center">No</th>
          <th class="px-4 py-3 text-left">Nama Usaha</th>
          <th class="px-4 py-3 text-left">Pemilik</th>
          <th class="px-4 py-3 text-left">Kontak</th>
          <th class="px-4 py-3 text-center">Komisi Diminta</th>
          <th class="px-4 py-3 text-center">Status</th>
          <th class="px-4 py-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100 bg-white">
        <?php if (!empty($vendors)): ?>
          <?php $no = 1; foreach ($vendors as $vendor): ?>
            <tr class="hover:bg-blue-50 transition-colors">
              <td class="px-4 py-3 text-center text-gray-600"><?= $no++ ?></td>
              <td class="px-4 py-3 font-medium text-gray-900"><?= esc($vendor['business_name']) ?></td>
              <td class="px-4 py-3 text-gray-700"><?= esc($vendor['owner_name']) ?></td>
              <td class="px-4 py-3 text-gray-700">
                <div><?= esc($vendor['phone']) ?></div>
                <?php if (!empty($vendor['whatsapp_number'])): ?>
                  <div class="text-green-600 text-sm">
                    <i class="fab fa-whatsapp"></i> <?= esc($vendor['whatsapp_number']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-center text-gray-900">
                <?= $vendor['requested_commission'] ? esc($vendor['requested_commission']).'%' : '-' ?>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if ($vendor['status'] === 'verified'): ?>
                  <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">Verified</span>
                <?php elseif ($vendor['status'] === 'rejected'): ?>
                  <span class="px-2.5 py-1 rounded-full bg-red-100 text-red-700 text-xs font-medium">Rejected</span>
                <?php elseif ($vendor['status'] === 'inactive'): ?>
                  <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">Inactive</span>
                <?php else: ?>
                  <span class="px-2.5 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-medium">Pending</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-3 text-center">
                <?php if ($vendor['status'] === 'pending'): ?>
                  <a href="<?= site_url('seo/vendor/approve/'.$vendor['id']) ?>"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md bg-green-600 text-white text-xs font-medium shadow-sm hover:bg-green-700 focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition"
                    onclick="return confirm('Yakin menyetujui vendor ini?')">
                    <i class="fas fa-check"></i> Approve
                  </a>
                <?php else: ?>
                  <span class="px-3 py-1.5 rounded-md bg-gray-200 text-gray-600 text-xs">Tidak Ada Aksi</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="px-4 py-10 text-center text-gray-500">
              <i class="fas fa-inbox text-gray-300 text-3xl mb-2"></i>
              <p class="text-sm">Belum ada data vendor.</p>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
