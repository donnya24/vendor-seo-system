<?= $this->include('vendoruser/layouts/header'); ?>

<?php $hasAreas = !empty($vendorAreas ?? []); ?>

<main class="app-main flex-1 p-4 bg-gray-50">
  <!-- Toolbar -->
  <div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-semibold">Area Layanan</h2>

    <div class="flex items-center gap-2">
      <?php if ($hasAreas): ?>
        <!-- Sudah ada area: Tambah nonaktif -->
        <button type="button"
                class="px-4 py-2 rounded-lg bg-gray-300 text-white opacity-60 cursor-not-allowed"
                title="Anda sudah menambahkan area. Gunakan Edit Area untuk mengubah."
                disabled>
          + Tambah Area
        </button>
      <?php else: ?>
        <!-- Belum ada area: Tambah aktif -->
        <a href="<?= site_url('vendoruser/areas/create?modal=1') ?>"
           onclick="return openAreasPopup(this.href)"
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
          + Tambah Area
        </a>
      <?php endif; ?>

      <?php if ($hasAreas): ?>
        <!-- Sudah ada area: Edit aktif -->
        <a href="<?= site_url('vendoruser/areas/edit?modal=1') ?>"
           onclick="return openAreasPopup(this.href)"
           class="px-4 py-2 bg-white border border-blue-600 text-blue-700 rounded-lg hover:bg-blue-50">
          Edit Area
        </a>
      <?php else: ?>
        <!-- Belum ada area: Edit nonaktif -->
        <button type="button"
                class="px-4 py-2 bg-white border border-gray-300 text-gray-400 rounded-lg opacity-60 cursor-not-allowed"
                title="Belum ada area untuk diedit"
                disabled>
          Edit Area
        </button>
      <?php endif; ?>
    </div>
  </div>

  <!-- Ringkasan area vendor -->
  <div class="mb-4">
    <div class="border border-gray-200 rounded-lg bg-white p-3">
      <?php if ($hasAreas): ?>
        <div class="flex flex-wrap gap-2">
          <?php foreach ($vendorAreas as $va): ?>
            <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full
                         bg-blue-50 border border-blue-200 text-blue-800 text-sm"
                  title="<?= esc($va['name']) ?>">
              <?= esc($va['name']) ?>
            </span>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <span class="text-sm text-gray-500">Belum ada area yang dipilih.</span>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Popup overlay -->
<div id="areasPopup" class="fixed inset-0 z-50 hidden">
  <div class="absolute inset-0 bg-black/60" onclick="closeAreasPopup()" aria-hidden="true"></div>

  <div class="relative z-10 w-full h-full flex items-center justify-center p-4">
    <div class="modal-card w-full max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh]">
      <div class="px-4 py-3 border-b flex items-center justify-between">
        <h3 class="font-semibold text-gray-800 text-sm md:text-base">Area Layanan</h3>
        <button type="button" onclick="closeAreasPopup()" class="text-gray-500 hover:text-gray-800 text-xl" aria-label="Tutup">&times;</button>
      </div>

      <div id="areasPopupBody" class="flex-1 overflow-auto p-4">
        <div class="h-40 flex items-center justify-center">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<style>
@keyframes spin{to{transform:rotate(360deg)}}
.animate-spin{animation:spin 1s linear infinite}
[x-cloak]{display:none!important}
</style>

<script>
// ====== SweetAlert mini helper (AJAX only; flash handled globally in footer) ======
const swalMini = {
  popup: 'rounded-md p-3 text-sm shadow',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-xs'
};

function showMini(status, message, redirect=null){
  const fire = () => {
    const icon  = status === 'success' ? 'success' : (status === 'error' ? 'error' : 'info');
    const timer = status === 'success' ? 1600 : undefined;
    Swal.fire({
      icon,
      title: status === 'success' ? 'Berhasil' : 'Info',
      text: message || '',
      timer,
      showConfirmButton: !timer,
      width: 300,
      customClass: swalMini
    }).then(() => { if (redirect) window.location.href = redirect; });
  };

  if (window.Swal) return fire();
  // Auto-load SweetAlert2 jika belum tersedia
  const s = document.createElement('script');
  s.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
  s.onload = fire;
  document.head.appendChild(s);
}

// ====== Modal open/close & AJAX loader ======
function openAreasPopup(url){
  const modal = document.getElementById('areasPopup');
  const body  = document.getElementById('areasPopupBody');
  if(!modal || !body) return true;

  body.innerHTML = '<div class="h-40 flex items-center justify-center"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
  modal.classList.remove('hidden');

  // Lock scroll
  const prevOverflow = document.documentElement.style.overflow;
  document.documentElement.dataset.prevOverflow = prevOverflow || '';
  document.documentElement.style.overflow = 'hidden';

  fetch(url, { headers: { 'X-Requested-With':'XMLHttpRequest' } })
    .then(r => {
      const ct = r.headers.get('content-type') || '';
      if (ct.includes('application/json')) return r.json().then(data => ({kind:'json', data}));
      return r.text().then(data => ({kind:'html', data}));
    })
    .then(result => {
      if (result.kind === 'json') {
        // Controller mengembalikan {status, message, redirect?}
        closeAreasPopup();
        showMini(result.data.status, result.data.message, result.data.redirect || null);
        return;
      }

      // Inject HTML
      body.innerHTML = result.data;

      // Eksekusi <script> dalam fragment (kecuali Alpine yg sudah ada)
      const scripts = body.querySelectorAll('script');
      scripts.forEach(old => {
        if (old.src && /alpinejs/i.test(old.src) && window.Alpine) return;
        const s = document.createElement('script');
        if (old.src) { s.src = old.src; s.defer = !!old.defer; s.async = !!old.async; }
        else { s.textContent = old.textContent; }
        document.head.appendChild(s).parentNode.removeChild(s);
      });

      // Re-init Alpine jika ada
      if (window.Alpine?.initTree) window.Alpine.initTree(body);
    })
    .catch(err => {
      body.innerHTML = `
        <div class="p-6 text-center">
          <div class="text-red-600 font-semibold mb-2">Gagal memuat formulir</div>
          <div class="text-gray-600 text-sm mb-4">${(err && err.message) ? err.message : 'Koneksi gagal'}</div>
          <button type="button" onclick="closeAreasPopup()" class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300">Tutup</button>
        </div>`;
    });

  return false; // cegah <a> lanjut navigasi
}

function closeAreasPopup(){
  const modal = document.getElementById('areasPopup');
  if(!modal) return;
  modal.classList.add('hidden');

  // Restore scroll
  const prev = document.documentElement.dataset.prevOverflow ?? '';
  document.documentElement.style.overflow = prev;
  delete document.documentElement.dataset.prevOverflow;
}

// Tutup dengan ESC dan klik backdrop
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    const modal = document.getElementById('areasPopup');
    if (modal && !modal.classList.contains('hidden')) closeAreasPopup();
  }
});
</script>

<?= $this->include('vendoruser/layouts/footer'); ?>
