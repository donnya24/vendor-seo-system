<?php
// Force no cache
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$session = session();

// FLASHDATA
$errorsArr   = $session->getFlashdata('errors') ?? $session->getFlashdata('errors_password') ?? [];
$successMsg  = $session->getFlashdata('success') ?? $session->getFlashdata('success_password');
$errorMsg    = $session->getFlashdata('error')   ?? $session->getFlashdata('error_password');

// Data dari controller - DENGAN FALLBACK KE GLOBAL
$hasPassword = $content_data['hasPassword'] ?? true;
$debugData = $content_data['debug_controller'] ?? $content_data ?? [];
$source = $debugData['source'] ?? 'unknown';

// Cache busting
$cacheBuster = time();
?>

<!-- DEBUG INFO - TAMPILKAN DI LAYAR UNTUK TESTING -->
<div style="background: #ffeb3b; padding: 10px; margin: 10px; border: 2px solid #ff9800; border-radius: 5px;">
    <strong>🚨 DEBUG INFO (HAPUS SETELAH FIX):</strong><br>
    hasPassword: <strong><?= $hasPassword ? 'YES' : 'NO' ?></strong><br>
    Source: <?= $source ?><br>
    User ID: <?= $debugData['user_id'] ?? 'UNKNOWN' ?><br>
    Secret2: <?= $debugData['secret2'] ?? 'UNKNOWN' ?><br>
    Cache Buster: <?= $cacheBuster ?>
</div>

<script>
console.log('=== PASSWORD MODAL DEBUG ===');
console.log('hasPassword:', <?= $hasPassword ? 'true' : 'false' ?>);
console.log('Source:', '<?= $source ?>');
console.log('Cache buster:', <?= $cacheBuster ?>);
</script>

