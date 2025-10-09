<?= $this->include('admin/layouts/header'); ?>
<?= $this->include('admin/layouts/sidebar'); ?>

<main class="app-main flex-1 p-4 sm:p-6 lg:p-8 bg-gray-50">

  <!-- Wrapper untuk konsistensi lebar & spasi vertikal -->
  <div class="mx-auto w-full max-w-screen-2xl space-y-4">

    <!-- Toolbar -->
    <div class="bg-white ring-1 ring-gray-200 rounded-xl shadow-sm px-5 py-4 sm:px-6 sm:py-5">
      <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-800">Daftar Layanan & Produk</h2>

        <div class="flex gap-2">
          <button
            onclick="openModal('<?= site_url('admin/services/create') ?>')"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 transition shadow-sm text-sm"
          >
            <span class="hidden sm:inline">+ Tambah Layanan / Produk</span>
            <span class="sm:hidden">+ Tambah</span>
          </button>

          <button
            id="deleteSelectedBtn"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 text-white hover:bg-red-700 active:bg-red-800 transition shadow-sm text-sm hidden"
          >
            Hapus Terpilih
          </button>
        </div>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white ring-1 ring-gray-200 rounded-xl shadow-sm px-5 py-4 sm:px-6 sm:py-5">
      <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-end">
        <!-- Filter Vendor -->
        <div class="flex-1">
          <label for="filterVendor" class="sr-only">Filter Vendor</label>
          <select id="filterVendor" 
                  class="block w-full py-2.5 px-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="">Semua Vendor</option>
            <?php foreach ($vendors as $vendor): ?>
              <option value="<?= esc($vendor['id']) ?>"><?= esc($vendor['business_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Filter Layanan -->
        <div class="flex-1">
          <label for="filterService" class="sr-only">Cari Layanan</label>
          <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
              </svg>
            </div>
            <input id="filterService" type="text"
                  class="block w-full py-2.5 ps-10 pe-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Filter nama layanan...">
          </div>
        </div>

        <!-- Filter Produk -->
        <div class="flex-1">
          <label for="filterProduct" class="sr-only">Cari Produk</label>
          <div class="relative">
            <div class="absolute inset-y-0 start-0 flex items-center ps-3 pointer-events-none">
              <svg class="w-4 h-4 text-gray-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
              </svg>
            </div>
            <input id="filterProduct" type="text"
                  class="block w-full py-2.5 ps-10 pe-3 text-sm text-gray-900 border border-gray-300 rounded-lg bg-white
                          focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Filter nama produk...">
          </div>
        </div>

        <!-- Tombol Reset -->
        <button type="button" id="clearFiltersBtn"
                class="px-4 py-2.5 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 transition text-sm border border-gray-300 whitespace-nowrap h-[42px] flex-shrink-0">
          Reset
        </button>
      </div>
    </div>

    <!-- Tabel -->
    <form id="bulkDeleteForm" method="post" action="<?= site_url('admin/services/delete_multiple') ?>">
      <?= csrf_field() ?>

      <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 overflow-hidden">
        <div class="max-h-[70vh] overflow-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100 scrollbar-thumb-rounded-full">
          <table class="min-w-full text-[13px] sm:text-sm text-gray-800">
            <thead class="sticky top-0 z-10 bg-blue-600 text-white">
              <tr class="uppercase tracking-wide">
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-center font-semibold w-12">No</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-left font-semibold">Vendor</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-left font-semibold">Layanan</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-left font-semibold">Deskripsi Layanan</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-left font-semibold">Produk & Harga</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-left font-semibold">Deskripsi Produk</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-center font-semibold">Lampiran</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-center font-semibold">URL Lampiran</th>
                <th class="px-3.5 py-2.5 sm:px-4 sm:py-3 text-center font-semibold">Aksi</th>
                <th class="px-2.5 py-2.5 sm:px-3 sm:py-3 text-center font-semibold w-12">
                  <input type="checkbox" id="selectAll" class="w-3.5 h-3.5 mx-auto accent-blue-600 cursor-pointer">
                </th>
              </tr>
            </thead>

            <tbody class="leading-relaxed [&>tr:nth-child(odd)]:bg-gray-50">
            <?php
            $groupedData = [];
            if (!empty($vendorServices)) {
            foreach ($vendorServices as $vendorData) {
                $vendor = $vendorData['vendor'];
                if (!empty($vendorData['services'])) {
                foreach ($vendorData['services'] as $service) {
                    $serviceName = $service['service_name'] ?? '-';
                    if ($serviceName !== '-') {
                    if (!isset($groupedData[$serviceName])) {
                        $groupedData[$serviceName] = [
                        'vendor_id' => $vendor['id'],
                        'vendor_name' => $vendor['business_name'],
                        'service_description' => $service['service_description'] ?? '-',
                        'products' => []
                        ];
                    }
                    
                    $products = explode('<br>', $service['products'] ?? '');
                    $prices = explode('<br>', $service['products_harga'] ?? '');
                    $descriptions = explode('<br>', $service['products_deskripsi'] ?? '');
                    $attachments = explode('<br>', $service['products_lampiran'] ?? '');
                    $attachmentUrls = explode('<br>', $service['products_lampiran_url'] ?? '');
                    
                    foreach ($products as $index => $product) {
                        if (!empty($product)) {
                        // Pastikan harga diubah ke integer
                        $price = (int)($prices[$index] ?? 0);
                        
                        $groupedData[$serviceName]['products'][] = [
                            'product_name' => $product,
                            'product_description' => $descriptions[$index] ?? '-',
                            'price' => $price,
                            'attachment' => $attachments[$index] ?? null,
                            'attachment_url' => $attachmentUrls[$index] ?? null,
                        ];
                        }
                    }
                    }
                }
                }
            }
            }
            $rowNo = 1;
            ?>

            <?php if (!empty($groupedData)): ?>
              <?php foreach ($groupedData as $serviceName => $serviceData): ?>
                <?php
                  $productCount = count($serviceData['products']);
                  $rowspan = $productCount > 0 ? $productCount : 1;
                  $editUrl = site_url('admin/services/edit?service_name=' . rawurlencode($serviceName) . '&vendor_id=' . $serviceData['vendor_id']);

                  $serviceDescShort = $serviceData['service_description'];
                  if (strlen($serviceDescShort) > 15) { $serviceDescShort = substr($serviceDescShort, 0, 15) . '...'; }
                ?>
                <tr class="border-b border-gray-200 hover:bg-blue-50/50 transition-colors" data-vendor-id="<?= $serviceData['vendor_id'] ?>">
                  <td class="px-4 py-3 text-center align-top font-medium" rowspan="<?= $rowspan ?>"><?= $rowNo++; ?></td>
                  <td class="px-4 py-3 align-top font-medium text-gray-900" rowspan="<?= $rowspan ?>">
                    <span class="inline-block max-w-[220px] md:max-w-[260px] truncate" title="<?= esc($serviceData['vendor_name']) ?>">
                      <?= esc($serviceData['vendor_name']); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 align-top font-medium text-gray-900" rowspan="<?= $rowspan ?>">
                    <span class="inline-block max-w-[220px] md:max-w-[260px] truncate" title="<?= esc($serviceName) ?>">
                      <?= esc($serviceName); ?>
                    </span>
                  </td>
                  <td class="px-4 py-3 align-top text-gray-600" rowspan="<?= $rowspan ?>">
                    <span class="inline-block max-w-[280px] truncate align-middle" title="<?= esc($serviceData['service_description']) ?>">
                      <?= esc($serviceDescShort); ?>
                    </span>
                    <?php if (strlen($serviceData['service_description']) > 15): ?>
                      <button type="button"
                              onclick="showDescription('<?= esc($serviceName) ?>', '<?= esc($serviceData['service_description']) ?>', 'layanan')"
                              class="ml-1 inline-flex items-center text-blue-600 hover:text-blue-800 text-xs underline">
                        Lihat
                      </button>
                    <?php endif; ?>
                  </td>

                  <?php if ($productCount > 0): ?>
                    <?php foreach ($serviceData['products'] as $index => $product): ?>
                      <?php
                        $productDescShort = $product['product_description'];
                        if (strlen($productDescShort) > 15) { $productDescShort = substr($productDescShort, 0, 15) . '...'; }
                      ?>
                      <?php if ($index > 0): ?></tr><tr class="border-b border-gray-200 hover:bg-blue-50/50 transition-colors" data-vendor-id="<?= $serviceData['vendor_id'] ?>"><?php endif; ?>

                      <!-- Produk & Harga -->
                      <td class="px-4 py-3 align-top">
                        <span class="inline-block max-w-[240px] truncate font-medium text-gray-900" title="<?= esc($product['product_name']); ?>">
                          <?= esc($product['product_name']); ?>
                        </span>
                        <span class="ml-1 inline-block rounded-full border border-gray-300 bg-gray-100 px-2.5 py-0.5 text-[11px] text-gray-700 align-middle">
                        Rp <?= number_format((int)$product['price'], 0, ',', '.'); ?>
                        </span>
                      </td>

                      <!-- Deskripsi Produk -->
                      <td class="px-4 py-3 align-top">
                        <span class="inline-block max-w-[280px] truncate text-gray-700" title="<?= esc($product['product_description']) ?>">
                          <?= esc($productDescShort); ?>
                        </span>
                        <?php if (strlen($product['product_description']) > 15): ?>
                          <button type="button"
                                  onclick="showDescription('<?= esc($product['product_name']) ?>', '<?= esc($product['product_description']) ?>', 'produk')"
                                  class="ml-1 inline-flex items-center text-blue-600 hover:text-blue-800 text-xs underline">
                            Lihat
                          </button>
                        <?php endif; ?>
                      </td>

                      <!-- Lampiran -->
                      <td class="px-4 py-3 align-top text-center">
                        <?php if ($product['attachment']): ?>
                          <a href="<?= base_url('uploads/vendor_products/'.trim((string)$product['attachment'])); ?>" target="_blank"
                             class="inline-flex items-center justify-center px-2.5 py-1.5 rounded border border-blue-200 bg-blue-50 text-blue-700 text-xs hover:bg-blue-100">
                            Lihat
                          </a>
                        <?php else: ?>
                          <span class="text-gray-400">-</span>
                        <?php endif; ?>
                      </td>

                      <!-- URL Lampiran -->
                      <td class="px-4 py-3 align-top text-center">
                        <?php if (!empty($product['attachment_url'])): ?>
                          <a href="<?= esc($product['attachment_url']) ?>" target="_blank"
                             class="inline-flex items-center justify-center px-2.5 py-1.5 rounded border border-blue-200 bg-blue-50 text-blue-700 text-xs hover:bg-blue-100">
                            Lihat
                          </a>
                        <?php else: ?>
                          <span class="text-gray-400">-</span>
                        <?php endif; ?>
                      </td>

                      <?php if ($index === 0): ?>
                        <!-- Aksi -->
                        <td class="px-4 py-3 text-center align-top" rowspan="<?= $rowspan ?>">
                          <button type="button"
                                  onclick="openModal('<?= $editUrl ?>')"
                                  class="px-3.5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-xs shadow-sm">
                            Edit
                          </button>
                        </td>

                        <!-- Checkbox -->
                        <td class="px-2 py-3 text-center align-top" rowspan="<?= $rowspan ?>">
                          <input type="checkbox" name="service_names[]" value="<?= esc($serviceName) ?>"
                                 class="rowCheckbox w-3.5 h-3.5 mx-auto accent-blue-600 cursor-pointer hover:scale-110 transition">
                        </td>
                      <?php endif; ?>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <td class="px-4 py-3 align-top"><span class="text-gray-400">-</span></td>
                    <td class="px-4 py-3 align-top"><span class="text-gray-400">-</span></td>
                    <td class="px-4 py-3 align-top text-center"><span class="text-gray-400">-</span></td>
                    <td class="px-4 py-3 align-top text-center"><span class="text-gray-400">-</span></td>
                    <td class="px-4 py-3 text-center align-top" rowspan="<?= $rowspan ?>">
                      <button type="button"
                              onclick="openModal('<?= $editUrl ?>')"
                              class="px-3.5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-xs shadow-sm">
                        Edit
                      </button>
                    </td>
                    <td class="px-2 py-3 text-center align-top" rowspan="<?= $rowspan ?>">
                      <input type="checkbox" name="service_names[]" value="<?= esc($serviceName) ?>"
                             class="rowCheckbox w-3.5 h-3.5 mx-auto accent-blue-600 cursor-pointer hover:scale-110 transition">
                    </td>
                  <?php endif; ?>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="10" class="px-4 py-6 text-gray-500 text-center">Belum ada layanan / produk.</td>
              </tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </form>

  </div> <!-- /wrapper -->
</main>

<!-- MODAL: wrapper tetap, tampilan diperhalus -->
<div id="spModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl mx-auto ring-1 ring-gray-200">
    <div id="modalContent">
      <div class="flex justify-center items-center h-40">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Deskripsi -->
<div id="descriptionModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4">
  <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden ring-1 ring-gray-200">
    <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
      <h3 id="descriptionTitle" class="text-sm font-semibold"></h3>
      <button type="button" onclick="closeDescriptionModal()" class="text-white/90 hover:text-white text-lg">&times;</button>
    </div>
    <div class="p-6">
      <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi Lengkap:</label>
        <div class="bg-gray-50 border border-gray-200 rounded-md p-3 max-h-72 overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
          <p id="descriptionContent" class="text-xs text-gray-700 whitespace-pre-line break-words leading-relaxed"></p>
        </div>
      </div>
    </div>
    <div class="bg-gray-50 px-6 py-3 flex justify-end border-t border-gray-200">
      <button type="button" onclick="closeDescriptionModal()"
              class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 text-xs border border-gray-300">
        Tutup
      </button>
    </div>
  </div>
</div>

<style>
/* Scrollbar halus & rapi */
.scrollbar-thin::-webkit-scrollbar{ width:8px; height:8px; }
.scrollbar-thin::-webkit-scrollbar-track{ background:#f5f5f5; border-radius:4px; }
.scrollbar-thin::-webkit-scrollbar-thumb{ background:#c5c5c5; border-radius:4px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover{ background:#a8a8a8; }
</style>

<script>
/* ---------- SweetAlert mini style ---------- */
const swalMini = {
  popup: 'rounded-md text-sm p-3 shadow',
  title: 'text-sm font-semibold',
  htmlContainer: 'text-sm'
};

/* ---------- Helpers ---------- */
function getCsrfHeaders(){
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  return token ? { 'X-CSRF-Token': token, 'X-Requested-With': 'XMLHttpRequest' } : { 'X-Requested-With': 'XMLHttpRequest' };
}
function notify(icon, title, text){
  Swal.fire({ icon, title, text, toast:true, position:'top-end', timer:1500, showConfirmButton:false, customClass:swalMini });
}
function $root(){ return document.getElementById('modalContent') || document; }
function $form(){ return $root().querySelector('#editForm') || document.getElementById('editForm'); }

/* ---------- Modal Deskripsi (tetap) ---------- */
function showDescription(name, description, type){
  const modal = document.getElementById('descriptionModal');
  const title = document.getElementById('descriptionTitle');
  const content = document.getElementById('descriptionContent');
  if(modal && title && content){
    title.textContent = `Deskripsi ${type}: ${name}`;
    content.innerHTML = (description||'').toString().replace(/\n/g,'<br>');
    modal.classList.remove('hidden'); modal.classList.add('flex');
  }
}
function closeDescriptionModal(){
  const modal = document.getElementById('descriptionModal');
  if(modal){ modal.classList.add('hidden'); modal.classList.remove('flex'); }
}

/* ---------- Modal create/edit ---------- */
function openModal(url){
  const modal = document.getElementById('spModal');
  const content = document.getElementById('modalContent');
  if(!modal || !content) return;

  modal.classList.remove('hidden'); modal.classList.add('flex');
  content.innerHTML = '<div class="flex justify-center items-center h-40"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';

  fetch(url, { headers: getCsrfHeaders() })
    .then(r => { if(!r.ok) throw new Error(`HTTP ${r.status} ${r.statusText}`); return r.text(); })
    .then(html => {
      content.innerHTML = html;
      initFormScripts();          // penting: aktifkan handler setelah inject HTML
      wireEditUI();               // penting: aktifkan tombol tambah/hapus/attachment
    })
    .catch(err => {
      content.innerHTML = `
        <div class="text-center p-6">
          <div class="text-red-500 text-lg mb-2">Gagal memuat form</div>
          <p class="text-gray-600 text-sm mb-2">${err.message}</p>
          <p class="text-gray-500 text-xs mb-4">URL: ${url}</p>
          <button onclick="closeModal()" class="px-4 py-2 rounded-lg bg-gray-100 text-gray-800 hover:bg-gray-200 text-sm border border-gray-300">
            Tutup
          </button>
        </div>`;
    });
}
function closeModal(){
  const modal = document.getElementById('spModal');
  const content = document.getElementById('modalContent');
  if(modal){ modal.classList.add('hidden'); modal.classList.remove('flex'); }
  if(content){ content.innerHTML = '<div class="flex justify-center items-center h-40"><div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>'; }
}

/* Submit (AJAX) untuk create/edit */
function initFormScripts(){
  const createForm = document.getElementById('createForm');
  if(createForm) createForm.addEventListener('submit', function(e){ e.preventDefault(); handleFormSubmit(this); });

  // cari di modal lebih dulu; kalau tidak ada, fallback ke dokumen
  const editForm = $root().querySelector('form[action="<?= site_url('admin/services/update') ?>"]') 
                || document.querySelector('form[action="<?= site_url('admin/services/update') ?>"]');
  if(editForm) editForm.addEventListener('submit', function(e){ e.preventDefault(); handleFormSubmit(this); });
}

/* --- NEW: aktifkan tombol Tambah Produk & expose fungsi global --- */
function wireEditUI(){
  const addBtn = $root().querySelector('#addProductBtn');
  if (addBtn && !addBtn.dataset.bound) {
    addBtn.addEventListener('click', addNewProduct);
    addBtn.dataset.bound = '1';
  }
}
/* fungsi global utk dipanggil dari onclick di HTML edit form */
window.addNewProduct = function(){
  const idx = nextProductIndex();
  const localId = (window.__newProductCounter = (window.__newProductCounter || 0) + 1);
  const wrap = $root().querySelector('#newProductsContainer');
  if(!wrap) return;

  const html = `
    <div class="product-row border border-gray-200 rounded-lg p-4 bg-gray-50" id="new-product-row-${localId}">
      <div class="flex items-start justify-between mb-3">
        <div class="font-medium text-sm text-gray-700">Produk Baru</div>
        <button type="button" onclick="removeNewProduct(${localId})" class="text-red-600 hover:text-red-800 text-xs transition-colors">
          Hapus produk
        </button>
      </div>
      <input type="hidden" name="products[${idx}][id]" value="">
      <input type="hidden" name="products[${idx}][delete_flag]" value="0">
      <input type="hidden" name="products[${idx}][remove_attachment]" value="0">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Nama Produk <span class="text-red-500">*</span></label>
          <input type="text" name="products[${idx}][product_name]"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-name-input"
            placeholder="Nama produk" required>
        </div>
        <div>
          <label class="block text-sm font-medium mb-1 text-gray-700">Harga (Rp) <span class="text-red-500">*</span></label>
          <input type="text" name="products[${idx}][price]"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 product-price-input price-input"
            placeholder="0" required>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium mb-1 text-gray-700">Deskripsi Produk</label>
          <textarea name="products[${idx}][product_description]" rows="2"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Deskripsi produk"></textarea>
        </div>

        <div class="md:col-span-2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">Unggah Lampiran</label>
              <input type="file" name="products[${idx}][attachment]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                accept=".pdf,.jpg,.jpeg,.png">
              <p class="text-xs text-gray-500 mt-1">PDF/JPG/PNG maks 2MB</p>
            </div>
            <div>
              <label class="block text-sm font-medium mb-1 text-gray-700">atau URL Lampiran</label>
              <input type="url" name="products[${idx}][attachment_url]"
                class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://contoh.com/file.pdf">
            </div>
          </div>
        </div>
      </div>
    </div>`;
  wrap.insertAdjacentHTML('beforeend', html);

  // fokus ke input pertama
  const row = document.getElementById('new-product-row-' + localId);
  row?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  row?.querySelector('input[type="text"]')?.focus();
};
window.removeNewProduct = function(localId){
  const row = document.getElementById('new-product-row-' + localId);
  row?.remove();
  notify('success','Berhasil','Produk baru dihapus');
};
/* Hapus produk EXISTING: kita pindahkan hidden input (id & delete_flag) ke form agar tetap terkirim */
window.deleteProduct = function(index, productId){
  const row = $root().querySelector('#product-row-' + index);
  const form = $form();
  if(!form) return;

  // Ambil/siapkan hidden inputs
  let idInput   = row?.querySelector(`input[name="products[${index}][id]"]`);
  let delInput  = row?.querySelector(`input[name="products[${index}][delete_flag]"]`);
  let rmvInput  = row?.querySelector(`input[name="products[${index}][remove_attachment]"]`);
  if(!idInput){
    idInput = document.createElement('input');
    idInput.type='hidden'; idInput.name=`products[${index}][id]`; idInput.value = String(productId||'');
  }
  if(!delInput){
    delInput = document.createElement('input');
    delInput.type='hidden'; delInput.name=`products[${index}][delete_flag]`;
  }
  delInput.value = '1';

  const holder = document.createElement('div');
  holder.style.display='none';
  holder.id = 'deleted-product-holder-' + index;
  holder.appendChild(idInput);
  holder.appendChild(delInput);
  if (rmvInput) holder.appendChild(rmvInput);
  form.appendChild(holder);

  // Hapus ringkasan (jika ada elemen summary terpisah)
  $root().querySelectorAll(`.product-summary[data-index="${index}"]`).forEach(el => el.remove());

  // Hapus row dari DOM
  row?.remove();
  notify('success','Berhasil','Produk dihapus dari formulir');
};
/* Hapus lampiran EXISTING: set flag & sembunyikan blok saat ini */
window.removeAttachment = function(index){
  const flag = $root().querySelector('#remove-attachment-' + index);
  const cur  = $root().querySelector('#current-attachment-' + index);
  if(flag){ flag.value = '1'; }
  if(cur){ cur.style.display='none'; }
  notify('success','Berhasil','Lampiran akan dihapus saat disimpan');
};

function handleFormSubmit(form){
  // Format semua input harga sebelum submit
  form.querySelectorAll('.price-input').forEach(input => {
    // Simpan nilai asli tanpa format
    const originalName = input.name;
    const originalValue = input.value;
    const unformattedValue = unformatCurrency(originalValue);
    
    // Buat input hidden untuk menyimpan nilai asli
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = originalName;
    hiddenInput.value = unformattedValue;
    form.appendChild(hiddenInput);
    
    // Ubah nama input asli agar tidak terkirim dua kali
    input.name = originalName + '_formatted';
  });

  const formData = new FormData(form);
  fetch(form.action, { method:'POST', headers: getCsrfHeaders(), body: formData })
    .then(res => res.json())
    .then(data => {
      if (data.csrfHash) document.querySelector('meta[name="csrf-token"]')?.setAttribute('content', data.csrfHash);
      if (data.status === 'success') {
        Swal.fire({ icon:'success', title:'Berhasil', text:data.message, timer:1500, showConfirmButton:false, width:300, customClass:swalMini })
          .then(() => { closeModal(); window.location.reload(); });
      } else {
        Swal.fire({ icon:'error', title:'Gagal', text:data.message||'Terjadi kesalahan', width:300, customClass:swalMini });
      }
    })
    .catch(() => Swal.fire({ icon:'error', title:'Error', text:'Koneksi gagal', width:300, customClass:swalMini }));
}

/* ---------- Global listeners ---------- */
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    if (!document.getElementById('descriptionModal').classList.contains('hidden')) closeDescriptionModal();
    else closeModal();
  }
});
document.addEventListener('click', (e) => {
  const modal = document.getElementById('spModal');
  const descModal = document.getElementById('descriptionModal');
  if (e.target === modal) closeModal();
  if (e.target === descModal) closeDescriptionModal();
});

// Ganti fungsi formatCurrency
function formatCurrency(value) {
  // Hapus semua karakter non-angka
  let number = value.replace(/[^\d]/g, '');
  
  // Jika kosong, return kosong
  if (number === '') return '';
  
  // Format dengan titik sebagai pemisah ribuan
  return number.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// Ganti fungsi unformatCurrency
function unformatCurrency(formattedValue) {
  return formattedValue.replace(/[^\d]/g, '');
}

// Perbaiki event listener untuk input harga
document.addEventListener('input', function(e) {
  if (e.target.matches('.price-input')) {
    // Hanya izinkan angka
    let value = e.target.value.replace(/[^\d]/g, '');
    
    // Simpan posisi kursor
    const cursorPosition = e.target.selectionStart;
    
    // Format nilai
    e.target.value = formatCurrency(value);
    
    // Kembalikan posisi kursor
    e.target.setSelectionRange(cursorPosition, cursorPosition);
  }
});

// Perbaiki event listener untuk paste
document.addEventListener('paste', function(e) {
  if (e.target.matches('.price-input')) {
    e.preventDefault();
    const pastedText = (e.clipboardData || window.clipboardData).getData('text');
    // Hapus semua karakter non-digit
    const cleanValue = pastedText.replace(/[^\d]/g, '');
    // Format nilai
    e.target.value = formatCurrency(cleanValue);
  }
});

// Perbaiki event listener untuk focus out
document.addEventListener('focusout', function(e) {
  if (e.target.matches('.price-input')) {
    // Format nilai saat keluar dari input
    e.target.value = formatCurrency(e.target.value);
  }
});

// Perbaiki event listener untuk focus in
document.addEventListener('focusin', function(e) {
  if (e.target.matches('.price-input')) {
    // Hapus format saat fokus untuk memudahkan edit
    e.target.value = unformatCurrency(e.target.value);
  }
});
document.addEventListener('input', function(e) {
  if (e.target.matches('.price-input')) {
    // Hanya izinkan angka
    e.target.value = e.target.value.replace(/[^\d]/g, '');
    
    // Format dengan titik pemisah ribuan
    const cursorPosition = e.target.selectionStart;
    const oldValue = e.target.value;
    const newValue = formatCurrency(e.target.value);
    
    if (oldValue !== newValue) {
      e.target.value = newValue;
      
      // Hitung posisi kursor baru
      const diff = newValue.length - oldValue.length;
      e.target.setSelectionRange(cursorPosition + diff, cursorPosition + diff);
    }
  }
});
/* ---------- Bulk delete + filter (tetap) ---------- */
document.addEventListener("DOMContentLoaded", function(){
  const selectAll = document.getElementById("selectAll");
  const deleteBtn = document.getElementById("deleteSelectedBtn");
  function toggleDeleteBtn(){ const checked = document.querySelectorAll(".rowCheckbox:checked").length; deleteBtn?.classList.toggle("hidden", checked===0); }
  selectAll?.addEventListener("change", function(){ const on = this.checked; document.querySelectorAll(".rowCheckbox").forEach(cb => cb.checked=on); toggleDeleteBtn(); });
  document.querySelectorAll(".rowCheckbox").forEach(cb => cb.addEventListener("change", toggleDeleteBtn));

  deleteBtn?.addEventListener("click", function(e){
    e.preventDefault();
    const serviceNames = Array.from(document.querySelectorAll(".rowCheckbox:checked")).map(cb => cb.value);
    if (serviceNames.length === 0) return;
    Swal.fire({
      title:'Hapus terpilih?',
      text:`Ada ${serviceNames.length} layanan yang akan dihapus beserta semua produknya.`,
      icon:'warning',
      showCancelButton:true,
      confirmButtonColor:'#d33',
      cancelButtonColor:'#3085d6',
      confirmButtonText:'Ya',
      cancelButtonText:'Batal',
      width:300,
      customClass:swalMini
    }).then((result)=>{
      if(!result.isConfirmed) return;
      
      // Buat FormData untuk mengirim data
      const formData = new FormData();
      serviceNames.forEach(name => {
        formData.append('service_names[]', name);
      });
      
      fetch("<?= site_url('admin/services/delete_multiple') ?>", {
        method:"POST",
        body: formData,
        headers: getCsrfHeaders()
      })
      .then(res=>res.json())
      .then(data=>{
        if (data.csrfHash) {
          document.querySelector("meta[name='csrf-token']")?.setAttribute("content", data.csrfHash);
        }
        if (data.status === 'success') {
          Swal.fire({ 
            icon:'success', 
            title:'Berhasil', 
            text:data.message, 
            timer:1500, 
            showConfirmButton:false, 
            width:300, 
            customClass:swalMini 
          })
          .then(()=>window.location.reload());
        } else {
          Swal.fire({ 
            icon:'error', 
            title:'Gagal', 
            text:data.message, 
            width:300, 
            customClass:swalMini 
          });
        }
      })
      .catch(()=> Swal.fire({ 
        icon:'error', 
        title:'Error', 
        text:'Koneksi gagal', 
        width:300, 
        customClass:swalMini 
      }));
    });
  });

  // Filter tabel (dengan tambahan filter vendor)
  const $vendor = document.getElementById('filterVendor');
  const $svc = document.getElementById('filterService');
  const $prd = document.getElementById('filterProduct');
  const $btnClear = document.getElementById('clearFiltersBtn');
  const tbody = document.querySelector('table tbody');

  function norm(txt){ return (txt||'').toString().toLowerCase().trim(); }
  function buildServiceGroups(){
    const rows = Array.from(tbody?.rows || []);
    const groups = [];
    let i=0;
    while(i<rows.length){
      const tr = rows[i];
      const vendorTd = tr.querySelector('td:nth-child(2)[rowspan]');
      const svcTd = tr.querySelector('td:nth-child(3)[rowspan]');
      if(!svcTd){ i++; continue; }
      const rowspan = parseInt(svcTd.getAttribute('rowspan')||'1',10)||1;
      const chunk = rows.slice(i, i+rowspan);
      const productCells = chunk.map((r, idx)=> idx===0 ? r.querySelector('td:nth-child(5)') : r.querySelector('td:nth-child(2)'));
      groups.push({ 
        vendorId: tr.getAttribute('data-vendor-id'),
        vendorName: norm(vendorTd.textContent),
        serviceName:norm(svcTd.textContent), 
        rows:chunk, 
        productCells 
      });
      i += rowspan;
    }
    return groups;
  }
  const serviceGroups = buildServiceGroups();
  function applyFilters(){
    const qVendor = $vendor?.value;
    const qSvc = norm($svc?.value);
    const qPrd = norm($prd?.value);
    serviceGroups.forEach(g=>{
      const vendorMatch = !qVendor || g.vendorId === qVendor;
      const svcMatch = !qSvc || g.serviceName.includes(qSvc);
      let prdMatch = true;
      if(qPrd){ prdMatch = g.productCells.some(td => td && norm(td.textContent).includes(qPrd)); }
      const show = vendorMatch && svcMatch && prdMatch;
      g.rows.forEach(r => r.style.display = show ? '' : 'none');
    });
  }
  function clearFilters(){ 
    if($vendor) $vendor.value = ''; 
    if($svc) $svc.value=''; 
    if($prd) $prd.value=''; 
    applyFilters(); 
  }
  let t; function debounced(){ clearTimeout(t); t=setTimeout(applyFilters,150); }
  $vendor?.addEventListener('change', applyFilters);
  $svc?.addEventListener('input', debounced);
  $prd?.addEventListener('input', debounced);
  $btnClear?.addEventListener('click', clearFilters);
});
/* expose global */
window.openModal=openModal; window.closeModal=closeModal;
window.showDescription=showDescription; window.closeDescriptionModal=closeDescriptionModal;
</script>

<?= $this->include('admin/layouts/footer') ?>