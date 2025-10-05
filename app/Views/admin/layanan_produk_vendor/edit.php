<form method="post" action="<?= site_url('admin/services/update/' . $service['id']) ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    
    <div class="mb-4">
        <label for="vendor_id" class="block text-sm font-medium text-gray-700">Vendor</label>
        <select class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md" id="vendor_id" name="vendor_id" required>
            <option value="">-- Pilih Vendor --</option>
            <?php foreach ($vendors as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $v['id'] == $vendor['id'] ? 'selected' : '' ?>>
                    <?= esc($v['business_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="mb-4">
        <label for="service_name" class="block text-sm font-medium text-gray-700">Nama Layanan</label>
        <input type="text" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" id="service_name" name="service_name" 
               value="<?= esc($service['service_name']) ?>" required>
    </div>
    
    <div class="mb-4">
        <label for="service_description" class="block text-sm font-medium text-gray-700">Deskripsi Layanan</label>
        <textarea class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" id="service_description" name="service_description" rows="3"><?= esc($service['service_description']) ?></textarea>
    </div>
    
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Produk</label>
        <div id="products_container">
            <?php foreach ($products as $index => $product): ?>
                <div class="product-item border border-gray-300 rounded-md p-3 mb-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                            <input type="text" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[<?= $index ?>][product_name]" 
                                   value="<?= esc($product['product_name']) ?>" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Harga</label>
                            <input type="number" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[<?= $index ?>][price]" 
                                   value="<?= $product['price'] ?>" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700">Deskripsi Produk</label>
                        <textarea class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[<?= $index ?>][product_description]" rows="2"><?= esc($product['product_description']) ?></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Lampiran</label>
                            <input type="file" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="attachment_<?= $index ?>" accept=".pdf,.jpg,.jpeg,.png">
                            <?php if (!empty($product['attachment'])): ?>
                                <p class="mt-1 text-sm text-gray-500">
                                    File saat ini: <a href="<?= base_url('uploads/vendor_products/' . $product['attachment']) ?>" target="_blank" class="text-blue-600 hover:text-blue-800"><?= $product['attachment'] ?></a>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">URL Lampiran</label>
                            <input type="url" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[<?= $index ?>][attachment_url]" 
                                   value="<?= esc($product['attachment_url']) ?>">
                        </div>
                    </div>
                    <input type="hidden" name="products[<?= $index ?>][temp_id]" value="<?= $index ?>">
                    <button type="button" class="mt-2 px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors remove-product">
                        <i class="fas fa-trash mr-1"></i> Hapus Produk
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" id="add_product" class="mt-2 px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Produk
        </button>
    </div>
    
    <div class="flex justify-end">
        <button type="button" onclick="closeModal()" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">
            Batal
        </button>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Simpan
        </button>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let productCount = <?= count($products) ?>;
    
    document.getElementById('add_product').addEventListener('click', function() {
        const container = document.getElementById('products_container');
        
        const productItem = document.createElement('div');
        productItem.className = 'product-item border border-gray-300 rounded-md p-3 mb-3';
        productItem.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Produk</label>
                    <input type="text" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[${productCount}][product_name]" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Harga</label>
                    <input type="number" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[${productCount}][price]" step="0.01" min="0" required>
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Deskripsi Produk</label>
                <textarea class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[${productCount}][product_description]" rows="2"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lampiran</label>
                    <input type="file" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="attachment_${productCount}" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">URL Lampiran</label>
                    <input type="url" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" name="products[${productCount}][attachment_url]">
                </div>
            </div>
            <input type="hidden" name="products[${productCount}][temp_id]" value="${productCount}">
            <button type="button" class="mt-2 px-3 py-1 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors remove-product">
                <i class="fas fa-trash mr-1"></i> Hapus Produk
            </button>
        `;
        
        container.appendChild(productItem);
        productCount++;
        
        // Add remove event
        productItem.querySelector('.remove-product').addEventListener('click', function() {
            productItem.remove();
        });
    });
    
    // Add remove event to existing remove buttons
    document.querySelectorAll('.remove-product').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.product-item').remove();
        });
    });
});
</script>