<?= $this->include('admin/layouts/header') ?>

<div class="p-4 md:p-6 lg:p-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Layanan & Produk Vendor</h1>
        <button onclick="openModal('<?= site_url('admin/services/create') ?>')" class="mt-4 sm:mt-0 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Layanan & Produk
        </button>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-blue-600 text-white">
            <h2 class="text-lg font-semibold">Daftar Layanan & Produk Vendor</h2>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Vendor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Layanan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $i = 1; ?>
                        <?php foreach ($vendorServices as $item): ?>
                            <?php if (!empty($item['services'])): ?>
                                <?php foreach ($item['services'] as $service): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= $i++ ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= esc($item['vendor']['business_name']) ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $item['vendor']['status'] == 'verified' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                                <?= ucfirst($item['vendor']['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div>
                                                <strong><?= esc($service['service_name']) ?></strong>
                                                <?php if (!empty($service['service_description'])): ?>
                                                    <br><span class="text-gray-500 text-xs"><?= esc($service['service_description']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php 
                                            $products = explode('<br>', $service['products']);
                                            $prices = explode('<br>', $service['products_harga']);
                                            
                                            foreach ($products as $index => $product): 
                                            ?>
                                                <div class="mb-1">
                                                    <?= esc($product) ?>
                                                    <?php if (isset($prices[$index])): ?>
                                                        <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                            Rp <?= $prices[$index] ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button onclick="openModal('<?= site_url('admin/services/edit/' . $service['id']) ?>')" class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <a href="<?= site_url('admin/services/delete/' . $service['id']) ?>" 
                                               class="text-red-600 hover:text-red-900" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus layanan ini?')">
                                                <i class="fas fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Container -->
<div id="formModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="bg-blue-600 px-4 py-3 flex items-center justify-between">
                <h3 class="text-lg font-medium text-white" id="modal-title">Form Layanan & Produk Vendor</h3>
                <button type="button" onclick="closeModal()" class="text-white hover:text-gray-200 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4" id="modalContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function openModal(url) {
    document.getElementById('modalContent').innerHTML = '<div class="text-center p-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div></div>';
    document.getElementById('formModal').classList.remove('hidden');
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('modalContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('modalContent').innerHTML = '<div class="alert alert-danger">Gagal memuat form. Silakan coba lagi.</div>';
        });
}

function closeModal() {
    document.getElementById('formModal').classList.add('hidden');
}

// Handle form submission via AJAX
document.addEventListener('submit', function(e) {
    if (e.target.closest('#modalContent')) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    closeModal();
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi kesalahan. Silakan coba lagi.'
            });
        });
    }
});
</script>

<?= $this->include('admin/layouts/footer') ?>