<?php
// File: app/Views/admin/Notifications/modal.php
?>

<!-- 🔔 Modal Notifikasi -->
<div x-show="notifModal"
     x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
     @click.self="notifModal=false; $store.ui.modal=null"
     style="display:none;"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4"
       @click.stop
       x-transition:enter="transition ease-out duration-200"
       x-transition:enter-start="opacity-0 scale-95 translate-y-1"
       x-transition:enter-end="opacity-100 scale-100 translate-y-0"
       x-transition:leave="transition ease-in duration-150"
       x-transition:leave-start="opacity-100 scale-100 translate-y-0"
       x-transition:leave-end="opacity-0 scale-95 translate-y-1">

    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="text-lg font-semibold text-gray-900">Semua Notifikasi</h3>
      <button type="button"
              @click.prevent.stop="notifModal=false; $store.ui.modal=null"
              class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-96 overflow-y-auto divide-y">
      <?php if (empty($notifications)): ?>
        <div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
      <?php else: ?>
        <?php foreach ($notifications as $n): ?>
          <div class="p-4 flex justify-between items-center hover:bg-gray-50">
            <!-- Info Notifikasi -->
            <div class="flex-1 pr-4">
              <p class="text-sm font-semibold text-gray-900">
                <?= esc($n['title'] ?? '-') ?>
                <?php if (!($n['is_read'] ?? false)): ?>
                  <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>
                <?php endif; ?>
              </p>
              <p class="text-xs text-gray-600"><?= esc($n['message'] ?? '-') ?></p>
              
              <!-- PERBAIKAN: Handle missing 'date' field dengan fallback ke created_at -->
              <p class="text-xs text-gray-400 mt-1">
                <?= !empty($n['date']) ? esc($n['date']) : 
                    (!empty($n['created_at']) ? date('d M Y H:i', strtotime($n['created_at'])) : '-') ?>
              </p>
            </div>

            <!-- Aksi -->
            <div class="flex items-center gap-2 shrink-0">
              <?php if (!($n['is_read'] ?? false)): ?>
                <form method="post" action="<?= site_url('admin/notifications/markRead/'.($n['id'] ?? '')) ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-xs text-blue-600 hover:underline">Tandai Dibaca</button>
                </form>
              <?php endif; ?>
              <form method="post" action="<?= site_url('admin/notifications/delete/'.($n['id'] ?? '')) ?>"
                    onsubmit="return confirm('Hapus notifikasi ini?');">
                <?= csrf_field() ?>
                <button type="submit" class="text-xs text-red-600 hover:underline">Hapus</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 border-t flex justify-between items-center">
      <form method="post" action="<?= site_url('admin/notifications/markAllRead') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
          Tandai Semua Dibaca
        </button>
      </form>
      <form method="post" action="<?= site_url('admin/notifications/deleteAll') ?>"
            onsubmit="return confirm('Hapus semua notifikasi?');">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700">
          Hapus Semua
        </button>
      </form>
    </div>
  </div>
</div>