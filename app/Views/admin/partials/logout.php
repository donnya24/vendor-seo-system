<form id="logoutForm" action="<?= site_url('logout'); ?>" method="post" class="hidden">
  <?= csrf_field() ?>
</form>
<div x-show="modalOpen === 'logout'" x-transition.opacity class="fixed inset-0 z-50" role="dialog" aria-modal="true">
  <div class="min-h-screen flex items-center justify-center p-4">
    <div class="fixed inset-0 bg-black/40" @click="modalOpen = null"></div>
    <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl">
      <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
        <i class="fa-solid fa-right-from-bracket text-2xl"></i>
      </div>
      <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
      <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>
      <div class="mt-6 flex items-center justify-center gap-3">
        <button class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                @click="modalOpen = null">Batal</button>
        <button class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700"
                @click="document.getElementById('logoutForm')?.submit();">Ya, Keluar</button>
      </div>
    </div>
  </div>
</div>
