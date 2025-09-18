<!-- MODAL NOTIFIKASI -->
<div x-show="notifModalOpen" x-cloak
     class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
     @click.self="notifModalOpen=false"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg"
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
              @click.prevent.stop="notifModalOpen=false"
              class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-96 overflow-y-auto divide-y">
      <?php if (empty($modalNotifications)): ?>
        <div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
      <?php else: ?>
        <?php foreach ($modalNotifications as $n): ?>
          <div class="p-4 flex justify-between items-start hover:bg-gray-50">
            <div class="min-w-0 pr-3">
              <p class="text-sm font-semibold text-gray-900 truncate">
                <?= esc($n['title'] ?? 'Notifikasi') ?>
                <?php if (empty($n['is_read'])): ?>
                  <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>
                <?php endif; ?>
              </p>
              <p class="text-xs text-gray-600 mt-0.5 break-words"><?= esc($n['message'] ?? '') ?></p>
              <p class="text-[11px] text-gray-400 mt-1"><?= esc($n['date'] ?? ($n['created_at'] ?? '')) ?></p>
            </div>
            <div class="flex flex-col gap-1 ml-2 shrink-0">
              <?php if (empty($n['is_read'])): ?>
                <form method="post" action="<?= site_url('seo/notifications/mark-read/'.(int)($n['id'] ?? 0)) ?>">
                  <?= csrf_field() ?>
                  <button type="submit" class="text-xs text-blue-600 hover:underline">Tandai Dibaca</button>
                </form>
              <?php endif; ?>
              <form method="post" action="<?= site_url('seo/notifications/delete/'.(int)($n['id'] ?? 0)) ?>"
                    onsubmit="return confirm('Sembunyikan notifikasi ini dari tampilan Anda?');">
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
      <form method="post" action="<?= site_url('seo/notifications/mark-all-read') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
          Tandai Semua Dibaca
        </button>
      </form>
      <form method="post" action="<?= site_url('seo/notifications/delete-all') ?>"
            onsubmit="return confirm('Sembunyikan semua notifikasi dari tampilan Anda?');">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700">
          Hapus Semua
        </button>
      </form>
    </div>
  </div>
</div>
