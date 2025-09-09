<?php
$session = session();

// FLASHDATA (password)
$errorsArr   = $session->getFlashdata('errors') ?? $session->getFlashdata('errors_password') ?? [];
$successMsg  = $session->getFlashdata('success') ?? $session->getFlashdata('success_password');
$errorMsg    = $session->getFlashdata('error')   ?? $session->getFlashdata('error_password');
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

    <form id="passwordForm" action="<?= site_url('vendoruser/password/update'); ?>" method="post" class="p-6 space-y-4">
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
        <button type="submit" class="px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition-colors font-medium">
          Simpan Password
        </button>
        <button type="button" class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 ml-2 transition-colors"
                @click="$store.ui.modal=null">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
  // Helper ambil CSRF dari cookie (sudah disiapkan di footer window.CSRF)
  function getCsrfFromCookie() {
    const n = window.CSRF?.cookieName;
    if (!n) return null;
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n.replace(/[-[\]{}()*+?.,\\^$|#\\s]/g,'\\$&') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  }
  function setAllCsrf(hash) {
    if (!hash) return;
    document.querySelectorAll('input[name="'+(window.CSRF?.tokenName || '<?= csrf_token() ?>')+'"]').forEach(i => i.value = hash);
    const meta = document.querySelector('meta[name="csrf-token"]'); if (meta) meta.setAttribute('content', hash);
  }

  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('passwordForm');
    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();

      const btn = form.querySelector('button[type="submit"]');
      btn?.classList.add('opacity-60','cursor-not-allowed');
      if (btn) btn.disabled = true;

      // siapkan FormData + CSRF
      const fd = new FormData(form);
      const csrfName = (window.CSRF && window.CSRF.tokenName) || '<?= csrf_token() ?>';
      const csrfHash = getCsrfFromCookie() || fd.get(csrfName);
      if (csrfHash) fd.set(csrfName, csrfHash);

      const headers = { 'X-Requested-With': 'XMLHttpRequest' };
      const hName = window.CSRF?.headerName;
      if (csrfHash && hName) headers[hName] = csrfHash;

      try {
        const res = await fetch(form.action, { method: 'POST', body: fd, headers });
        const data = await res.json().catch(() => ({}));

        // update CSRF baru dari server
        if (data?.csrf) setAllCsrf(data.csrf);

        if (res.ok && data?.status === 'success') {
          await Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: data.message || 'Password berhasil diperbarui.',
            confirmButtonText: 'OK'
          });
          // tutup modal setelah OK
          try { window.Alpine?.store('ui').modal = null; } catch(e){}
          // reset form
          form.reset();
        } else {
          // kumpulkan pesan error
          let html = '';
          if (data?.errors && typeof data.errors === 'object') {
            html += '<ul style="text-align:left;margin:0;padding-left:1rem">';
            Object.values(data.errors).forEach(msg => { html += '<li>'+ String(msg) +'</li>'; });
            html += '</ul>';
          } else {
            html = data?.message || 'Gagal memperbarui password.';
          }
          await Swal.fire({ icon: 'error', title: 'Gagal', html });
        }
      } catch (err) {
        await Swal.fire({ icon: 'error', title: 'Gagal', text: 'Tidak dapat menghubungi server. Coba lagi.' });
      } finally {
        if (btn) { btn.disabled = false; btn.classList.remove('opacity-60','cursor-not-allowed'); }
      }
    });

    // Fallback: kalau datang dari non-AJAX (redirect/flashdata), tetap tampilkan popup
    const PASS_SUCCESS = <?= json_encode($successMsg ?? null, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
    const PASS_ERROR   = <?= json_encode($errorMsg   ?? null, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
    const PASS_ERRORS  = <?= json_encode(array_values($errorsArr ?? []), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;

    if (PASS_SUCCESS) Swal.fire({ icon:'success', title:'Berhasil', text: PASS_SUCCESS });
    if (PASS_ERROR)   Swal.fire({ icon:'error',   title:'Gagal',   text: PASS_ERROR });
    (PASS_ERRORS || []).forEach(msg => Swal.fire({ icon:'error', title:'Gagal', text: msg }));
  });
</script>
