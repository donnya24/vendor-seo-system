<!-- 🔔 Modal Notifikasi -->
<div x-show="notifModal" x-cloak
     class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-lg mx-4">
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b">
      <h3 class="text-lg font-semibold text-gray-900">Semua Notifikasi</h3>
      <button @click="notifModal=false" class="text-gray-500 hover:text-gray-700">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="max-h-96 overflow-y-auto divide-y">
      <?php if (empty($notifications)): ?>
        <div class="p-4 text-center text-sm text-gray-500">Tidak ada notifikasi.</div>
      <?php else: foreach ($notifications as $n): ?>
        <div class="p-4 flex justify-between items-start hover:bg-gray-50">
          <div>
            <p class="text-sm font-semibold text-gray-900">
              <?= esc($n['title']) ?>
              <?php if (!$n['is_read']): ?>
                <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">Baru</span>
              <?php endif; ?>
            </p>
            <p class="text-xs text-gray-600"><?= esc($n['message']) ?></p>
            <p class="text-xs text-gray-400 mt-1"><?= esc($n['date']) ?></p>
          </div>

          <div class="flex flex-col gap-1 ml-2">
            <?php if (!$n['is_read']): ?>
              <form method="post" action="<?= site_url('vendoruser/notifications/'.$n['id'].'/read') ?>">
                <?= csrf_field() ?>
                <button class="text-xs text-blue-600 hover:underline" type="submit">Tandai Dibaca</button>
              </form>
            <?php endif; ?>

            <!-- Hapus satu notifikasi -->
            <form method="post"
                  action="<?= site_url('vendoruser/notifications/'.$n['id'].'/delete') ?>"
                  class="js-notif-delete">
              <?= csrf_field() ?>
              <button class="text-xs text-red-600 hover:underline" type="submit">Hapus</button>
            </form>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- Footer -->
    <div class="px-4 py-3 border-t flex justify-between items-center">
      <form method="post" action="<?= site_url('vendoruser/notifications/mark-all') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-blue-600 text-white text-sm hover:bg-blue-700">
          Tandai Semua Dibaca
        </button>
      </form>

      <!-- Hapus semua notifikasi -->
      <form method="post"
            action="<?= site_url('vendoruser/notifications/delete-all') ?>"
            class="js-notif-delete-all">
        <?= csrf_field() ?>
        <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white text-sm hover:bg-red-700">
          Hapus Semua
        </button>
      </form>
    </div>
  </div>
</div>
