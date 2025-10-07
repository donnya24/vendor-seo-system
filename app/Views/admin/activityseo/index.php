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

    <h2 class="text-xl font-semibold mb-6 text-center">Riwayat Aktivitas Tim SEO</h2>

    <!-- HEADER DENGAN FILTER DAN DELETE ALL -->
    <div class="mb-5 flex flex-col sm:flex-row gap-4 items-center justify-between">
      <!-- FILTER USER SEO -->
      <form method="get" class="flex items-center gap-3">
        <div class="flex items-center gap-2">
          <label for="id" class="font-medium text-gray-700">Filter User SEO:</label>
          <select id="id" name="id" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
            <option value="">-- Semua Tim SEO --</option>
            <?php foreach($users as $u): ?>
              <option value="<?= esc($u['user_id']) ?>" <?= ($user_id == $u['user_id']) ? 'selected' : '' ?>>
                <?= esc($u['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg shadow">
          Filter
        </button>
      </form>

      <!-- DELETE ALL BUTTON -->
      <?php if (!empty($logs)): ?>
        <form method="post" action="<?= site_url('admin/activities/seo/delete-all' . (!empty($user_id) ? '?id=' . $user_id : '')) ?>" 
              id="deleteAllForm">
          <?= csrf_field() ?>
          <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg shadow flex items-center gap-2">
            <i class="fas fa-trash-alt"></i>
            Hapus Semua Riwayat
          </button>
        </form>
      <?php endif; ?>
    </div>

    <!-- INFO STATS -->
    <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
      <div class="flex flex-wrap gap-4 text-sm text-blue-800">
        <div class="flex items-center gap-1">
          <i class="fas fa-users"></i>
          <span>Total User SEO: <?= count($users) ?></span>
        </div>
        <div class="flex items-center gap-1">
          <i class="fas fa-history"></i>
          <span>Total Aktivitas: <?= count($logs) ?></span>
        </div>
        <?php if (!empty($user_id)): ?>
          <div class="flex items-center gap-1">
            <i class="fas fa-filter"></i>
            <span>Filter aktif: User tertentu</span>
          </div>
        <?php endif; ?>
      </div>
    </div>

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
                  <td class="p-3 text-center">
                    <div class="font-medium"><?= esc($log['name'] ?? '-') ?></div>
                    <div class="text-xs text-gray-500">ID: <?= esc($log['user_id'] ?? '-') ?></div>
                  </td>
                  <td class="p-3 text-center"><?= esc($log['created_at']); ?></td>
                  <td class="p-3 text-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium 
                      <?= $log['action'] === 'create' ? 'bg-green-100 text-green-800' : '' ?>
                      <?= $log['action'] === 'update' ? 'bg-blue-100 text-blue-800' : '' ?>
                      <?= $log['action'] === 'delete' ? 'bg-red-100 text-red-800' : '' ?>
                      <?= $log['action'] === 'view' ? 'bg-gray-100 text-gray-800' : '' ?>
                      <?= !in_array($log['action'], ['create','update','delete','view']) ? 'bg-yellow-100 text-yellow-800' : '' ?>">
                      <?= esc($log['action']); ?>
                    </span>
                  </td>
                  <td class="p-3 text-center">
                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                      <?= esc($log['module']); ?>
                    </span>
                  </td>
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
                  <td class="p-3 text-center">
                    <code class="text-xs bg-gray-100 px-2 py-1 rounded"><?= esc($log['ip_address']); ?></code>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="7" class="p-4 text-center text-gray-500">
                  <div class="py-8">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                    <p class="text-lg">Belum ada aktivitas tim SEO.</p>
                    <?php if (!empty($user_id)): ?>
                      <p class="text-sm text-gray-500 mt-1">User ini belum memiliki riwayat aktivitas.</p>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3 text-sm text-gray-500 text-left">
      <i class="fas fa-info-circle mr-1"></i>
      Catatan: Riwayat ini menampilkan semua aktivitas <strong>Tim SEO</strong>, bisa difilter berdasarkan user tertentu.
    </div>
  </div>
</main>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete all form
    const deleteForm = document.querySelector('#deleteAllForm');
    if (deleteForm) {
        deleteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userFilter = '<?= !empty($user_id) ? " untuk user ini" : " semua tim SEO" ?>';
            
            Swal.fire({
                title: 'Hapus Semua Riwayat?',
                html: `Anda akan menghapus <strong>semua riwayat aktivitas${userFilter}</strong>.<br><br>Tindakan ini <strong>tidak dapat dibatalkan</strong>!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                width: 420,
                padding: '1rem',
                customClass: {
                    popup: 'small-swal-popup',
                    title: 'small-swal-title',
                    htmlContainer: 'small-swal-content',
                    confirmButton: 'small-swal-confirm',
                    cancelButton: 'small-swal-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form
                    this.submit();
                }
            });
        });
    }

    // View Description Button
    document.querySelectorAll('.viewDescBtn').forEach(btn => {
        btn.addEventListener('click', () => {
            Swal.fire({
                title: 'Detail Deskripsi',
                html: `<div class="desc-popup">${btn.dataset.desc}</div>`,
                width: 500,
                padding: '1rem',
                confirmButtonText: 'Tutup',
                customClass: { 
                    popup: 'small-swal-popup',
                    title: 'small-swal-title',
                    htmlContainer: 'small-swal-content desc-popup-container',
                    confirmButton: 'small-swal-confirm'
                }
            });
        });
    });
});
</script>

<style>
/* Style untuk SweetAlert yang lebih kecil */
.small-swal-popup {
    font-size: 0.875rem !important;
    border-radius: 12px !important;
}

.small-swal-title {
    font-size: 1.1rem !important;
    margin-bottom: 0.5rem !important;
    padding: 0 !important;
}

.small-swal-content {
    font-size: 0.8rem !important;
    line-height: 1.4 !important;
    padding: 0 !important;
}

.small-swal-confirm,
.small-swal-cancel {
    font-size: 0.8rem !important;
    padding: 0.5rem 1.2rem !important;
    margin: 0 0.3rem !important;
}

.desc-popup-container {
    max-height: 300px !important;
    overflow-y: auto !important;
}

.desc-popup {
    font-size: 0.8rem !important;
    text-align: left !important;
    word-wrap: break-word !important;
    white-space: pre-wrap !important;
    padding: 0.8rem !important;
    background: #f8f9fa !important;
    border-radius: 6px !important;
    border: 1px solid #e9ecef !important;
    line-height: 1.5 !important;
}

/* Scrollbar untuk popup deskripsi */
.desc-popup-container::-webkit-scrollbar { 
    width: 6px !important;
}

.desc-popup-container::-webkit-scrollbar-thumb { 
    background: #cbd5e1 !important;
    border-radius: 3px !important;
}

.desc-popup-container::-webkit-scrollbar-track { 
    background: #f1f5f9 !important;
}

/* Scrollbar untuk tabel */
.max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar { 
    width: 8px; 
    height: 8px; 
}
.max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar-thumb { 
    background: #cbd5e1; 
    border-radius: 9999px; 
}
.max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar-track { 
    background: #f1f5f9; 
}
</style>

<?= $this->include('admin/layouts/footer'); ?>