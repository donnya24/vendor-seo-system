<?php
  $currentPage = isset($content_data['currentPage']) ? max(1, (int)$content_data['currentPage']) : 1;
  $perPage = isset($content_data['perPage']) ? (int)$content_data['perPage'] : 20;
  $totalLogs = isset($content_data['totalLogs']) ? (int)$content_data['totalLogs'] : 0;
  $totalPages = isset($content_data['totalPages']) ? max(1, (int)$content_data['totalPages']) : 1;
  $logs = isset($content_data['logs']) ? $content_data['logs'] : [];
  $offset = ($currentPage - 1) * $perPage;
  $startNo = $offset + 1;
?>

<main class="app-main flex-1 p-4 bg-gray-50">
  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="text-xl font-semibold mb-4 text-center">Riwayat Aktivitas</h2>

    <!-- Info Summary -->
    <div class="mb-4 p-3 bg-blue-50 rounded-lg">
      <div class="flex flex-wrap items-center justify-between text-sm">
        <div class="flex items-center space-x-4">
          <span class="text-blue-700 font-medium">Total Aktivitas: <?= number_format($totalLogs) ?></span>
          <span class="text-gray-600">Halaman: <?= $currentPage ?> dari <?= $totalPages ?></span>
        </div>
        <?php if($totalPages > 1): ?>
        <div class="flex items-center space-x-2">
          <!-- Pagination Controls -->
          <?php if($currentPage > 1): ?>
            <a href="?page=<?= $currentPage - 1 ?>" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
              &laquo; Sebelumnya
            </a>
          <?php endif; ?>
          
          <?php 
          // Tampilkan pagination numbers
          $startPage = max(1, $currentPage - 2);
          $endPage = min($totalPages, $currentPage + 2);
          
          for($p = $startPage; $p <= $endPage; $p++): 
          ?>
            <a href="?page=<?= $p ?>" class="px-3 py-1 rounded text-sm <?= $p == $currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' ?>">
              <?= $p ?>
            </a>
          <?php endfor; ?>
          
          <?php if($currentPage < $totalPages): ?>
            <a href="?page=<?= $currentPage + 1 ?>" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
              Berikutnya &raquo;
            </a>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Wrapper agar tabel bisa scroll -->
    <div class="overflow-x-auto">
      <div class="max-h-[70vh] overflow-y-auto border border-gray-200 rounded-lg">
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
                  <td class="p-3 text-center">
                    <span class="inline-block px-2 py-1 rounded-full text-xs font-medium <?= $log['badge_class'] ?? 'bg-gray-100 text-gray-800' ?>">
                      <?= esc($log['action_label'] ?? $log['action']); ?>
                    </span>
                  </td>
                  <td class="p-3 text-center"><?= esc($log['module_label'] ?? $log['module']); ?></td>
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
                <td colspan="6" class="p-8 text-center text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <p class="text-lg font-medium text-gray-500 mb-2">Belum ada aktivitas</p>
                    <p class="text-sm text-gray-400">Riwayat aktivitas Anda akan muncul di sini</p>
                  </div>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Info pagination bottom -->
    <?php if($totalPages > 1): ?>
    <div class="mt-4 flex items-center justify-between text-sm text-gray-600">
      <div>
        Menampilkan <?= count($logs) ?> dari <?= number_format($totalLogs) ?> aktivitas
      </div>
      <div class="flex items-center space-x-2">
        <?php if($currentPage > 1): ?>
          <a href="?page=1" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
            &laquo; First
          </a>
        <?php endif; ?>
        
        <?php if($currentPage > 1): ?>
          <a href="?page=<?= $currentPage - 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
            &lsaquo; Prev
          </a>
        <?php endif; ?>
        
        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded">
          Page <?= $currentPage ?> of <?= $totalPages ?>
        </span>
        
        <?php if($currentPage < $totalPages): ?>
          <a href="?page=<?= $currentPage + 1 ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
            Next &rsaquo;
          </a>
        <?php endif; ?>
        
        <?php if($currentPage < $totalPages): ?>
          <a href="?page=<?= $totalPages ?>" class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">
            Last &raquo;
          </a>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- Catatan tambahan -->
    <div class="mt-6 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
      <div class="flex items-start">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
        </svg>
        <div>
          <p class="text-sm font-medium text-yellow-800">Catatan</p>
          <p class="text-sm text-yellow-700 mt-1">Riwayat ini menampilkan semua aktivitas vendor sejak akun dibuat. Data disimpan secara otomatis untuk keperluan audit dan keamanan.</p>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- Tambahkan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.viewDescBtn').forEach(btn => {
      btn.addEventListener('click', () => {
        Swal.fire({
          title: 'Detail Deskripsi',
          html: `<div class="desc-popup text-left">${btn.dataset.desc}</div>`,
          width: 600,
          confirmButtonText: 'Tutup',
          customClass: { 
            popup: 'swal-custom-popup',
            confirmButton: 'swal-confirm-btn'
          }
        });
      });
    });

    // Auto-refresh setiap 30 detik jika di halaman pertama
    <?php if($currentPage === 1): ?>
    setTimeout(() => {
      window.location.reload();
    }, 30000);
    <?php endif; ?>
  });
</script>

<style>
  /* Ukuran popup lebih compact */
  .swal-custom-popup { 
    font-size: 0.85rem; 
    padding: 1rem; 
    border-radius: 0.5rem;
  }
  .swal-custom-popup .desc-popup {
    font-size: 0.8rem; 
    text-align: left; 
    word-wrap: break-word; 
    white-space: pre-wrap;
    line-height: 1.4;
    max-height: 400px;
    overflow-y: auto;
    padding: 0.5rem;
    background: #f8fafc;
    border-radius: 0.25rem;
  }
  .swal-confirm-btn {
    background-color: #2563eb !important;
    color: white !important;
    border-radius: 0.375rem !important;
    padding: 0.5rem 1rem !important;
    font-size: 0.875rem !important;
  }

  /* Scrollbar abu-abu lembut untuk wrapper tabel */
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

  /* Hover effects untuk pagination */
  .pagination-link:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
  }
</style>