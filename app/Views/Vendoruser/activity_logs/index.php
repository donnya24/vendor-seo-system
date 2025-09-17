<?php
$currentPage = isset($page) ? max(1, (int)$page) : max(1, (int)($_GET['page'] ?? 1));
$perPageGuess = isset($perPage) ? (int)$perPage : (is_array($logs ?? null) ? max(1, count($logs)) : 10);
$offset = ($currentPage - 1) * $perPageGuess;
$startNo = $offset + 1;
?>

<main id="activityPage" class="app-main flex-1 p-4 bg-gray-50" x-data="activityLogs()" x-init="init()">

  <div class="bg-white rounded-2xl p-6 shadow">
    <h2 class="text-xl font-semibold mb-4 text-center">Riwayat Aktivitas</h2>

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
            <template x-for="(log, i) in logs" :key="log.id">
              <tr class="hover:bg-gray-50">
                <td class="p-3 text-center font-medium" x-text="startNo + i"></td>
                <td class="p-3 text-center" x-text="log.created_at"></td>
                <td class="p-3 text-center" x-text="log.action"></td>
                <td class="p-3 text-center" x-text="log.module"></td>
                <td class="p-3 text-center">
                  <span x-text="shorten(log.description)"></span>
                  <button 
                    type="button"
                    x-show="log.description && log.description.length > 50"
                    @click="openModal(log.description)"
                    class="text-blue-600 hover:underline ml-1">
                    lihat…
                  </button>
                </td>
                <td class="p-3 text-center" x-text="log.ip_address"></td>
              </tr>
            </template>
            <tr x-show="logs.length === 0">
              <td colspan="6" class="p-4 text-center text-gray-500">Belum ada aktivitas.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-3 text-sm text-gray-500 text-left">
      Catatan: Riwayat ini menampilkan semua aktivitas vendor sejak login terakhir.
    </div>
  </div>

  <!-- Modal Deskripsi -->
  <div x-data="{ open: false, desc: '' }"
       x-show="open"
       x-transition:enter="transition ease-out duration-300"
       x-transition:enter-start="opacity-0 translate-y-full"
       x-transition:enter-end="opacity-100 translate-y-0"
       x-transition:leave="transition ease-in duration-200"
       x-transition:leave-start="opacity-100 translate-y-0"
       x-transition:leave-end="opacity-0 translate-y-full"
       class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-2"
       style="display: none;">
    <div class="bg-white rounded-lg shadow-lg max-w-xl w-full p-4 md:p-6">
      <div class="flex justify-between items-center mb-3">
        <h3 class="text-lg font-semibold">Detail Deskripsi</h3>
        <button @click="open=false" class="text-gray-500 hover:text-gray-700 text-lg"><i class="fas fa-times"></i></button>
      </div>
      <div class="text-sm text-gray-700 whitespace-pre-wrap" x-text="desc"></div>
      <div class="mt-4 flex justify-end">
        <button @click="open=false" class="px-3 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-sm">Tutup</button>
      </div>
    </div>
  </div>

</main>

<script>
function activityLogs() {
  return {
    startNo: <?= $startNo ?>,
    logs: <?= json_encode($logs ?? []) ?>,
    init() {
      // SPA load listener atau fetch tambahan bisa ditaruh di sini
    },
    shorten(text) {
      if(!text) return '';
      let plain = text.replace(/<[^>]+>/g,'');
      return plain.length > 50 ? plain.substring(0,50) + '…' : plain;
    },
    openModal(desc) {
      let modal = document.querySelector('[x-data*="open: false"]');
      modal.__x.$data.desc = desc;
      modal.__x.$data.open = true;
      document.body.style.overflow = 'hidden';
      modal.addEventListener('transitionend', () => {
        if(!modal.__x.$data.open) document.body.style.overflow = 'auto';
      }, { once: true });
    }
  }
}
</script>

<style>
  /* Scrollbar lembut untuk wrapper tabel */
  .max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar { width: 8px; height: 8px; }
  .max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
  .max-h-\[70vh\].overflow-y-auto::-webkit-scrollbar-track { background: #f1f5f9; }
</style>
