<?php
$session = session();

$errorsArr   = $session->getFlashdata('errors') ?? $session->getFlashdata('errors_password') ?? [];
$successMsg  = $session->getFlashdata('success') ?? $session->getFlashdata('success_password');
$errorMsg    = $session->getFlashdata('error')   ?? $session->getFlashdata('error_password');
$firstError  = is_array($errorsArr) && ! empty($errorsArr) ? reset($errorsArr) : null;
?>

<div x-show="$store.ui.modal==='passwordEdit'" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.away="$store.ui.modal=null">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">Ubah Password</h3>
      <button class="text-gray-500 hover:text-gray-700 transition-colors" @click="$store.ui.modal=null">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>

    <form action="<?= site_url('vendoruser/password/update'); ?>" method="post" class="p-6 space-y-4">
      <?= csrf_field() ?>

      <?php if (!empty($errorMsg)): ?>
        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
          <div class="flex items-start"><i class="fas fa-exclamation-circle mr-2 mt-0.5"></i><span><?= esc($errorMsg) ?></span></div>
        </div>
      <?php endif; ?>
      <?php if (!empty($errorsArr)): ?>
        <div class="p-3 bg-red-50 text-red-700 rounded-lg text-sm border border-red-200">
          <?php foreach ($errorsArr as $msg): ?>
            <div class="flex items-start"><i class="fas fa-exclamation-circle mr-2 mt-0.5"></i><span><?= esc($msg) ?></span></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <?php if (!empty($successMsg)): ?>
        <div class="p-3 bg-green-50 text-green-700 rounded-lg text-sm border border-green-200">
          <div class="flex items-start"><i class="fas fa-check-circle mr-2 mt-0.5"></i><span><?= esc($successMsg) ?></span></div>
        </div>
      <?php endif; ?>

      <div x-data="{ show: false }" class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">Password Sekarang</label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="current_password" required autocomplete="current-password"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
          <button type="button" @click="show = !show"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
      </div>

      <div x-data="{ show: false }" class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">Password Baru</label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="new_password" required minlength="8" autocomplete="new-password" aria-describedby="pwHelp"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
          <button type="button" @click="show = !show"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
        <p id="pwHelp" class="text-xs text-gray-500 mt-1">Minimal 8 karakter. Disarankan gabungkan huruf & angka/simbol.</p>
      </div>

      <div x-data="{ show: false }" class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">Konfirmasi Password</label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" name="pass_confirm" required minlength="8" autocomplete="new-password"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
          <button type="button" @click="show = !show"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
      </div>

      <div class="pt-2">
        <button class="px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
          Simpan Password
        </button>
        <button type="button" class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 ml-2 transition-colors"
                @click="$store.ui.modal=null">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
// munculkan toast otomatis dari flashdata
document.addEventListener('alpine:init', () => {
  <?php if (!empty($successMsg)): ?>
    setTimeout(()=>Alpine.store('toast')?.show(<?= json_encode($successMsg, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>,'success'),0);
  <?php endif; ?>
  <?php if (!empty($firstError)): ?>
    setTimeout(()=>Alpine.store('toast')?.show(<?= json_encode($firstError, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>,'error'),0);
  <?php elseif (!empty($errorMsg)): ?>
    setTimeout(()=>Alpine.store('toast')?.show(<?= json_encode($errorMsg, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>,'error'),0);
  <?php endif; ?>
});
</script>
