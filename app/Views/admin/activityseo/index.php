<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<?php
  $currentPage = isset($page) ? max(1, (int)$page) : max(1, (int)($_GET['page'] ?? 1));
  $perPageGuess = isset($perPage) ? (int)$perPage : (is_array($logs ?? null) ? max(1, count($logs)) : 10);
  $offset = ($currentPage - 1) * $perPageGuess;
  $startNo = $offset + 1;
?>

<main class="app-main flex-1 p-4 bg-gray-50">
  <div class="bg-white rounded-2xl p-6 shadow">

    <h2 class="text-xl font-semibold mb-6 text-center">Riwayat Aktivitas SEO</h2>

    <!-- FILTER USER SEO -->
    <form method="get" class="mb-5 flex flex-col sm:flex-row gap-3 items-center justify-between">
      <div class="flex items-center gap-2">
        <label for="id" class="font-medium text-gray-700">Pilih User SEO:</label>
        <select id="id" name="id" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
          <option value="">-- Semua User --</option>
          <?php foreach($users as $u): ?>
            <option value="<?= esc($u['user_id']) ?>" <?= ($user_id == $u['user_id']) ? 'selected' : '' ?>>
              <?= esc($u['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow">
          Filter
        </button>
      </div>
    </form>

    <!-- WRAPPER TABEL -->
    <div class="overflow-x-auto">
      <div class="max-h-[70vh] overflow-y-auto border border-gray-200 rounded-lg">
        <table class="min-w-full text-sm">
          <thead class="bg-blue-600 text-white text-xs uppercase tracking-wide sticky top-0 z-10">
            <tr>
              <th class="p-3 text-center w-12">No</th>
              <th class="p-3 text-center">User SEO</th>
              <th class="p-3 text-center">Waktu</th>
              <th class="p-3 text-center">Aksi</th>
              <th class="p-3 text-center">Modul</th>
              <th class="p-3 text-center">Deskripsi</th>
              <th class="p-3 text-center">IP Address</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <?php if (!empty($logs)): ?> 
              <?php foreach ($logs as $i => $log): ?>
                <tr class="hover:bg-gray-50">
                  <td class="p-3 text-center font-medium"><?= $startNo + $i ?></td>
                  <td class="p-3 text-center"><?= esc($log['name'] ?? '-') ?></td>
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
                    <?php if (mb_strlen($plain) > 50): ?>
                      <button 
                        type="button"
                        class="text-blue-600 hover:underline ml-1 viewDescBtn"
                        data-desc="<?= htmlspecialchars($plain, ENT_QUOTES, 'UTF-8') ?>">
                        lihat…
                      </button>
                    <?php endif; ?>
                  </td>
                  <td class="p-3 text-center"><?= esc($log['ip_address']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="p-4 text-center text-gray-500">Belum ada aktivitas.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3 text-sm text-gray-500 text-left">
      Catatan: Riwayat ini menampilkan semua aktivitas user SEO, bisa difilter berdasarkan user tertentu.
    </div>
  </div>
</main>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
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
  });
</script>

<style>
  .swal-custom-popup { font-size: 0.85rem; padding: 1rem; }
  .swal-custom-popup .desc-popup {
    font-size: 0.8rem; text-align: left; word-wrap: break-word; white-space: pre-wrap;
  }
  .max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar { width: 8px; height: 8px; }
  .max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; }
</style>

<?= $this->include('admin/layouts/footer'); ?>
