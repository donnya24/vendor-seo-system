<?= $this->extend('seo/layouts/seo_master') ?>
<?= $this->section('content') ?>

<?php
  // Hitung pagination manual supaya nomor berurutan
  $currentPage = isset($page) ? max(1, (int)$page) : max(1, (int)($_GET['page'] ?? 1));
  $perPageGuess = isset($perPage) ? (int)$perPage : (is_array($logs ?? null) ? max(1, count($logs)) : 10);
  $offset = ($currentPage - 1) * $perPageGuess;
  $startNo = $offset + 1;
?>

<h2 class="text-2xl font-bold mb-4 text-center">Log Aktivitas SEO</h2>

<div class="bg-white rounded-lg shadow p-6">
  <!-- Wrapper horizontal scroll -->
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-blue-600 text-white text-xs uppercase tracking-wide sticky top-0 z-10">
        <tr>
          <th class="p-3 text-center w-12">No</th>
          <th class="p-3 text-center">Waktu</th>
          <th class="p-3 text-center">Aksi</th>
          <th class="p-3 text-center">Module</th>
          <th class="p-3 text-center">Deskripsi</th>
          <th class="p-3 text-center">IP Address</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-100">
        <?php if(!empty($logs)): ?> 
          <?php foreach($logs as $i => $log): ?>
            <tr class="hover:bg-gray-50">
              <td class="p-3 text-center font-medium"><?= $startNo + $i ?></td>
              <td class="p-3 text-center"><?= esc($log['created_at']); ?></td>
              <td class="p-3 text-center"><?= esc($log['action']); ?></td>
              <td class="p-3 text-center"><?= esc($log['module']); ?></td>
              <td class="p-3 text-center">
                <?php 
                  $desc = $log['description'] ?? '';
                  $plain = strip_tags($desc);
                  $short = mb_strimwidth($plain, 0, 50, "…");
                ?>
                <?= esc($short); ?>
                <?php if(mb_strlen($plain) > 50): ?>
                  <button 
                    type="button"
                    class="text-blue-600 hover:underline ml-1 viewDescBtn"
                    data-desc="<?= esc($plain) ?>">
                    lihat…
                  </button>
                <?php endif; ?>
              </td>
              <td class="p-3 text-center"><?= esc($log['ip_address']); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="6" class="p-4 text-center text-gray-500">Belum ada aktivitas.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Catatan tambahan -->
  <div class="mt-3 text-sm text-gray-500 text-left">
    Catatan: Riwayat ini menampilkan semua aktivitas user SEO.
  </div>
</div>

<!-- Tambahkan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.querySelectorAll('.viewDescBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      Swal.fire({
        title: 'Detail Deskripsi',
        html: `<div class="desc-popup">${btn.dataset.desc}</div>`,
        width: 600,
        confirmButtonText: 'Tutup',
        customClass: { popup: 'swal-custom-popup' }
      });
    });
  });
</script>

<style>
  /* Ukuran popup lebih compact */
  .swal-custom-popup { font-size: 0.85rem; padding: 1rem; }
  .swal-custom-popup .desc-popup {
    font-size: 0.8rem; text-align: left; word-wrap: break-word; white-space: pre-wrap;
  }
</style>

<?= $this->endSection() ?>
