<!-- Logout Modal -->
<div x-data="{ open: false }">
  <button @click="open = true" class="hidden" id="logoutTrigger"></button>
  <div x-show="open" x-cloak
       class="fixed inset-0 flex items-center justify-center bg-black/50 z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-sm p-6"
         @click.outside="open=false"
         x-transition>
      <h2 class="text-lg font-semibold mb-4">Konfirmasi Logout</h2>
      <p class="text-sm text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari aplikasi?</p>
      <div class="flex justify-end gap-3">
        <button @click="open=false" class="px-3 py-1 rounded bg-gray-200 text-gray-700">Batal</button>
        <form method="post" action="<?= site_url('logout') ?>">
          <?= csrf_field() ?>
          <button type="submit" class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700">Logout</button>
        </form>
      </div>
    </div>
  </div>
</div>