<div x-show="$store.ui.modal==='passwordEdit'" x-cloak
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/50 p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md" @click.away="$store.ui.modal=null">
    <div class="px-6 py-4 border-b flex items-center justify-between">
      <h3 class="text-lg font-semibold">
        <?= $hasPassword ? 'Ubah Password' : 'Atur Password Pertama' ?>
      </h3>
      <button class="text-gray-500 hover:text-gray-700 transition-colors" @click="$store.ui.modal=null">
        <i class="fas fa-times text-lg"></i>
      </button>
    </div>

    <form id="passwordForm" action="<?= site_url('vendoruser/password/update'); ?>" method="post" class="p-6 space-y-4">
      <?= csrf_field() ?>

      <!-- Flash Messages -->
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

      <!-- Informasi untuk User Tanpa Password -->
      <?php if (!$hasPassword): ?>
        <div class="p-3 bg-blue-50 text-blue-700 rounded-lg text-sm border border-blue-200">
          <div class="flex items-start">
            <i class="fas fa-info-circle mr-2 mt-0.5 flex-shrink-0"></i>
            <div>
              <p class="font-semibold">Atur Password Pertama Kali</p>
              <p class="mt-1 text-sm">Silakan atur password untuk akun Anda. Setelah ini, Anda bisa login dengan email dan password.</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Password Saat Ini - HANYA TAMPIL JIKA USER SUDAH PERNAH SET PASSWORD -->
      <?php if ($hasPassword): ?>
        <div x-data="{ show: false }" class="relative">
          <label class="block text-sm font-semibold mb-1 text-gray-700">
            Password Sekarang
          </label>
          <div class="relative">
            <input :type="show ? 'text' : 'password'" 
                   name="current_password" 
                   required 
                   placeholder="Masukkan password saat ini"
                   class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors"
                   autocomplete="current-password">
            <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
              <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
            </button>
          </div>
        </div>
      <?php else: ?>
        <!-- Field password saat ini TIDAK DITAMPILKAN untuk user tanpa password -->
        <input type="hidden" name="current_password" value="">
      <?php endif; ?>

      <!-- Password Baru -->
      <div x-data="{ show: false }" class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">
          Password Baru
        </label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" 
                 name="new_password" 
                 required 
                 minlength="8" 
                 autocomplete="new-password"
                 placeholder="<?= $hasPassword ? 'Masukkan password baru' : 'Buat password baru untuk akun Anda' ?>"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
          <button type="button" @click="show = !show"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
        <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter. Disarankan gabungkan huruf, angka, dan simbol.</p>
      </div>

      <!-- Konfirmasi Password -->
      <div x-data="{ show: false }" class="relative">
        <label class="block text-sm font-semibold mb-1 text-gray-700">
          Konfirmasi Password
        </label>
        <div class="relative">
          <input :type="show ? 'text' : 'password'" 
                 name="pass_confirm" 
                 required 
                 minlength="8" 
                 autocomplete="new-password"
                 placeholder="Ketik ulang password baru"
                 class="w-full border rounded-lg px-3 py-2.5 pr-10 focus:ring-2 focus:ring-blue-200 focus:border-blue-600 transition-colors">
          <button type="button" @click="show = !show"
                  class="absolute inset-y-0 right-0 flex items-center justify-center w-10 text-gray-400 hover:text-gray-600 transition-colors">
            <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-sm"></i>
          </button>
        </div>
      </div>

      <div class="pt-2">
        <button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
          <?= $hasPassword ? 'Simpan Password' : 'Atur Password' ?>
        </button>
        <button type="button" class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 ml-2 transition-colors"
                @click="$store.ui.modal=null">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- ... script tetap sama ... -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('passwordForm');
    if (!form) return;

    console.log('Password Form Loaded - hasPassword:', <?= $hasPassword ? 'true' : 'false' ?>);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = form.querySelector('button[type="submit"]');
        const originalText = btn?.textContent;
        
        // Show loading state
        if (btn) {
            btn.disabled = true;
            btn.classList.add('opacity-60', 'cursor-not-allowed');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        }

        // Prepare FormData
        const fd = new FormData(form);

        // CSRF handling
        const csrfName = (window.CSRF && window.CSRF.tokenName) || '<?= csrf_token() ?>';
        const csrfHash = document.cookie.match(new RegExp('(?:^|;\\s*)' + (window.CSRF?.cookieName || 'csrf_cookie_name').replace(/[-[\]{}()*+?.,\\^$|#\\s]/g,'\\$&') + '=([^;]*)'))?.[1];
        if (csrfHash) fd.set(csrfName, csrfHash);

        const headers = { 'X-Requested-With': 'XMLHttpRequest' };
        const hName = window.CSRF?.headerName;
        if (csrfHash && hName) headers[hName] = csrfHash;

        try {
            const res = await fetch(form.action, { method: 'POST', body: fd, headers });
            const data = await res.json().catch(() => ({}));

            // Update CSRF token if provided
            if (data?.csrf) {
                document.querySelectorAll(`input[name="${csrfName}"]`).forEach(i => i.value = data.csrf);
                const meta = document.querySelector('meta[name="csrf-token"]');
                if (meta) meta.setAttribute('content', data.csrf);
            }

            if (res.ok && data?.status === 'success') {
                await Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message || 'Password berhasil diperbarui.',
                    confirmButtonText: 'OK'
                });
                
                // Close modal and reset form
                try { 
                    window.Alpine?.store('ui').modal = null; 
                } catch(e){}
                form.reset();
                
                // Force page reload untuk update status
                setTimeout(() => {
                    window.location.reload(true); // force reload dari server
                }, 1000);
                
            } else {
                let errorMessage = data?.message || 'Gagal memperbarui password.';
                if (data?.errors && typeof data.errors === 'object') {
                    errorMessage = Object.values(data.errors).join('<br>');
                }
                
                await Swal.fire({ 
                    icon: 'error', 
                    title: 'Gagal', 
                    html: errorMessage 
                });
            }
        } catch (err) {
            await Swal.fire({ 
                icon: 'error', 
                title: 'Gagal', 
                text: 'Tidak dapat menghubungi server. Coba lagi.' 
            });
        } finally {
            // Restore button state
            if (btn) {
                btn.disabled = false;
                btn.classList.remove('opacity-60', 'cursor-not-allowed');
                btn.textContent = originalText;
            }
        }
    });
});
</script>