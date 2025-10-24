<?php
$__stats        = $stats        ?? ['unread'=>0];
$__recentLeads  = $recentLeads  ?? [];
$__topKeywords  = $topKeywords  ?? [];
?>
<script>
// ==== HAPUS ALPINE STORE INIT DARI FOOTER ====
// Biarkan inisialisasi hanya di header saja untuk menghindari overwrite state

// tandai notifikasi dibaca (AJAX)
function markNotifAsRead(){
  fetch("<?= site_url('vendoruser/notifications/mark-all') ?>", {
    method: "GET",
    headers: {"X-Requested-With": "XMLHttpRequest"}
  }).then(res => {
    if(res.ok){
      const b = document.getElementById("notifBadge");
      if(b) b.remove();
    }
  }).catch(()=>{});
}
</script>

<script>
  // SweetAlert2 toast helper
  window.swalToast = (icon, title) => {
    if (!window.Swal || !title) return;
    const Toast = Swal.mixin({
      toast: true, position: 'top-end', showConfirmButton: false,
      timer: 2800, timerProgressBar: true,
      customClass: { popup: 'rounded-md text-sm shadow' }
    });
    Toast.fire({ icon, title });
  };
</script>

<!-- Sinkronisasi CSRF + cegah double submit -->
<script>
  window.CSRF = {
    tokenName: '<?= csrf_token() ?>',
    headerName: '<?= csrf_header() ?>',
    cookieName: '<?= config('Security')->cookieName ?>' || 'csrf_cookie_name'
  };
  
  function getCsrfFromCookie() {
    const n = window.CSRF.cookieName;
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + n.replace(/[-[\]{}()*+?.,\\^$|#\s]/g,'\\$&') + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  }
  
  function refreshAllCsrfFields() {
    const hash = getCsrfFromCookie();
    if (!hash) return;
    document.querySelectorAll('input[name="'+window.CSRF.tokenName+'"]').forEach(i => i.value = hash);
    const meta = document.querySelector('meta[name="csrf-token"]'); 
    if (meta) meta.setAttribute('content', hash);
  }
  
  document.addEventListener('submit', function(e){
    try { 
      refreshAllCsrfFields(); 
    } catch(_) {}
    
    const form = e.target;
    const btn = form.querySelector('button[type="submit"],input[type="submit"]');
    if (btn && !btn.dataset.once) {
      btn.dataset.once = '1';
      btn.disabled = true;
      btn.classList.add('opacity-60','cursor-not-allowed');
    }
  }, true);
</script>

<!-- Konfirmasi SweetAlert2 hapus notif -->
<script>
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[onsubmit*="confirm("]')?.forEach(f => f.removeAttribute('onsubmit'));
  });
  
  document.addEventListener('submit', function (e) {
    const form = e.target;

    if (form.classList.contains('js-notif-delete')) {
      e.preventDefault(); 
      Swal.fire({
        icon: 'warning',
        title: 'Apakah yakin ingin menghapus?',
        text: 'Notifikasi ini akan dihapus dari akun Anda.',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-md p-4',
          confirmButton: 'bg-red-600 text-white px-3 py-2 rounded mr-2',
          cancelButton: 'bg-gray-100 text-gray-700 px-3 py-2 rounded'
        }
      }).then((r) => { 
        if (r.isConfirmed) form.submit(); 
      });
    }

    if (form.classList.contains('js-notif-delete-all')) {
      e.preventDefault(); 
      Swal.fire({
        icon: 'warning',
        title: 'Hapus semua notifikasi?',
        text: 'Semua notifikasi Anda akan dibersihkan dari akun.',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus semua',
        cancelButtonText: 'Batal',
        customClass: {
          popup: 'rounded-md p-4',
          confirmButton: 'bg-red-600 text-white px-3 py-2 rounded mr-2',
          cancelButton: 'bg-gray-100 text-gray-700 px-3 py-2 rounded'
        }
      }).then((r) => { 
        if (r.isConfirmed) form.submit(); 
      });
    }
  }, true);
</script>

</div> <!-- /flex-1 -->
</div> <!-- /flex container -->

<style>
  [x-cloak]{display:none!important}
  @media (max-width: 767px){ 
    .max-w-\[90vw\]{max-width:90vw} 
  }
</style>

<!-- Global Logout Modal -->
<div x-show="$store.ui.modal==='logout'" x-cloak
     @keydown.escape.window="$store.ui.modal=null"
     x-transition.opacity
     class="fixed inset-0 z-[120] flex items-center justify-center p-4">
  <div class="fixed inset-0 bg-black/50" @click="$store.ui.modal=null"></div>
  <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl">
    <div class="w-14 h-14 mx-auto rounded-full bg-red-50 text-red-600 flex items-center justify-center">
      <i class="fa-solid fa-right-from-bracket text-2xl"></i>
    </div>
    <h3 class="mt-4 text-center text-xl font-semibold text-gray-900">Keluar dari Sistem?</h3>
    <p class="mt-2 text-center text-sm text-gray-500">Anda akan keluar dari sesi saat ini.</p>
    <div class="mt-6 flex items-center justify-center gap-3">
      <button type="button"
              class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
              @click="$store.ui.modal=null">Batal</button>
      <form action="<?= site_url('logout') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit" class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">
          Ya, Keluar
        </button>
      </form>
    </div>
  </div>
</div>

</body>
</html>