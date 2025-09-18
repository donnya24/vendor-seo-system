<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<div class="bg-white shadow rounded-lg p-6">
  <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
    <i class="fas fa-building text-blue-600"></i> Daftar Vendor
  </h2>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="bg-green-100 text-green-700 px-4 py-2 rounded mb-4">
      <?= session()->getFlashdata('success') ?>
    </div>
  <?php elseif (session()->getFlashdata('error')): ?>
    <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4">
      <?= session()->getFlashdata('error') ?>
    </div>
  <?php endif; ?>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm border">
      <thead class="bg-blue-600 text-white uppercase">
        <tr>
          <th class="px-4 py-2 text-center border">No</th>
          <th class="px-4 py-2 text-left border">Nama Usaha</th>
          <th class="px-4 py-2 text-left border">Pemilik</th>
          <th class="px-4 py-2 text-left border">Kontak</th>
          <th class="px-4 py-2 text-center border">Komisi Diminta</th>
          <th class="px-4 py-2 text-center border">Status</th>
          <th class="px-4 py-2 text-center border">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($vendors)): ?>
          <?php $no = 1; foreach ($vendors as $vendor): ?>
            <tr class="border-t hover:bg-blue-50">
              <td class="px-4 py-2 border text-center"><?= $no++ ?></td>
              <td class="px-4 py-2 border"><?= esc($vendor['business_name']) ?></td>
              <td class="px-4 py-2 border"><?= esc($vendor['owner_name']) ?></td>
              <td class="px-4 py-2 border">
                <?= esc($vendor['phone']) ?><br>
                <?php if (!empty($vendor['whatsapp_number'])): ?>
                  <span class="text-green-600">
                    <i class="fab fa-whatsapp"></i> <?= esc($vendor['whatsapp_number']) ?>
                  </span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2 border text-center">
                <?= $vendor['requested_commission'] ? esc($vendor['requested_commission']).'%' : '-' ?>
              </td>
              <td class="px-4 py-2 border text-center">
                <?php if ($vendor['approved_by_seo']): ?>
                  <span class="px-2 py-1 bg-green-100 text-green-700 rounded">Disetujui SEO</span>
                <?php else: ?>
                  <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded">Menunggu</span>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2 border text-center">
                <?php if (empty($vendor['approved_by_seo'])): ?>
                  <a href="<?= site_url('seo/vendor/approve/'.$vendor['id']) ?>"
                    class="px-3 py-1 rounded bg-green-600 text-white hover:bg-green-700"
                    onclick="return confirm('Yakin menyetujui vendor ini?')">
                    Approve Vendor
                  </a>
                <?php else: ?>
                  <span class="px-3 py-1 rounded bg-gray-300 text-gray-700">Sudah Disetujui</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" class="px-4 py-6 text-center text-gray-500">
              Belum ada data vendor.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
